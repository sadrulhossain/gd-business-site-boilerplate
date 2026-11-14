<?php

namespace Database\Seeders\Site;

use App\Models\Service;
use Illuminate\Database\Seeder;

/** Sample services (no images; add them in the admin). Run by hand (see SiteSettingsSeeder). */
class SiteServicesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Business strategy', 'A clear plan for the next one to three years: goals, priorities and the steps to reach them.'],
            ['Marketing and branding', 'Positioning, website and campaigns that bring in the right customers.'],
            ['Financial planning', 'Budgets, forecasts and cash-flow reviews so you always know where you stand.'],
            ['Operations review', 'We find the bottlenecks in your processes and fix them with simple, lasting changes.'],
            ['Team coaching', 'Hiring, onboarding and leadership support for growing teams.'],
            ['Digital transformation', 'Pick and roll out the tools that save time instead of adding work.'],
        ];

        foreach ($rows as $i => [$title, $description]) {
            Service::query()->updateOrCreate(['title' => $title], [
                'description' => $description,
                'sort_order' => ($i + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
