<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email : Existing account email}';
    protected $description = 'Grant admin access to an existing user account';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error('No user found with that email address.');
            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => true])->save();

        $this->info("Admin access granted to {$user->email}.");

        return self::SUCCESS;
    }
}
