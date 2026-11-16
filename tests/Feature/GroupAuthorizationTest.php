<?php

use App\Enums\GroupSlug;
use App\Models\Group;
use App\Models\User;
use Database\Seeders\GroupSeeder;
use Illuminate\Support\Facades\Gate;

function userInGroup(GroupSlug $slug): User
{
    $user = User::factory()->create();
    $user->groups()->attach(Group::factory()->slug($slug)->create());

    return $user;
}

test('the group seeder creates the application groups and is idempotent', function () {
    $this->seed(GroupSeeder::class);
    $this->seed(GroupSeeder::class);

    expect(Group::orderBy('slug')->pluck('name', 'slug')->all())->toBe([
        'admin' => 'Admin',
        'choir' => 'Choir',
        'council' => 'Council',
        'editor' => 'Editor',
    ]);
});

test('group:assign adds a user to a group without duplicating', function () {
    $this->seed(GroupSeeder::class);
    $user = User::factory()->create();

    $this->artisan('group:assign', ['email' => $user->email, 'group' => 'choir'])->assertSuccessful();
    $this->artisan('group:assign', ['email' => $user->email, 'group' => 'choir'])->assertSuccessful();

    expect($user->groups()->pluck('slug')->all())->toBe(['choir']);
});

test('group:assign fails for an unknown user or group', function () {
    $this->seed(GroupSeeder::class);
    $user = User::factory()->create();

    $this->artisan('group:assign', ['email' => 'nobody@example.com', 'group' => 'choir'])->assertFailed();
    $this->artisan('group:assign', ['email' => $user->email, 'group' => 'nope'])->assertFailed();

    expect($user->groups()->count())->toBe(0);
});

test('inGroup checks membership by enum or slug', function () {
    $user = userInGroup(GroupSlug::Choir);

    expect($user->inGroup(GroupSlug::Choir))->toBeTrue()
        ->and($user->inGroup('council', 'choir'))->toBeTrue()
        ->and($user->inGroup(GroupSlug::Council))->toBeFalse()
        ->and($user->isAdmin())->toBeFalse();
});

test('admins pass every gate, including undefined ones', function () {
    $admin = userInGroup(GroupSlug::Admin);

    expect(Gate::forUser($admin)->allows('anything-at-all'))->toBeTrue();
});

test('non-admins are denied undefined gates', function () {
    expect(Gate::forUser(userInGroup(GroupSlug::Choir))->allows('anything-at-all'))->toBeFalse();
});

test('defined gates still apply to non-admins', function () {
    Gate::define('edit-music', fn (User $user) => $user->inGroup(GroupSlug::Editor));

    expect(Gate::forUser(userInGroup(GroupSlug::Editor))->allows('edit-music'))->toBeTrue()
        ->and(Gate::forUser(userInGroup(GroupSlug::Choir))->allows('edit-music'))->toBeFalse();
});
