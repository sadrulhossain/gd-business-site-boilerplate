<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/** Creates the superadmin from SUPERADMIN_* in api/.env (env is the source of truth). */
class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('access.superadmin.email');
        $password = config('access.superadmin.password');

        if (! $email || ! $password) {
            $this->command?->warn('SuperadminSeeder skipped: set SUPERADMIN_EMAIL and SUPERADMIN_PASSWORD in api/.env');

            return;
        }

        $user = User::query()->updateOrCreate(['email' => $email], [
            'name' => config('access.superadmin.name'),
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([config('access.superadmin_role')]);
    }
}
