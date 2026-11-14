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
            'hero' => ['Grow with confidence', 'আত্মবিশ্বাসের সাথে এগিয়ে যান', 'We help local businesses plan, launch and scale with clear, honest guidance and hands-on support.', 'স্থানীয় ব্যবসাকে পরিকল্পনা, শুরু এবং প্রসারে আমরা সহায়তা করি স্পষ্ট ও সৎ দিকনির্দেশনা এবং সরাসরি সহযোগিতার মাধ্যমে।'],
            'about' => ['About us', 'আমাদের সম্পর্কে', "Northwind started in 2012 with a simple idea: small businesses deserve the same quality of advice as large ones.\n\nToday our team of specialists supports more than 300 clients across retail, hospitality and professional services.", "নর্থউইন্ড ২০১২ সালে একটি সহজ ধারণা নিয়ে শুরু হয়েছিল: বড় প্রতিষ্ঠানের মতো ছোট ব্যবসাও সমান মানের পরামর্শ পাওয়ার যোগ্য।\n\nআজ আমাদের বিশেষজ্ঞ দল খুচরা, আতিথেয়তা ও পেশাদার সেবা খাতের ৩০০-র বেশি ক্লায়েন্টকে সহায়তা করছে।"],
            'services' => ['Our services', 'আমাদের সেবা', 'From strategy to day-to-day delivery, here is how we can help.', 'কৌশল থেকে দৈনন্দিন বাস্তবায়ন পর্যন্ত, আমরা যেভাবে সহায়তা করতে পারি।'],
            'pricing' => ['Our pricing', 'আমাদের মূল্য তালিকা', 'Clear, upfront prices for every service.', 'প্রতিটি সেবার জন্য স্পষ্ট ও আগেভাগে জানা মূল্য।'],
            'testimonials' => ['What clients say', 'ক্লায়েন্টরা যা বলেন', 'Real feedback from the people we work with.', 'আমাদের সাথে কাজ করা মানুষদের প্রকৃত মতামত।'],
            'gallery' => ['Gallery', 'গ্যালারি', 'A look at our work and our workspace.', 'আমাদের কাজ এবং কর্মস্থলের একটি ঝলক।'],
            'faq' => ['Frequently asked questions', 'সচরাচর জিজ্ঞাসা', 'Quick answers to the things we are asked most.', 'আমাদের সবচেয়ে বেশি করা প্রশ্নগুলোর দ্রুত উত্তর।'],
            'team' => ['Meet the team', 'আমাদের দলের সাথে পরিচিত হন', 'The people behind your results.', 'আপনার সাফল্যের পেছনের মানুষগুলো।'],
            'contact' => ['Get in touch', 'যোগাযোগ করুন', 'Tell us about your project and we will reply within one working day.', 'আপনার প্রকল্প সম্পর্কে আমাদের জানান, আমরা এক কার্যদিবসের মধ্যে উত্তর দেব।'],
        ];

        $order = 0;

        foreach ($copy as $key => [$title, $title2, $content, $content2]) {
            Section::query()->updateOrCreate(['key' => $key], [
                'title' => $title,
                'title_2nd' => $title2,
                'content' => $content,
                'content_2nd' => $content2,
                'sort_order' => $order += 10,
                // Pricing stays off until an admin turns it on.
                'is_visible' => $key !== 'pricing',
            ]);
        }
    }
}
