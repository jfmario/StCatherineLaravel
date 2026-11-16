<?php

namespace App\Models;

use App\Enums\MusicTradition;
use Database\Factories\MusicLibraryEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * File columns hold object keys on the read-only `music_library` disk.
 *
 * @property int $id
 * @property int $music_library_chapter_id
 * @property string $name
 * @property string $sort_key
 * @property string|null $description
 * @property MusicTradition|null $tradition
 * @property int|null $tone Octoechos tone, 1-8
 * @property int $parts Number of vocal parts, 1-4
 * @property string|null $icon_path
 * @property string|null $sheet_music_pdf_path
 * @property string|null $note_playback_path
 * @property string|null $note_playback_s_path
 * @property string|null $note_playback_a_path
 * @property string|null $note_playback_t_path
 * @property string|null $note_playback_b_path
 * @property string|null $example_mp3_path
 * @property string|null $example_youtube_url
 * @property-read bool $is_ready
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'music_library_chapter_id',
    'name',
    'sort_key',
    'description',
    'tradition',
    'tone',
    'parts',
    'icon_path',
    'sheet_music_pdf_path',
    'note_playback_path',
    'note_playback_s_path',
    'note_playback_a_path',
    'note_playback_t_path',
    'note_playback_b_path',
    'example_mp3_path',
    'example_youtube_url',
])]
class MusicLibraryEntry extends Model
{
    /** @use HasFactory<MusicLibraryEntryFactory> */
    use HasFactory;

    /**
     * Columns of which at least one must be filled for an entry to be ready.
     *
     * @var list<string>
     */
    public const READINESS_COLUMNS = [
        'sheet_music_pdf_path',
        'note_playback_path',
        'example_mp3_path',
        'example_youtube_url',
    ];

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'parts' => 2,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tradition' => MusicTradition::class,
            'tone' => 'integer',
            'parts' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<MusicLibraryChapter, $this>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(MusicLibraryChapter::class, 'music_library_chapter_id');
    }

    /**
     * Whether the entry has at least a PDF, note playback, MP3, or YouTube link.
     *
     * @return Attribute<bool, never>
     */
    protected function isReady(): Attribute
    {
        return Attribute::get(function (): bool {
            foreach (self::READINESS_COLUMNS as $column) {
                if (filled($this->getAttribute($column))) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Limit the query to entries that are ready.
     *
     * @param  Builder<MusicLibraryEntry>  $query
     */
    public function scopeReady(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            foreach (self::READINESS_COLUMNS as $column) {
                $query->orWhere(function (Builder $query) use ($column): void {
                    $query->whereNotNull($column)->where($column, '!=', '');
                });
            }
        });
    }
}
