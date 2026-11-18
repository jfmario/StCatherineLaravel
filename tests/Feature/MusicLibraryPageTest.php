<?php

use App\Enums\RoleName;
use App\Models\MusicLibraryBook;
use App\Models\MusicLibraryChapter;
use App\Models\MusicLibraryEntry;
use App\Models\User;
use App\Support\MusicLibraryMedia;
use Database\Seeders\RoleSeeder;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake(MusicLibraryMedia::DISK)
        ->buildTemporaryUrlsUsing(fn (string $path): string => 'https://signed.test/'.$path);
});

function choirUser(): User
{
    return User::factory()->create()->assignRole(RoleName::Choir);
}

function entryComponent(MusicLibraryEntry $entry): Testable
{
    return Livewire::test('music-library.entry', [
        'entryId' => $entry->id,
        'name' => $entry->name,
        'sortKey' => $entry->sort_key,
    ]);
}

test('guests see book names in order without chapters', function () {
    MusicLibraryBook::factory()->create(['name' => 'Second Book', 'sort_key' => 'b']);
    $first = MusicLibraryBook::factory()->create(['name' => 'First Book', 'sort_key' => 'a']);
    MusicLibraryChapter::factory()->for($first, 'book')->create(['name' => 'Hidden Chapter']);

    $this->get(route('music-library'))
        ->assertOk()
        ->assertSeeInOrder(['First Book', 'Second Book'])
        ->assertDontSee('Hidden Chapter');
});

test('the sort key never reaches non-managers, even inside component snapshots', function () {
    $book = MusicLibraryBook::factory()->create(['sort_key' => 'secret-book-key']);
    $chapter = MusicLibraryChapter::factory()->for($book, 'book')->create(['sort_key' => 'secret-chapter-key']);
    MusicLibraryEntry::factory()->for($chapter, 'chapter')->create(['sort_key' => 'secret-entry-key']);

    $html = Livewire::test('music-library.book', ['bookId' => $book->id, 'name' => 'B', 'sortKey' => ''])
        ->call('toggle')
        ->html();

    expect($html)->not->toContain('secret-chapter-key');
});

test('a book lazily loads chapters and a chapter lazily loads entries', function () {
    $book = MusicLibraryBook::factory()->create();
    $chapter = MusicLibraryChapter::factory()->for($book, 'book')->create(['name' => 'Chapter One']);
    MusicLibraryEntry::factory()->for($chapter, 'chapter')->create(['name' => 'Entry One']);

    $bookComponent = Livewire::test('music-library.book', ['bookId' => $book->id, 'name' => $book->name, 'sortKey' => $book->sort_key])
        ->assertDontSee('Chapter One')
        ->call('toggle')
        ->assertSee('Chapter One')
        ->assertDontSee('Entry One');

    Livewire::test('music-library.chapter', ['chapterId' => $chapter->id, 'name' => $chapter->name, 'sortKey' => $chapter->sort_key])
        ->assertDontSee('Entry One')
        ->call('toggle')
        ->assertSee('Entry One');
});

test('an opened entry shows its available media', function () {
    $entry = MusicLibraryEntry::factory()->withPdf()->withYouTube()->create([
        'tradition' => 'russian',
        'tone' => 3,
        'parts' => 3,
        'icon_path' => 'icons/a.png',
        'note_playback_path' => 'notes/a.mp3',
        'example_mp3_path' => 'examples/a.mp3',
        'example_youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
    ]);

    entryComponent($entry)
        ->assertDontSee('Russian')
        ->call('toggle')
        ->assertSee('Russian')
        ->assertSee('Tone 3')
        ->assertSee('3 parts')
        ->assertSeeHtml('https://www.youtube-nocookie.com/embed/abcdefghijk')
        ->assertSeeHtml('https://signed.test/icons/a.png')
        ->assertSeeHtml('https://signed.test/notes/a.mp3')
        ->assertSeeHtml('https://signed.test/examples/a.mp3')
        ->assertSeeHtml('https://signed.test/'.$entry->sheet_music_pdf_path);
});

test('an opened entry omits media it does not have', function () {
    $entry = MusicLibraryEntry::factory()->create([
        'tradition' => null, 'tone' => null, 'parts' => 1,
    ]);

    entryComponent($entry)
        ->call('toggle')
        ->assertDontSeeHtml('<audio')
        ->assertDontSeeHtml('<iframe')
        ->assertDontSee('Sheet music')
        ->assertDontSee('Parts');
});

test('part players are lazy and only offered when part files exist', function () {
    $entry = MusicLibraryEntry::factory()->create([
        'parts' => 4,
        'note_playback_s_path' => 'parts/s.mp3',
        'note_playback_b_path' => 'parts/b.mp3',
    ]);

    entryComponent($entry)
        ->call('toggle')
        ->assertSee('Parts')
        ->assertDontSeeHtml('https://signed.test/parts/s.mp3')
        ->call('toggleParts')
        ->assertSeeHtml('https://signed.test/parts/s.mp3')
        ->assertSeeHtml('https://signed.test/parts/b.mp3')
        ->assertSee('Soprano')
        ->assertSee('Bass')
        ->assertDontSee('Alto');
});

test('sort keys and add buttons are hidden from guests', function () {
    MusicLibraryBook::factory()->create(['name' => 'Book', 'sort_key' => 'visible-key-123']);

    $this->get(route('music-library'))
        ->assertDontSee('visible-key-123')
        ->assertDontSee('Add book');
});

test('sort keys and add buttons are visible to managers', function () {
    MusicLibraryBook::factory()->create(['name' => 'Book', 'sort_key' => 'visible-key-123']);

    $this->actingAs(choirUser())
        ->get(route('music-library'))
        ->assertSee('visible-key-123')
        ->assertSee('Add book');
});

test('a manager can add a book, a chapter and an entry', function () {
    $this->actingAs(choirUser());

    Livewire::test('pages::music-library')
        ->set('name', 'New Book')
        ->set('sortKey', 'nb')
        ->call('addBook')
        ->assertHasNoErrors();

    $book = MusicLibraryBook::firstWhere('name', 'New Book');
    expect($book)->not->toBeNull();

    Livewire::test('music-library.book', ['bookId' => $book->id, 'name' => $book->name, 'sortKey' => $book->sort_key])
        ->set('chapterName', 'New Chapter')
        ->set('chapterSortKey', 'nc')
        ->call('addChapter')
        ->assertHasNoErrors();

    $chapter = $book->chapters()->firstWhere('name', 'New Chapter');
    expect($chapter)->not->toBeNull();

    Livewire::test('music-library.chapter', ['chapterId' => $chapter->id, 'name' => $chapter->name, 'sortKey' => $chapter->sort_key])
        ->set('form.name', 'New Entry')
        ->set('form.sort_key', 'ne')
        ->set('form.tradition', 'byzantine')
        ->set('form.tone', 4)
        ->set('form.parts', 3)
        ->call('addEntry')
        ->assertHasNoErrors();

    $entry = $chapter->entries()->firstWhere('name', 'New Entry');
    expect($entry->tradition->value)->toBe('byzantine')
        ->and($entry->tone)->toBe(4)
        ->and($entry->sheet_music_pdf_path)->toBeNull();
});

test('adding requires the manage permission', function (?User $user) {
    $book = MusicLibraryBook::factory()->create();
    $chapter = MusicLibraryChapter::factory()->for($book, 'book')->create();

    if ($user) {
        $this->actingAs($user);
    }

    Livewire::test('pages::music-library')->set('name', 'X')->set('sortKey', 'x')->call('addBook')->assertForbidden();
    Livewire::test('music-library.book', ['bookId' => $book->id, 'name' => 'b', 'sortKey' => 'b'])
        ->set('chapterName', 'X')->set('chapterSortKey', 'x')->call('addChapter')->assertForbidden();
    Livewire::test('music-library.chapter', ['chapterId' => $chapter->id, 'name' => 'c', 'sortKey' => 'c'])
        ->set('form.name', 'X')->set('form.sort_key', 'x')->call('addEntry')->assertForbidden();

    expect(MusicLibraryBook::count())->toBe(1)
        ->and(MusicLibraryEntry::count())->toBe(0);
})->with([
    'guest' => fn () => null,
    'editor' => fn () => User::factory()->create()->assignRole(RoleName::Editor),
]);

test('add forms validate input', function () {
    $this->actingAs(choirUser());
    $chapter = MusicLibraryChapter::factory()->create();

    Livewire::test('pages::music-library')->call('addBook')->assertHasErrors(['name', 'sortKey']);

    Livewire::test('music-library.chapter', ['chapterId' => $chapter->id, 'name' => 'c', 'sortKey' => 'c'])
        ->set('form.name', 'X')->set('form.sort_key', 'x')
        ->set('form.parts', 3)->set('form.tone', 9)->set('form.tradition', 'gregorian')->set('form.example_youtube_url', 'nope')
        ->call('addEntry')
        ->assertHasErrors(['form.tone', 'form.tradition', 'form.example_youtube_url']);
});

test('youtube embed urls are derived from common link shapes', function (?string $input, ?string $expected) {
    expect(MusicLibraryMedia::youtubeEmbedUrl($input))->toBe($expected);
})->with([
    'watch' => ['https://www.youtube.com/watch?v=abcdefghijk', 'https://www.youtube-nocookie.com/embed/abcdefghijk'],
    'watch with extra params' => ['https://www.youtube.com/watch?feature=share&v=abcdefghijk', 'https://www.youtube-nocookie.com/embed/abcdefghijk'],
    'short link' => ['https://youtu.be/abcdefghijk?t=3', 'https://www.youtube-nocookie.com/embed/abcdefghijk'],
    'embed' => ['https://www.youtube.com/embed/abcdefghijk', 'https://www.youtube-nocookie.com/embed/abcdefghijk'],
    'not youtube' => ['https://example.com/watch?v=abcdefghijk', null],
    'null' => [null, null],
]);

function chapterForm(): Testable
{
    test()->actingAs(choirUser());
    $chapter = MusicLibraryChapter::factory()->create();

    return Livewire::test('music-library.chapter', ['chapterId' => $chapter->id, 'name' => 'c', 'sortKey' => 'c']);
}

function putLibraryFiles(string ...$keys): void
{
    foreach ($keys as $key) {
        Storage::disk(MusicLibraryMedia::DISK)->put($key, 'x');
    }
}

test('populating a 4 part path fills every file field it finds', function () {
    putLibraryFiles('a/b/c.pdf', 'a/b/c.mp3', 'a/b/c-example.mp3', 'a/b/c-s.mp3', 'a/b/c-a.mp3', 'a/b/c-t.mp3', 'a/b/c-b.mp3', 'a/b/c-m.mp3');

    chapterForm()
        ->set('form.parts', 4)
        ->set('form.s3_path', '/a/b/c')
        ->call('populateFromPath')
        ->assertHasNoErrors()
        ->assertSet('scanWarning', null)
        ->assertSet('form.sheet_music_pdf_path', 'a/b/c.pdf')
        ->assertSet('form.note_playback_path', 'a/b/c.mp3')
        ->assertSet('form.example_mp3_path', 'a/b/c-example.mp3')
        ->assertSet('form.note_playback_s_path', 'a/b/c-s.mp3')
        ->assertSet('form.note_playback_a_path', 'a/b/c-a.mp3')
        ->assertSet('form.note_playback_t_path', 'a/b/c-t.mp3')
        ->assertSet('form.note_playback_b_path', 'a/b/c-b.mp3');
});

test('populating a 2 part path maps melody to soprano and ison to bass', function () {
    putLibraryFiles('a/c-m.mp3', 'a/c-i.mp3', 'a/c-s.mp3', 'a/c-b.mp3');

    chapterForm()
        ->set('form.parts', 2)
        ->set('form.s3_path', 'a/c')
        ->call('populateFromPath')
        ->assertSet('form.note_playback_s_path', 'a/c-m.mp3')
        ->assertSet('form.note_playback_b_path', 'a/c-i.mp3')
        ->assertSet('form.note_playback_a_path', '')
        ->assertSet('form.note_playback_t_path', '');
});

test('populating a 1 part path ignores part files', function () {
    putLibraryFiles('a/c.mp3', 'a/c-s.mp3', 'a/c-m.mp3');

    chapterForm()
        ->set('form.parts', 1)
        ->set('form.s3_path', 'a/c')
        ->call('populateFromPath')
        ->assertSet('form.note_playback_path', 'a/c.mp3')
        ->assertSet('form.note_playback_s_path', '');
});

test('populating fills only the files that exist and keeps other values', function () {
    putLibraryFiles('a/c.pdf');

    chapterForm()
        ->set('form.parts', 3)
        ->set('form.example_mp3_path', 'typed/by-hand.mp3')
        ->set('form.s3_path', 'a/c')
        ->call('populateFromPath')
        ->assertSet('form.sheet_music_pdf_path', 'a/c.pdf')
        ->assertSet('form.note_playback_path', '')
        ->assertSet('form.example_mp3_path', 'typed/by-hand.mp3');
});

test('populating warns when no files are found', function () {
    putLibraryFiles('other/path.pdf');

    chapterForm()
        ->call('toggle')
        ->set('form.parts', 3)
        ->set('form.s3_path', 'a/c')
        ->call('populateFromPath')
        ->assertHasNoErrors()
        ->assertSee('No files were found')
        ->assertSet('form.sheet_music_pdf_path', '');
});

test('populating requires parts and an extensionless path', function () {
    chapterForm()
        ->set('form.s3_path', 'a/c')
        ->call('populateFromPath')
        ->assertHasErrors(['form.parts'])
        ->set('form.parts', 3)
        ->set('form.s3_path', 'a/c.pdf')
        ->call('populateFromPath')
        ->assertHasErrors(['form.s3_path'])
        ->set('form.s3_path', '')
        ->call('populateFromPath')
        ->assertHasErrors(['form.s3_path']);
});

test('populating is forbidden without the manage permission', function () {
    $chapter = MusicLibraryChapter::factory()->create();

    Livewire::test('music-library.chapter', ['chapterId' => $chapter->id, 'name' => 'c', 'sortKey' => 'c'])
        ->set('form.parts', 3)->set('form.s3_path', 'a/c')
        ->call('populateFromPath')
        ->assertForbidden();
});

test('a drive failure while populating is thrown, not treated as missing files', function () {
    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('exists')->andThrow(new RuntimeException('S3 is unreachable'));
    Storage::set(MusicLibraryMedia::DISK, $disk);

    expect(fn () => chapterForm()
        ->set('form.parts', 3)
        ->set('form.s3_path', 'a/c')
        ->call('populateFromPath')
    )->toThrow(RuntimeException::class, 'S3 is unreachable');
});

test('the music library disk throws on drive errors', function () {
    expect(config('filesystems.disks.music_library.throw'))->toBeTrue();
});

test('the parts section labels two part music as melody and ison', function () {
    $entry = MusicLibraryEntry::factory()->create([
        'parts' => 2,
        'note_playback_s_path' => 'p/m.mp3',
        'note_playback_b_path' => 'p/i.mp3',
        'note_playback_a_path' => 'p/stray.mp3',
    ]);

    entryComponent($entry)
        ->call('toggle')
        ->call('toggleParts')
        ->assertSee('Melody')
        ->assertSee('Ison')
        ->assertDontSee('Soprano')
        ->assertDontSee('Alto')
        ->assertSeeHtml('https://signed.test/p/m.mp3')
        ->assertSeeHtml('https://signed.test/p/i.mp3')
        ->assertDontSeeHtml('https://signed.test/p/stray.mp3');
});

test('a manager can edit an entry', function () {
    $this->actingAs(choirUser());
    $entry = MusicLibraryEntry::factory()->create([
        'name' => 'Old Name',
        'tone' => 2,
        'sheet_music_pdf_path' => 'old.pdf',
    ]);

    entryComponent($entry)
        ->call('edit')
        ->assertSet('form.name', 'Old Name')
        ->assertSet('form.tone', 2)
        ->assertSet('form.sheet_music_pdf_path', 'old.pdf')
        ->set('form.name', 'New Name')
        ->set('form.tone', '')
        ->set('form.sheet_music_pdf_path', '')
        ->call('updateEntry')
        ->assertHasNoErrors()
        ->assertSet('name', 'New Name');

    $entry->refresh();
    expect($entry->name)->toBe('New Name')
        ->and($entry->tone)->toBeNull()
        ->and($entry->sheet_music_pdf_path)->toBeNull();
});

test('the edit form validates input', function () {
    $this->actingAs(choirUser());
    $entry = MusicLibraryEntry::factory()->create();

    entryComponent($entry)
        ->call('edit')
        ->set('form.name', '')
        ->set('form.tone', 9)
        ->call('updateEntry')
        ->assertHasErrors(['form.name', 'form.tone']);
});

test('editing requires the manage permission', function (?User $user) {
    $entry = MusicLibraryEntry::factory()->create(['name' => 'Keep']);

    if ($user) {
        $this->actingAs($user);
    }

    entryComponent($entry)->call('edit')->assertForbidden();
    entryComponent($entry)->set('form.name', 'Hacked')->call('updateEntry')->assertForbidden();

    expect($entry->refresh()->name)->toBe('Keep');
})->with([
    'guest' => fn () => null,
    'editor' => fn () => User::factory()->create()->assignRole(RoleName::Editor),
]);

test('the edit button is only shown to managers', function () {
    $entry = MusicLibraryEntry::factory()->create();

    entryComponent($entry)->call('toggle')->assertDontSee('Edit entry');

    $this->actingAs(choirUser());
    entryComponent($entry)->call('toggle')->assertSee('Edit entry');
});
