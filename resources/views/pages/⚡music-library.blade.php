<?php

use App\Enums\PermissionName;
use App\Models\MusicLibraryBook;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::site')] #[Title('Music Library')] class extends Component {
    public string $name = '';
    public string $sortKey = '';
    public string $description = '';

    /**
     * Book names only; chapters and entries are fetched when opened.
     *
     * @return Collection<int, MusicLibraryBook>
     */
    #[Computed]
    public function books(): Collection
    {
        return MusicLibraryBook::query()->orderBy('sort_key')->orderBy('name')->get(['id', 'name', 'sort_key']);
    }

    /**
     * Add a book to the library.
     */
    public function addBook(): void
    {
        abort_unless(auth()->user()?->can(PermissionName::ManageMusicLibrary->value), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sortKey' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        MusicLibraryBook::create([
            'name' => $validated['name'],
            'sort_key' => $validated['sortKey'],
            'description' => $validated['description'] ?: null,
        ]);

        unset($this->books);
        $this->reset('name', 'sortKey', 'description');

        Flux::modal('add-book')->close();
        Flux::toast(variant: 'success', text: __('Book added.'));
    }
}; ?>

<flux:container class="space-y-8 py-10">
    <section>
        <flux:heading size="xl" level="1" class="mb-3">Music Library</flux:heading>
        <flux:text size="lg">Choir music organized by book and chapter. Open a book to see its chapters, and a chapter to see its entries.</flux:text>
    </section>

    <section class="border-t border-[var(--liturgical-border)]">
        @php($canManage = auth()->user()?->can(\App\Enums\PermissionName::ManageMusicLibrary->value) ?? false)
        @forelse ($this->books as $book)
            <livewire:music-library.book
                :key="'book-'.$book->id"
                :book-id="$book->id"
                :name="$book->name"
                :sort-key="$canManage ? $book->sort_key : ''"
            />
        @empty
            <flux:text class="py-4">No music has been added yet.</flux:text>
        @endforelse
    </section>

    @can(\App\Enums\PermissionName::ManageMusicLibrary->value)
        <flux:modal.trigger name="add-book">
            <flux:button icon="plus">Add book</flux:button>
        </flux:modal.trigger>

        <flux:modal name="add-book" class="md:w-96">
            <form wire:submit="addBook" class="space-y-6">
                <flux:heading size="lg">Add book</flux:heading>
                <flux:input wire:model="name" label="Name" required />
                <flux:input wire:model="sortKey" label="Sort key" required />
                <flux:textarea wire:model="description" label="Description" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">Save</flux:button>
                </div>
            </form>
        </flux:modal>
    @endcan
</flux:container>
