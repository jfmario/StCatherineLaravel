<?php

namespace Database\Seeders;

use App\Enums\GroupSlug;
use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Seed the application groups. Idempotent and safe to run in production.
     */
    public function run(): void
    {
        foreach (GroupSlug::cases() as $slug) {
            Group::updateOrCreate(
                ['slug' => $slug->value],
                ['name' => $slug->label()],
            );
        }
    }
}
