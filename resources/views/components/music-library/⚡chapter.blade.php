<?php

use App\Concerns\ManagesMusicLibraryEntryForm;
use App\Enums\PermissionName;
use App\Models\MusicLibraryChapter;
use App\Models\MusicLibraryEntry;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    use ManagesMusicLibraryEntryForm;

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
     * Add an entry to this chapter.
     */
    public function addEntry(): void
    {
        $this->authorizeManageMusicLibrary();

        $attributes = $this->validatedEntryAttributes();

        MusicLibraryChapter::findOrFail($this->chapterId)->entries()->create($attributes);

        unset($this->entries);
        $this->resetForm();

        Flux::modal('add-entry-'.$this->chapterId)->close();
        Flux::toast(variant: 'success', text: __('Entry added.'));
    }

    private function resetForm(): void
    {
        $this->scanWarning = null;
        $this->form = $this->emptyEntryForm();
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
                        <x-music-library.entry-fields :form="$form" :scan-warning="$scanWarning" />
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
