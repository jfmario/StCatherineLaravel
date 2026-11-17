<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Finds the files that exist for a Music Library entry from its extensionless base path.
 *
 * The disk must be configured to throw, so a drive failure is never mistaken for a missing file.
 */
class MusicLibraryScanner
{
    /**
     * Part suffix => entry column, for 3 and 4 part music.
     *
     * @var array<string, string>
     */
    public const CHOIR_PARTS = [
        '-s' => 'note_playback_s_path',
        '-a' => 'note_playback_a_path',
        '-t' => 'note_playback_t_path',
        '-b' => 'note_playback_b_path',
    ];

    /**
     * Part suffix => entry column, for 2 part music (melody and ison).
     *
     * @var array<string, string>
     */
    public const TWO_PART = [
        '-m' => 'note_playback_s_path',
        '-i' => 'note_playback_b_path',
    ];

    /**
     * Entry column => object key for every file that exists.
     *
     * @return array<string, string>
     */
    public static function scan(string $basePath, int $parts): array
    {
        $basePath = ltrim(trim($basePath), '/');

        $candidates = [
            'sheet_music_pdf_path' => $basePath.'.pdf',
            'note_playback_path' => $basePath.'.mp3',
            'example_mp3_path' => $basePath.'-example.mp3',
        ];

        $partSuffixes = match (true) {
            $parts >= 3 => self::CHOIR_PARTS,
            $parts === 2 => self::TWO_PART,
            default => [],
        };

        foreach ($partSuffixes as $suffix => $column) {
            $candidates[$column] = $basePath.$suffix.'.mp3';
        }

        $disk = Storage::disk(MusicLibraryMedia::DISK);

        return array_filter($candidates, fn (string $key): bool => $disk->exists($key));
    }
}
