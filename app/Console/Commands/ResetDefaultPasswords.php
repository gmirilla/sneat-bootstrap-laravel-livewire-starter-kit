<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Accounts created by agents / the third-party API used to get the shared password "Password".
 * Find them and replace the password with an unguessable one (owners can use "forgot password").
 */
class ResetDefaultPasswords extends Command
{
    protected $signature = 'users:reset-default-passwords {--dry-run : List affected accounts without changing them}';

    protected $description = 'Replace the legacy shared default password ("Password") with a random one';

    public function handle(): int
    {
        $affected = 0;

        User::query()->select(['id', 'email', 'role', 'password'])->orderBy('id')->chunkById(200, function ($users) use (&$affected) {
            foreach ($users as $user) {
                if (!Hash::check('Password', $user->password)) {
                    continue;
                }

                $affected++;
                $this->line("#{$user->id} {$user->email} ({$user->role})");

                if (!$this->option('dry-run')) {
                    User::whereKey($user->id)->update(['password' => Hash::make(Str::random(40))]);
                }
            }
        });

        $this->info($this->option('dry-run')
            ? "{$affected} account(s) still use the default password (dry run, nothing changed)."
            : "{$affected} account(s) reset.");

        return self::SUCCESS;
    }
}
