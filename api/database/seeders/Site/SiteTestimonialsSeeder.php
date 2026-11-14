<?php

namespace Database\Seeders\Site;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/** Sample testimonials. Run by hand (see SiteSettingsSeeder). */
class SiteTestimonialsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Amelia Carter', 'Owner, Carter Bakery', 'Northwind rebuilt our pricing and our margins improved within a quarter. Honest advice, no jargon.'],
            ['Daniel Okafor', 'Director, Okafor Logistics', 'They found three process bottlenecks we had lived with for years. Deliveries are now a day faster.'],
            ['Priya Nair', 'Founder, Bloom Studio', 'From the first call I felt understood. The new brand and website doubled our enquiries.'],
        ];

        foreach ($rows as $i => [$name, $role, $quote]) {
            Testimonial::query()->updateOrCreate(['author_name' => $name], [
                'author_role' => $role,
                'quote' => $quote,
                'sort_order' => ($i + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
