<?php

namespace Database\Factories;

use App\Models\MusicLibraryBook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MusicLibraryBook>
 */
class MusicLibraryBookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sort_key' => fake()->unique()->numerify('book-####'),
            'description' => fake()->sentence(),
        ];
    }
}
