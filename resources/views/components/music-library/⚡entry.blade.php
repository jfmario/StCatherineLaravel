<?php

use App\Enums\PermissionName;
use App\Models\MusicLibraryEntry;
use App\Support\MusicLibraryMedia;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int $entryId;
    public string $name;
    public string $sortKey;
    public bool $open = false;
    public bool $showParts = false;

    /**
     * The full entry is only fetched once it is opened.
     */
    #[Computed]
    public function entry(): MusicLibraryEntry
    {
        return MusicLibraryEntry::findOrFail($this->entryId);
    }

    /**
     * Whether any per-part playback file exists for the entry.
     */
    #[Computed]
    public function hasParts(): bool
    {
        return $this->entry->parts > 1 && count($this->partPaths()) > 0;
    }

    /**
     * Open or close the entry.
     */
    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    /**
     * Show or hide the per-part players.
     */
    public function toggleParts(): void
    {
        $this->showParts = ! $this->showParts;
    }

    /**
     * Part label => object key, for the parts that have a file.
     *
     * @return array<string, string>
     */
    public function partPaths(): array
    {
        $entry = $this->entry;

        $labelled = $entry->parts === 2
            ? ['Melody' => $entry->note_playback_s_path, 'Ison' => $entry->note_playback_b_path]
            : [
                'Soprano' => $entry->note_playback_s_path,
                'Alto' => $entry->note_playback_a_path,
                'Tenor' => $entry->note_playback_t_path,
                'Bass' => $entry->note_playback_b_path,
            ];

        return array_filter($labelled, filled(...));
    }
}; ?>

<div class="py-1">
    <div class="flex items-baseline gap-3">
        <flux:heading level="4">
            <button type="button" wire:click="toggle" aria-expanded="{{ $open ? 'true' : 'false' }}" class="flex cursor-pointer items-center gap-2 text-left">
                <flux:icon.chevron-right class="size-4 shrink-0 transition-transform {{ $open ? 'rotate-90' : '' }}" />
                {{ $name }}
            </button>
        </flux:heading>
        @can(PermissionName::ManageMusicLibrary->value)
            <flux:text size="sm" class="font-mono opacity-60">{{ $sortKey }}</flux:text>
        @endcan
    </div>

    @if ($open)
        @php
            $entry = $this->entry;
            $iconUrl = MusicLibraryMedia::url($entry->icon_path);
            $pdfUrl = MusicLibraryMedia::url($entry->sheet_music_pdf_path);
            $noteUrl = MusicLibraryMedia::url($entry->note_playback_path);
            $exampleUrl = MusicLibraryMedia::url($entry->example_mp3_path);
            $youtubeUrl = MusicLibraryMedia::youtubeEmbedUrl($entry->example_youtube_url);
        @endphp

        <flux:card class="mt-2 ml-6">
            <div class="grid gap-6 md:grid-cols-[1fr_auto]">
                <div class="min-w-0 space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($entry->tradition)
                            <flux:badge>{{ $entry->tradition->name }}</flux:badge>
                        @endif
                        @if ($entry->tone)
                            <flux:badge>Tone {{ $entry->tone }}</flux:badge>
                        @endif
                        <flux:badge>{{ $entry->parts }} {{ \Illuminate\Support\Str::plural('part', $entry->parts) }}</flux:badge>
                    </div>

                    @if (filled($entry->description))
                        <flux:text>{{ $entry->description }}</flux:text>
                    @endif

                    @if ($youtubeUrl)
                        <iframe
                            src="{{ $youtubeUrl }}"
                            title="{{ $entry->name }}"
                            class="aspect-video w-full max-w-xl rounded"
                            loading="lazy"
                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    @endif

                    @if ($noteUrl)
                        <div class="space-y-1">
                            <flux:text size="sm">Note playback</flux:text>
                            <audio controls preload="none" src="{{ $noteUrl }}" class="w-full max-w-xl"></audio>
                        </div>
                    @endif

                    @if ($exampleUrl)
                        <div class="space-y-1">
                            <flux:text size="sm">Example</flux:text>
                            <audio controls preload="none" src="{{ $exampleUrl }}" class="w-full max-w-xl"></audio>
                        </div>
                    @endif

                    @if ($pdfUrl)
                        <flux:link :href="$pdfUrl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2">
                            <flux:icon.document-text class="size-5" />
                            Sheet music (PDF)
                        </flux:link>
                    @endif

                    @if ($this->hasParts)
                        <div class="space-y-3">
                            <flux:button size="sm" wire:click="toggleParts" :icon="$showParts ? 'chevron-down' : 'chevron-right'">Parts</flux:button>

                            @if ($showParts)
                                <div class="space-y-3">
                                    @foreach ($this->partPaths() as $label => $path)
                                        <div class="space-y-1" wire:key="part-{{ $label }}">
                                            <flux:text size="sm">{{ $label }}</flux:text>
                                            <audio controls preload="none" src="{{ MusicLibraryMedia::url($path) }}" class="w-full max-w-xl"></audio>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($iconUrl)
                    <img src="{{ $iconUrl }}" alt="{{ $entry->name }}" class="w-full max-w-48 rounded md:justify-self-end">
                @endif
            </div>
        </flux:card>
    @endif
</div>
