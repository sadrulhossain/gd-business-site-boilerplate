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
            ['How does a first engagement work?', 'প্রথম কাজ কীভাবে শুরু হয়?', 'We start with a free 30-minute call, then send a short proposal with scope, timeline and price. Nothing starts until you agree.', 'আমরা ৩০ মিনিটের বিনামূল্যে কল দিয়ে শুরু করি, তারপর পরিধি, সময়সূচি ও মূল্যসহ একটি সংক্ষিপ্ত প্রস্তাব পাঠাই। আপনি সম্মত না হওয়া পর্যন্ত কিছুই শুরু হয় না।'],
            ['Do you work with very small businesses?', 'আপনারা কি খুব ছোট ব্যবসার সাথে কাজ করেন?', 'Yes. Most of our clients have fewer than 20 people, and our packages are sized accordingly.', 'হ্যাঁ। আমাদের বেশিরভাগ ক্লায়েন্টের কর্মী সংখ্যা ২০ জনের কম, এবং আমাদের প্যাকেজও সেভাবেই সাজানো।'],
            ['How much does it cost?', 'এর খরচ কত?', 'Projects start from a fixed fee; ongoing support is billed monthly. You always see the price before we begin.', 'প্রকল্প শুরু হয় নির্দিষ্ট ফি থেকে; চলমান সহায়তার বিল মাসিক। কাজ শুরুর আগেই আপনি মূল্য দেখতে পাবেন।'],
            ['Can you work remotely?', 'আপনারা কি দূর থেকে কাজ করতে পারেন?', 'Yes. We work with clients online and visit on site when it helps.', 'হ্যাঁ। আমরা অনলাইনে ক্লায়েন্টদের সাথে কাজ করি এবং প্রয়োজন হলে সরাসরি গিয়ে দেখা করি।'],
            ['How quickly can we start?', 'কত দ্রুত আমরা শুরু করতে পারি?', 'Usually within two weeks of agreeing the proposal.', 'সাধারণত প্রস্তাবে সম্মত হওয়ার দুই সপ্তাহের মধ্যে।'],
        ];

        foreach ($rows as $i => [$question, $question2, $answer, $answer2]) {
            Faq::query()->updateOrCreate(['question' => $question], [
                'question_2nd' => $question2,
                'answer' => $answer,
                'answer_2nd' => $answer2,
                'sort_order' => ($i + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
