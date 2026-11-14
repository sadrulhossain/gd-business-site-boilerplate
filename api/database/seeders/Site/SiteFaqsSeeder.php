<?php

namespace Database\Seeders\Site;

use App\Models\Faq;
use Illuminate\Database\Seeder;

/** Sample FAQs. Run by hand (see SiteSettingsSeeder). */
class SiteFaqsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['How does a first engagement work?', 'We start with a free 30-minute call, then send a short proposal with scope, timeline and price. Nothing starts until you agree.'],
            ['Do you work with very small businesses?', 'Yes. Most of our clients have fewer than 20 people, and our packages are sized accordingly.'],
            ['How much does it cost?', 'Projects start from a fixed fee; ongoing support is billed monthly. You always see the price before we begin.'],
            ['Can you work remotely?', 'Yes. We work with clients online and visit on site when it helps.'],
            ['How quickly can we start?', 'Usually within two weeks of agreeing the proposal.'],
        ];

        foreach ($rows as $i => [$question, $answer]) {
            Faq::query()->updateOrCreate(['question' => $question], [
                'answer' => $answer,
                'sort_order' => ($i + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
