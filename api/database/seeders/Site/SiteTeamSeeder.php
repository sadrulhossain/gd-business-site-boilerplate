<?php

namespace Database\Seeders\Site;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

/** Sample team (no photos; add them in the admin). Run by hand (see SiteSettingsSeeder). */
class SiteTeamSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Helen Brooks', 'Managing Director', 'Helen founded Northwind after fifteen years leading finance teams. She works with clients on strategy and funding.'],
            ['Marcus Lee', 'Head of Operations', 'Marcus turns plans into routines that work every day, from small shops to multi-site teams.'],
            ['Sofia Alvarez', 'Marketing Lead', 'Sofia shapes brands and campaigns with a focus on measurable results.'],
            ['Tom Whitfield', 'Senior Consultant', 'Tom coaches owners and managers through growth, hiring and change.'],
        ];

        foreach ($rows as $i => [$name, $role, $bio]) {
            TeamMember::query()->updateOrCreate(['name' => $name], [
                'role' => $role,
                'bio' => $bio,
                'sort_order' => ($i + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
