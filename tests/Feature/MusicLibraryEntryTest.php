<?php

use App\Enums\MusicTradition;
use App\Models\MusicLibraryBook;
use App\Models\MusicLibraryChapter;
use App\Models\MusicLibraryEntry;

function bareEntry(array $attributes = []): MusicLibraryEntry
{
    return MusicLibraryEntry::factory()->create([
        'tradition' => null,
        'tone' => null,
        ...$attributes,
    ]);
}

test('an entry with no media is not ready', function () {
    expect(bareEntry()->is_ready)->toBeFalse();
});

test('an entry is ready with any one qualifying media field', function (string $column, string $value) {
    $entry = bareEntry([$column => $value]);

    expect($entry->is_ready)->toBeTrue()
        ->and(MusicLibraryEntry::ready()->whereKey($entry->id)->exists())->toBeTrue();
})->with([
    'sheet music pdf' => ['sheet_music_pdf_path', 'a.pdf'],
    'note playback' => ['note_playback_path', 'a.mid'],
    'example mp3' => ['example_mp3_path', 'a.mp3'],
    'example youtube' => ['example_youtube_url', 'https://youtu.be/abc'],
]);

test('icon and part-specific playback files do not make an entry ready', function () {
    $entry = bareEntry([
        'icon_path' => 'icon.png',
        'note_playback_s_path' => 's.mid',
        'note_playback_a_path' => 'a.mid',
        'note_playback_t_path' => 't.mid',
        'note_playback_b_path' => 'b.mid',
        'example_mp3_path' => '',
    ]);

    expect($entry->is_ready)->toBeFalse()
        ->and(MusicLibraryEntry::ready()->whereKey($entry->id)->exists())->toBeFalse();
});

test('an entry defaults to two parts with nullable tradition and tone', function () {
    $entry = MusicLibraryEntry::create([
        'music_library_chapter_id' => MusicLibraryChapter::factory()->create()->id,
        'name' => 'Troparion',
        'sort_key' => 'a',
    ])->refresh();

    expect($entry->parts)->toBe(2)
        ->and($entry->tradition)->toBeNull()
        ->and($entry->tone)->toBeNull();
});

test('tradition and tone are cast', function () {
    $entry = bareEntry(['tradition' => 'russian', 'tone' => 5])->refresh();

    expect($entry->tradition)->toBe(MusicTradition::Russian)
        ->and($entry->tone)->toBe(5);
});

test('books own chapters which own entries, and deleting a book cascades', function () {
    $entry = MusicLibraryEntry::factory()->create();
    $chapter = $entry->chapter;
    $book = $chapter->book;

    expect($book->chapters->pluck('id')->all())->toBe([$chapter->id])
        ->and($chapter->entries->pluck('id')->all())->toBe([$entry->id]);

    $book->delete();

    expect(MusicLibraryChapter::count())->toBe(0)
        ->and(MusicLibraryEntry::count())->toBe(0)
        ->and(MusicLibraryBook::count())->toBe(0);
});
