<?php

namespace Database\Factories;

use App\Enums\GroupSlug;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => str($name)->slug()->toString(),
        ];
    }

    /**
     * Use one of the well-known application groups.
     */
    public function slug(GroupSlug $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $slug->label(),
            'slug' => $slug->value,
        ]);
    }
}
