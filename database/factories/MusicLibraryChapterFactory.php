<?php

namespace Database\Factories;

use App\Models\MusicLibraryBook;
use App\Models\MusicLibraryChapter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MusicLibraryChapter>
 */
class MusicLibraryChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'music_library_book_id' => MusicLibraryBook::factory(),
            'name' => fake()->words(3, true),
            'sort_key' => fake()->unique()->numerify('chapter-####'),
            'description' => fake()->sentence(),
        ];
    }
}
