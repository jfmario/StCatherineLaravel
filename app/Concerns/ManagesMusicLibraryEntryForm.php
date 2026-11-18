<?php

namespace App\Concerns;

use App\Enums\MusicTradition;
use App\Enums\PermissionName;
use App\Support\MusicLibraryScanner;
use Flux\Flux;
use Illuminate\Validation\Rule;

/**
 * Shared form state, validation and S3 path scanning for adding and editing music library entries.
 *
 * Expects the using Livewire component to declare `public array $form` and `public ?string $scanWarning`.
 */
trait ManagesMusicLibraryEntryForm
{
    /**
     * Abort unless the current user can manage the music library.
     */
    protected function authorizeManageMusicLibrary(): void
    {
        abort_unless(auth()->user()?->can(PermissionName::ManageMusicLibrary->value), 403);
    }

    /**
     * Pre-fill the file fields from the files found under the entered S3 path.
     */
    public function populateFromPath(): void
    {
        $this->authorizeManageMusicLibrary();

        $this->validate([
            'form.parts' => ['required', 'integer', 'between:1,4'],
            'form.s3_path' => ['required', 'string', 'max:255', 'regex:/^\S(.*\S)?$/', 'not_regex:/\.(pdf|mp3)$/i'],
        ], [
            'form.s3_path.not_regex' => __('Leave the file extension off the path.'),
            'form.s3_path.regex' => __('The path must not start or end with spaces.'),
        ]);

        $found = MusicLibraryScanner::scan($this->form['s3_path'], (int) $this->form['parts']);

        if ($found === []) {
            $this->scanWarning = __('No files were found for that path and number of parts. Check the path and try again, or enter the files by hand.');

            return;
        }

        $this->scanWarning = null;
        $this->form = [...$this->form, ...$found];

        Flux::toast(variant: 'success', text: trans_choice('Filled :count field from the music library.|Filled :count fields from the music library.', count($found)));
    }

    /**
     * Validate the form and return entry attributes ready to persist.
     *
     * @return array<string, mixed>
     */
    protected function validatedEntryAttributes(): array
    {
        $validated = $this->validate([
            'form.name' => ['required', 'string', 'max:255'],
            'form.sort_key' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.tradition' => ['nullable', Rule::enum(MusicTradition::class)],
            'form.tone' => ['nullable', 'integer', 'between:1,8'],
            'form.parts' => ['required', 'integer', 'between:1,4'],
            'form.s3_path' => ['nullable', 'string', 'max:255'],
            'form.icon_path' => ['nullable', 'string', 'max:255'],
            'form.sheet_music_pdf_path' => ['nullable', 'string', 'max:255'],
            'form.note_playback_path' => ['nullable', 'string', 'max:255'],
            'form.note_playback_s_path' => ['nullable', 'string', 'max:255'],
            'form.note_playback_a_path' => ['nullable', 'string', 'max:255'],
            'form.note_playback_t_path' => ['nullable', 'string', 'max:255'],
            'form.note_playback_b_path' => ['nullable', 'string', 'max:255'],
            'form.example_mp3_path' => ['nullable', 'string', 'max:255'],
            'form.example_youtube_url' => ['nullable', 'url', 'max:255'],
        ])['form'];

        unset($validated['s3_path']);

        return array_map(fn (mixed $value): mixed => $value === '' ? null : $value, $validated);
    }

    /**
     * Form values for an empty entry.
     *
     * @return array<string, mixed>
     */
    protected function emptyEntryForm(): array
    {
        return [
            's3_path' => '',
            'name' => '',
            'sort_key' => '',
            'description' => '',
            'tradition' => '',
            'tone' => '',
            'parts' => '',
            'icon_path' => '',
            'sheet_music_pdf_path' => '',
            'note_playback_path' => '',
            'note_playback_s_path' => '',
            'note_playback_a_path' => '',
            'note_playback_t_path' => '',
            'note_playback_b_path' => '',
            'example_mp3_path' => '',
            'example_youtube_url' => '',
        ];
    }
}
