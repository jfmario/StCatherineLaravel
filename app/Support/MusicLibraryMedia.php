<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves Music Library file columns to browser-usable URLs.
 */
class MusicLibraryMedia
{
    public const DISK = 'music_library';

    public const URL_LIFETIME_MINUTES = 30;

    /**
     * A short-lived signed URL for an object key on the private music library disk.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->temporaryUrl($path, now()->addMinutes(self::URL_LIFETIME_MINUTES));
    }

    /**
     * The privacy-friendly embed URL for a YouTube watch, share, or embed link.
     */
    public static function youtubeEmbedUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $pattern = '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

        if (! preg_match($pattern, $url, $matches)) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/'.$matches[1];
    }
}
