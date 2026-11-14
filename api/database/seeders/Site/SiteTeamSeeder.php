<?php

namespace Database\Seeders\Site;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

/** Sample team (no photos; add them in the admin). Run by hand (see SiteSettingsSeeder). */
class SiteTeamSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Helen Brooks', 'Managing Director', 'ব্যবস্থাপনা পরিচালক', 'Helen founded Northwind after fifteen years leading finance teams. She works with clients on strategy and funding.', 'পনেরো বছর অর্থ বিভাগের নেতৃত্ব দেওয়ার পর হেলেন নর্থউইন্ড প্রতিষ্ঠা করেন। তিনি ক্লায়েন্টদের সাথে কৌশল ও অর্থায়ন নিয়ে কাজ করেন।'],
            ['Marcus Lee', 'Head of Operations', 'অপারেশনস প্রধান', 'Marcus turns plans into routines that work every day, from small shops to multi-site teams.', 'মার্কাস পরিকল্পনাকে প্রতিদিনের কার্যকর রুটিনে রূপ দেন, ছোট দোকান থেকে বহু-শাখার দল পর্যন্ত।'],
            ['Sofia Alvarez', 'Marketing Lead', 'মার্কেটিং প্রধান', 'Sofia shapes brands and campaigns with a focus on measurable results.', 'সোফিয়া পরিমাপযোগ্য ফলাফলের দিকে লক্ষ্য রেখে ব্র্যান্ড ও ক্যাম্পেইন গড়ে তোলেন।'],
            ['Tom Whitfield', 'Senior Consultant', 'জ্যেষ্ঠ পরামর্শক', 'Tom coaches owners and managers through growth, hiring and change.', 'টম মালিক ও ম্যানেজারদের প্রবৃদ্ধি, নিয়োগ ও পরিবর্তনের পথে কোচিং দেন।'],
        ];

        foreach ($rows as $i => [$name, $role, $role2, $bio, $bio2]) {
            TeamMember::query()->updateOrCreate(['name' => $name], [
                'role' => $role,
                'role_2nd' => $role2,
                'bio' => $bio,
                'bio_2nd' => $bio2,
                'sort_order' => ($i + 1) * 10,
                'is_visible' => true,
            ]);
        }
    }
}
