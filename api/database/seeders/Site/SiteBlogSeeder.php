<?php

namespace Database\Seeders\Site;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/** Sample categories and published posts, in both languages (no cover images). Run by hand (see SiteSettingsSeeder). */
class SiteBlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];

        foreach ([['Strategy', 'কৌশল', 'strategy'], ['Marketing', 'মার্কেটিং', 'marketing'], ['Finance', 'অর্থ', 'finance']] as $i => [$name, $name2, $slug]) {
            $categories[$slug] = Category::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'name_2nd' => $name2, 'sort_order' => ($i + 1) * 10]);
        }

        $posts = [
            [
                'strategy', 'five-questions-before-you-grow',
                'Five questions to ask before you grow', 'বড় হওয়ার আগে পাঁচটি প্রশ্ন',
                'Growth is exciting, but only if the basics are ready. Start with these five questions.', 'প্রবৃদ্ধি উত্তেজনাপূর্ণ, তবে মূল ভিত্তি প্রস্তুত থাকলেই। এই পাঁচটি প্রশ্ন দিয়ে শুরু করুন।',
                '<p>Growth is exciting, but it multiplies whatever you already have, including the problems.</p><h2>1. Do you know your margins?</h2><p>Revenue is vanity; margin per product or service tells you where to push.</p><h2>2. Can your team handle twice the work?</h2><p>If the answer relies on heroics, fix the process first.</p><h2>3. Who is your best customer?</h2><p>Find more of them rather than more customers in general.</p>',
                '<p>প্রবৃদ্ধি উত্তেজনাপূর্ণ, কিন্তু এটি আপনার যা আছে তাকেই বহুগুণ করে, সমস্যাগুলোসহ।</p><h2>১. আপনি কি আপনার মুনাফার হার জানেন?</h2><p>আয় শুধু বাহ্যিক সাফল্য; প্রতিটি পণ্য বা সেবার মুনাফার হার বলে দেয় কোথায় জোর দিতে হবে।</p><h2>২. আপনার দল কি দ্বিগুণ কাজ সামলাতে পারবে?</h2><p>উত্তর যদি অতিমানবীয় চেষ্টার ওপর নির্ভর করে, আগে প্রক্রিয়া ঠিক করুন।</p><h2>৩. আপনার সেরা গ্রাহক কে?</h2><p>সাধারণভাবে বেশি গ্রাহকের বদলে তাঁদের মতো আরও গ্রাহক খুঁজুন।</p>',
            ],
            [
                'marketing', 'a-simple-website-checklist',
                'A simple website checklist for local businesses', 'স্থানীয় ব্যবসার জন্য একটি সহজ ওয়েবসাইট চেকলিস্ট',
                'Your website only needs to do a few things well. Here is the checklist we use.', 'আপনার ওয়েবসাইটকে মাত্র কয়েকটি কাজ ভালোভাবে করতে হবে। এখানে আমাদের ব্যবহৃত চেকলিস্ট।',
                '<p>A good small-business website is clear, fast and easy to act on.</p><ul><li>Say what you do in the first sentence.</li><li>Show your phone number and opening hours.</li><li>Make the contact form short.</li><li>Check it on a phone.</li></ul>',
                '<p>একটি ভালো ছোট ব্যবসার ওয়েবসাইট হয় স্পষ্ট, দ্রুত এবং ব্যবহারে সহজ।</p><ul><li>প্রথম বাক্যেই বলুন আপনি কী করেন।</li><li>ফোন নম্বর ও খোলা থাকার সময় দেখান।</li><li>যোগাযোগের ফর্ম ছোট রাখুন।</li><li>ফোনে এটি দেখে নিন।</li></ul>',
            ],
            [
                'finance', 'cash-flow-in-plain-english',
                'Cash flow in plain English', 'সহজ ভাষায় নগদ প্রবাহ',
                'Profit is an opinion, cash is a fact. How to keep an eye on the second one.', 'মুনাফা একটি মত, নগদ একটি বাস্তবতা। দ্বিতীয়টির ওপর কীভাবে নজর রাখবেন।',
                '<p>Many healthy businesses fail because they run out of cash, not because they lose money.</p><h2>Build a 13-week forecast</h2><p>List expected money in and out for the next 13 weeks and update it every Monday.</p><h2>Invoice early</h2><p>Every day between finishing work and sending the invoice is a day you finance your customer.</p>',
                '<p>অনেক সুস্থ ব্যবসা ব্যর্থ হয় নগদ শেষ হয়ে যাওয়ার কারণে, লোকসানের কারণে নয়।</p><h2>১৩ সপ্তাহের পূর্বাভাস তৈরি করুন</h2><p>আগামী ১৩ সপ্তাহে প্রত্যাশিত আয় ও ব্যয়ের তালিকা করুন এবং প্রতি সোমবার হালনাগাদ করুন।</p><h2>তাড়াতাড়ি ইনভয়েস দিন</h2><p>কাজ শেষ করা ও ইনভয়েস পাঠানোর মাঝের প্রতিটি দিন আপনি আপনার গ্রাহকের অর্থায়ন করছেন।</p>',
            ],
        ];

        foreach ($posts as $i => [$cat, $slug, $title, $title2, $excerpt, $excerpt2, $body, $body2]) {
            Post::query()->updateOrCreate(['slug' => $slug], [
                'category_id' => $categories[$cat]->id,
                'title' => $title,
                'title_2nd' => $title2,
                'excerpt' => $excerpt,
                'excerpt_2nd' => $excerpt2,
                'body' => $body,
                'body_2nd' => $body2,
                'is_published' => true,
                'published_at' => now()->subDays(($i + 1) * 7),
            ]);
        }
    }
}
