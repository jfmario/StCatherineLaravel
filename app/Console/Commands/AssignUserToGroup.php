<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('group:assign {email : The email of an existing user} {group : The group slug (admin, choir, council, editor)}')]
#[Description('Add an existing user to a group')]
class AssignUserToGroup extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }

        $group = Group::where('slug', $this->argument('group'))->first();

        if ($group === null) {
            $this->error('No group found with that slug. Run the GroupSeeder first?');

            return self::FAILURE;
        }

        $user->groups()->syncWithoutDetaching([$group->id]);

        $this->info("{$user->email} is now in the {$group->name} group.");

        return self::SUCCESS;
    }
}
