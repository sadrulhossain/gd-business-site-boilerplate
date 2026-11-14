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
            ['Amelia Carter', 'Owner, Carter Bakery', 'মালিক, কার্টার বেকারি', 'Northwind rebuilt our pricing and our margins improved within a quarter. Honest advice, no jargon.', 'নর্থউইন্ড আমাদের মূল্য নির্ধারণ নতুন করে সাজিয়েছে এবং এক প্রান্তিকের মধ্যেই আমাদের মুনাফা বেড়েছে। সৎ পরামর্শ, কোনো জটিল শব্দ নেই।'],
            ['Daniel Okafor', 'Director, Okafor Logistics', 'পরিচালক, ওকাফোর লজিস্টিকস', 'They found three process bottlenecks we had lived with for years. Deliveries are now a day faster.', 'তারা এমন তিনটি প্রক্রিয়াগত বাধা খুঁজে পেয়েছে যা নিয়ে আমরা বছরের পর বছর চলছিলাম। এখন ডেলিভারি একদিন দ্রুত হয়।'],
            ['Priya Nair', 'Founder, Bloom Studio', 'প্রতিষ্ঠাতা, ব্লুম স্টুডিও', 'From the first call I felt understood. The new brand and website doubled our enquiries.', 'প্রথম কল থেকেই মনে হয়েছে তারা আমাকে বুঝেছে। নতুন ব্র্যান্ড ও ওয়েবসাইট আমাদের জিজ্ঞাসা দ্বিগুণ করেছে।'],
        ];

        foreach ($rows as $i => [$name, $role, $role2, $quote, $quote2]) {
            Testimonial::query()->updateOrCreate(['author_name' => $name], [
                'author_role' => $role,
                'author_role_2nd' => $role2,
                'quote' => $quote,
                'quote_2nd' => $quote2,
                'sort_order' => ($i + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
