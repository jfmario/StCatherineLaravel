<?php

namespace App\Models;

use Database\Factories\MusicLibraryChapterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $music_library_book_id
 * @property string $name
 * @property string $sort_key
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['music_library_book_id', 'name', 'sort_key', 'description'])]
class MusicLibraryChapter extends Model
{
    /** @use HasFactory<MusicLibraryChapterFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<MusicLibraryBook, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(MusicLibraryBook::class, 'music_library_book_id');
    }

    /**
     * @return HasMany<MusicLibraryEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(MusicLibraryEntry::class)->orderBy('sort_key')->orderBy('name');
    }
}
