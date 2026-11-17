<?php

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

test('the role seeder creates the application roles and is idempotent', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::orderBy('name')->pluck('name')->all())->toBe(['Admin', 'Choir', 'Council', 'Editor']);
});

test('admins pass every gate, including undefined ones', function () {
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create()->assignRole(RoleName::Admin);

    expect(Gate::forUser($admin)->allows('anything-at-all'))->toBeTrue();
});

test('non-admins are denied undefined gates', function () {
    $this->seed(RoleSeeder::class);
    $choir = User::factory()->create()->assignRole(RoleName::Choir);

    expect(Gate::forUser($choir)->allows('anything-at-all'))->toBeFalse();
});

test('defined gates still apply to non-admins', function () {
    $this->seed(RoleSeeder::class);
    Gate::define('edit-music', fn (User $user) => $user->hasRole(RoleName::Editor));

    $editor = User::factory()->create()->assignRole(RoleName::Editor);
    $choir = User::factory()->create()->assignRole(RoleName::Choir);

    expect(Gate::forUser($editor)->allows('edit-music'))->toBeTrue()
        ->and(Gate::forUser($choir)->allows('edit-music'))->toBeFalse();
});
