<?php

namespace Database\Seeders\Site;

use App\Models\Section;
use Illuminate\Database\Seeder;

/** Sample section copy; turns every section on. Run by hand (see SiteSettingsSeeder). */
class SiteSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $copy = [
            'hero' => ['Grow with confidence', 'We help local businesses plan, launch and scale with clear, honest guidance and hands-on support.'],
            'about' => ['About us', "Northwind started in 2012 with a simple idea: small businesses deserve the same quality of advice as large ones.\n\nToday our team of specialists supports more than 300 clients across retail, hospitality and professional services."],
            'services' => ['Our services', 'From strategy to day-to-day delivery, here is how we can help.'],
            'testimonials' => ['What clients say', 'Real feedback from the people we work with.'],
            'gallery' => ['Gallery', 'A look at our work and our workspace.'],
            'faq' => ['Frequently asked questions', 'Quick answers to the things we are asked most.'],
            'team' => ['Meet the team', 'The people behind your results.'],
            'contact' => ['Get in touch', 'Tell us about your project and we will reply within one working day.'],
        ];

        $order = 0;

        foreach ($copy as $key => [$title, $content]) {
            Section::query()->updateOrCreate(['key' => $key], [
                'title' => $title,
                'content' => $content,
                'sort_order' => $order += 10,
                'is_visible' => true,
            ]);
        }
    }
}
