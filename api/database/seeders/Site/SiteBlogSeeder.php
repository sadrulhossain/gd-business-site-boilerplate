<?php

namespace Database\Seeders\Site;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/** Sample categories and published posts (no cover images). Run by hand (see SiteSettingsSeeder). */
class SiteBlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];

        foreach ([['Strategy', 'strategy'], ['Marketing', 'marketing'], ['Finance', 'finance']] as $i => [$name, $slug]) {
            $categories[$slug] = Category::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => ($i + 1) * 10]);
        }

        $posts = [
            ['strategy', 'five-questions-before-you-grow', 'Five questions to ask before you grow', 'Growth is exciting, but only if the basics are ready. Start with these five questions.',
                '<p>Growth is exciting, but it multiplies whatever you already have, including the problems.</p><h2>1. Do you know your margins?</h2><p>Revenue is vanity; margin per product or service tells you where to push.</p><h2>2. Can your team handle twice the work?</h2><p>If the answer relies on heroics, fix the process first.</p><h2>3. Who is your best customer?</h2><p>Find more of them rather than more customers in general.</p>'],
            ['marketing', 'a-simple-website-checklist', 'A simple website checklist for local businesses', 'Your website only needs to do a few things well. Here is the checklist we use.',
                '<p>A good small-business website is clear, fast and easy to act on.</p><ul><li>Say what you do in the first sentence.</li><li>Show your phone number and opening hours.</li><li>Make the contact form short.</li><li>Check it on a phone.</li></ul>'],
            ['finance', 'cash-flow-in-plain-english', 'Cash flow in plain English', 'Profit is an opinion, cash is a fact. How to keep an eye on the second one.',
                '<p>Many healthy businesses fail because they run out of cash, not because they lose money.</p><h2>Build a 13-week forecast</h2><p>List expected money in and out for the next 13 weeks and update it every Monday.</p><h2>Invoice early</h2><p>Every day between finishing work and sending the invoice is a day you finance your customer.</p>'],
        ];

        foreach ($posts as $i => [$cat, $slug, $title, $excerpt, $body]) {
            Post::query()->updateOrCreate(['slug' => $slug], [
                'category_id' => $categories[$cat]->id,
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $body,
                'is_published' => true,
                'published_at' => now()->subDays(($i + 1) * 7),
            ]);
        }
    }
}
