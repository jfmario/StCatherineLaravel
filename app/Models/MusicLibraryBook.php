<?php

namespace App\Models;

use Database\Factories\MusicLibraryBookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $sort_key
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'sort_key', 'description'])]
class MusicLibraryBook extends Model
{
    /** @use HasFactory<MusicLibraryBookFactory> */
    use HasFactory;

    /**
     * @return HasMany<MusicLibraryChapter, $this>
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(MusicLibraryChapter::class)->orderBy('sort_key')->orderBy('name');
    }
}
