<?php

use App\Enums\MusicTradition;
use App\Enums\PermissionName;
use App\Models\MusicLibraryChapter;
use App\Models\MusicLibraryEntry;
use App\Support\MusicLibraryScanner;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int $chapterId;
    public string $name;
    public string $sortKey;
    public bool $open = false;

    /** @var array<string, mixed> */
    public array $form = [];

    public ?string $scanWarning = null;

    public function mount(): void
    {
        $this->resetForm();
    }

    /**
     * Entries are only queried once the chapter is open.
     *
     * @return Collection<int, MusicLibraryEntry>
     */
    #[Computed]
    public function entries(): Collection
    {
        return MusicLibraryEntry::query()
            ->where('music_library_chapter_id', $this->chapterId)
            ->orderBy('sort_key')
            ->orderBy('name')
            ->get(['id', 'name', 'sort_key']);
    }

    /**
     * Open or close the chapter.
     */
    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    /**
     * Pre-fill the file fields from the files found under the entered S3 path.
     */
    public function populateFromPath(): void
    {
        abort_unless(auth()->user()?->can(PermissionName::ManageMusicLibrary->value), 403);

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
     * Add an entry to this chapter.
     */
    public function addEntry(): void
    {
        abort_unless(auth()->user()?->can(PermissionName::ManageMusicLibrary->value), 403);

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

        MusicLibraryChapter::findOrFail($this->chapterId)->entries()->create(
            array_map(fn (mixed $value): mixed => $value === '' ? null : $value, $validated)
        );

        unset($this->entries);
        $this->resetForm();

        Flux::modal('add-entry-'.$this->chapterId)->close();
        Flux::toast(variant: 'success', text: __('Entry added.'));
    }

    private function resetForm(): void
    {
        $this->scanWarning = null;
        $this->form = [
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
}; ?>

<div class="py-1">
    <div class="flex items-baseline gap-3">
        <flux:heading level="3">
            <button type="button" wire:click="toggle" aria-expanded="{{ $open ? 'true' : 'false' }}" class="flex cursor-pointer items-center gap-2 text-left font-semibold">
                <flux:icon.chevron-right class="size-4 shrink-0 transition-transform {{ $open ? 'rotate-90' : '' }}" />
                {{ $name }}
            </button>
        </flux:heading>
        @can(PermissionName::ManageMusicLibrary->value)
            <flux:text size="sm" class="font-mono opacity-60">{{ $sortKey }}</flux:text>
        @endcan
    </div>

    @if ($open)
        <div class="mt-1 ml-6 space-y-1">
            @php($canManage = auth()->user()?->can(PermissionName::ManageMusicLibrary->value) ?? false)
            @forelse ($this->entries as $entry)
                <livewire:music-library.entry
                    :key="'entry-'.$entry->id"
                    :entry-id="$entry->id"
                    :name="$entry->name"
                    :sort-key="$canManage ? $entry->sort_key : ''"
                />
            @empty
                <flux:text>No entries yet.</flux:text>
            @endforelse

            @can(PermissionName::ManageMusicLibrary->value)
                <flux:modal.trigger :name="'add-entry-'.$chapterId">
                    <flux:button size="sm" icon="plus" class="mt-2">Add entry</flux:button>
                </flux:modal.trigger>

                <flux:modal :name="'add-entry-'.$chapterId" class="md:w-[32rem]">
                    <form wire:submit="addEntry" class="space-y-4">
                        <flux:heading size="lg">Add entry to {{ $name }}</flux:heading>
                        <flux:input wire:model="form.name" label="Name" required />
                        <flux:input wire:model="form.sort_key" label="Sort key" required />
                        <flux:textarea wire:model="form.description" label="Description" />
                        <div class="grid grid-cols-3 gap-4">
                            <flux:select wire:model="form.tradition" label="Tradition">
                                <flux:select.option value="">None</flux:select.option>
                                @foreach (\App\Enums\MusicTradition::cases() as $tradition)
                                    <flux:select.option :value="$tradition->value">{{ $tradition->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:select wire:model="form.tone" label="Tone">
                                <flux:select.option value="">None</flux:select.option>
                                @foreach (range(1, 8) as $tone)
                                    <flux:select.option :value="$tone">{{ $tone }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <flux:separator />

                        <flux:select wire:model.live="form.parts" label="Number of parts" description="Choose this first; it decides which files are looked for.">
                            <flux:select.option value="">Choose...</flux:select.option>
                            @foreach (range(1, 4) as $parts)
                                <flux:select.option :value="$parts">{{ $parts }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:field>
                            <flux:label>S3 path</flux:label>
                            <flux:description>
                                For example, <code>divine-liturgy/second-antiphon/only-begotten</code>. Leave off the file extension (no <code>.pdf</code> or <code>.mp3</code>).
                            </flux:description>
                            <div class="flex gap-2">
                                <flux:input wire:model="form.s3_path" placeholder="divine-liturgy/second-antiphon/only-begotten" :disabled="blank($form['parts'])" class="flex-1" />
                                <flux:button wire:click="populateFromPath" :disabled="blank($form['parts'])" icon="magnifying-glass">Populate</flux:button>
                            </div>
                            <flux:error name="form.s3_path" />
                        </flux:field>
                        @if ($scanWarning)
                            <flux:callout variant="warning" icon="exclamation-triangle" :heading="$scanWarning" />
                        @endif

                        <flux:input wire:model="form.icon_path" label="Image" />
                        <flux:input wire:model="form.sheet_music_pdf_path" label="Sheet music PDF" />
                        <flux:input wire:model="form.note_playback_path" label="Note playback" />
                        @php($partFields = match ((int) $form['parts']) {
                            2 => ['s' => 'Melody', 'b' => 'Ison'],
                            3, 4 => ['s' => 'Soprano', 'a' => 'Alto', 't' => 'Tenor', 'b' => 'Bass'],
                            default => [],
                        })
                        @if ($partFields !== [])
                            <div class="grid grid-cols-2 gap-4">
                                @foreach ($partFields as $letter => $label)
                                    <flux:input wire:model="form.note_playback_{{ $letter }}_path" :label="$label.' playback'" />
                                @endforeach
                            </div>
                        @endif
                        <flux:input wire:model="form.example_mp3_path" label="Example MP3" />
                        <flux:input wire:model="form.example_youtube_url" label="Example YouTube URL" type="url" />
                        <div class="flex">
                            <flux:spacer />
                            <flux:button type="submit" variant="primary">Save</flux:button>
                        </div>
                    </form>
                </flux:modal>
            @endcan
        </div>
    @endif
</div>
