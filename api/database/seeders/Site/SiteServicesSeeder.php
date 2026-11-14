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
            ['Business strategy', 'ব্যবসায়িক কৌশল', 'A clear plan for the next one to three years: goals, priorities and the steps to reach them.', 'আগামী এক থেকে তিন বছরের জন্য একটি স্পষ্ট পরিকল্পনা: লক্ষ্য, অগ্রাধিকার এবং সেখানে পৌঁছানোর ধাপসমূহ।'],
            ['Marketing and branding', 'মার্কেটিং ও ব্র্যান্ডিং', 'Positioning, website and campaigns that bring in the right customers.', 'সঠিক গ্রাহক আনার মতো পজিশনিং, ওয়েবসাইট ও ক্যাম্পেইন।'],
            ['Financial planning', 'আর্থিক পরিকল্পনা', 'Budgets, forecasts and cash-flow reviews so you always know where you stand.', 'বাজেট, পূর্বাভাস ও নগদ প্রবাহের পর্যালোচনা, যাতে আপনি সবসময় নিজের অবস্থান জানতে পারেন।'],
            ['Operations review', 'কার্যক্রম পর্যালোচনা', 'We find the bottlenecks in your processes and fix them with simple, lasting changes.', 'আমরা আপনার প্রক্রিয়ার বাধাগুলো খুঁজে বের করি এবং সহজ, স্থায়ী পরিবর্তনের মাধ্যমে সমাধান করি।'],
            ['Team coaching', 'টিম কোচিং', 'Hiring, onboarding and leadership support for growing teams.', 'বেড়ে ওঠা দলের জন্য নিয়োগ, অনবোর্ডিং ও নেতৃত্ব সহায়তা।'],
            ['Digital transformation', 'ডিজিটাল রূপান্তর', 'Pick and roll out the tools that save time instead of adding work.', 'কাজ বাড়ানোর বদলে সময় বাঁচায় এমন টুল বেছে নিন এবং চালু করুন।'],
        ];

        foreach ($rows as $i => [$title, $title2, $description, $description2]) {
            Service::query()->updateOrCreate(['title' => $title], [
                'title_2nd' => $title2,
                'description' => $description,
                'description_2nd' => $description2,
                'sort_order' => ($i + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
