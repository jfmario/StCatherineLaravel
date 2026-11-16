<?php

namespace Database\Factories;

use App\Enums\MusicTradition;
use App\Models\MusicLibraryChapter;
use App\Models\MusicLibraryEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MusicLibraryEntry>
 */
class MusicLibraryEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'music_library_chapter_id' => MusicLibraryChapter::factory(),
            'name' => fake()->words(3, true),
            'sort_key' => fake()->unique()->numerify('entry-####'),
            'description' => fake()->sentence(),
            'tradition' => fake()->optional()->randomElement(MusicTradition::cases()),
            'tone' => fake()->optional()->numberBetween(1, 8),
            'parts' => fake()->numberBetween(1, 4),
        ];
    }

    /**
     * Indicate that the entry has sheet music.
     */
    public function withPdf(): static
    {
        return $this->state(fn (): array => [
            'sheet_music_pdf_path' => 'music/'.fake()->slug().'.pdf',
        ]);
    }

    /**
     * Indicate that the entry has an example YouTube link.
     */
    public function withYouTube(): static
    {
        return $this->state(fn (): array => [
            'example_youtube_url' => 'https://www.youtube.com/watch?v='.fake()->regexify('[A-Za-z0-9_-]{11}'),
        ]);
    }
}
