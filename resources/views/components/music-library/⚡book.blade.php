<?php

use App\Enums\PermissionName;
use App\Models\MusicLibraryBook;
use App\Models\MusicLibraryChapter;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int $bookId;
    public string $name;
    public string $sortKey;
    public bool $open = false;

    public string $chapterName = '';
    public string $chapterSortKey = '';
    public string $chapterDescription = '';

    /**
     * Chapters are only queried once the book is open.
     *
     * @return Collection<int, MusicLibraryChapter>
     */
    #[Computed]
    public function chapters(): Collection
    {
        return MusicLibraryChapter::query()
            ->where('music_library_book_id', $this->bookId)
            ->orderBy('sort_key')
            ->orderBy('name')
            ->get(['id', 'name', 'sort_key']);
    }

    /**
     * Open or close the book.
     */
    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    /**
     * Add a chapter to this book.
     */
    public function addChapter(): void
    {
        abort_unless(auth()->user()?->can(PermissionName::ManageMusicLibrary->value), 403);

        $validated = $this->validate([
            'chapterName' => ['required', 'string', 'max:255'],
            'chapterSortKey' => ['required', 'string', 'max:255'],
            'chapterDescription' => ['nullable', 'string'],
        ]);

        MusicLibraryBook::findOrFail($this->bookId)->chapters()->create([
            'name' => $validated['chapterName'],
            'sort_key' => $validated['chapterSortKey'],
            'description' => $validated['chapterDescription'] ?: null,
        ]);

        unset($this->chapters);
        $this->reset('chapterName', 'chapterSortKey', 'chapterDescription');

        Flux::modal('add-chapter-'.$this->bookId)->close();
        Flux::toast(variant: 'success', text: __('Chapter added.'));
    }
}; ?>

<div class="border-b border-[var(--liturgical-border)] py-3">
    <div class="flex items-baseline gap-3">
        <flux:heading size="lg" level="2">
            <button type="button" wire:click="toggle" aria-expanded="{{ $open ? 'true' : 'false' }}" class="flex cursor-pointer items-center gap-2 text-left">
                <flux:icon.chevron-right class="size-5 shrink-0 transition-transform {{ $open ? 'rotate-90' : '' }}" />
                {{ $name }}
            </button>
        </flux:heading>
        @can(PermissionName::ManageMusicLibrary->value)
            <flux:text size="sm" class="font-mono opacity-60">{{ $sortKey }}</flux:text>
        @endcan
    </div>

    @if ($open)
        <div class="mt-2 ml-7 space-y-1">
            @php($canManage = auth()->user()?->can(PermissionName::ManageMusicLibrary->value) ?? false)
            @forelse ($this->chapters as $chapter)
                <livewire:music-library.chapter
                    :key="'chapter-'.$chapter->id"
                    :chapter-id="$chapter->id"
                    :name="$chapter->name"
                    :sort-key="$canManage ? $chapter->sort_key : ''"
                />
            @empty
                <flux:text>No chapters yet.</flux:text>
            @endforelse

            @can(PermissionName::ManageMusicLibrary->value)
                <flux:modal.trigger :name="'add-chapter-'.$bookId">
                    <flux:button size="sm" icon="plus" class="mt-2">Add chapter</flux:button>
                </flux:modal.trigger>

                <flux:modal :name="'add-chapter-'.$bookId" class="md:w-96">
                    <form wire:submit="addChapter" class="space-y-6">
                        <flux:heading size="lg">Add chapter to {{ $name }}</flux:heading>
                        <flux:input wire:model="chapterName" label="Name" required />
                        <flux:input wire:model="chapterSortKey" label="Sort key" required />
                        <flux:textarea wire:model="chapterDescription" label="Description" />
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
