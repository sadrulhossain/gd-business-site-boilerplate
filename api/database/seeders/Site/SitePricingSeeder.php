<?php

namespace Database\Seeders\Site;

use App\Models\Pricing;
use App\Models\Service;
use Illuminate\Database\Seeder;

/** Three sample price options for every existing service, in both languages. Run SiteServicesSeeder first. Run by hand (see SiteSettingsSeeder). */
class SitePricingSeeder extends Seeder
{
    public function run(): void
    {
        // [name, name_2nd, price multiplier, description, description_2nd, features, features_2nd, featured]
        $plans = [
            ['Starter', 'স্টার্টার', 1, 'A focused first step.', 'একটি মনোযোগী প্রথম ধাপ।',
                "Initial consultation\nOne written report\nEmail support",
                "প্রাথমিক পরামর্শ\nএকটি লিখিত প্রতিবেদন\nইমেইল সহায়তা", false],
            ['Standard', 'স্ট্যান্ডার্ড', 2, 'Our most popular option.', 'আমাদের সবচেয়ে জনপ্রিয় বিকল্প।',
                "Everything in Starter\nMonthly review call\nPriority email support",
                "স্টার্টারের সবকিছু\nমাসিক পর্যালোচনা কল\nঅগ্রাধিকার ইমেইল সহায়তা", true],
            ['Premium', 'প্রিমিয়াম', 4, 'Full hands-on support.', 'সম্পূর্ণ সরাসরি সহায়তা।',
                "Everything in Standard\nDedicated consultant\nPhone and on-site support",
                "স্ট্যান্ডার্ডের সবকিছু\nনির্ধারিত পরামর্শক\nফোন ও সরাসরি উপস্থিত সহায়তা", false],
        ];

        foreach (Service::query()->orderBy('sort_order')->orderBy('id')->get() as $i => $service) {
            $base = 250 + ($i % 3) * 100;

            foreach ($plans as $j => [$name, $name2, $multiplier, $description, $description2, $features, $features2, $featured]) {
                Pricing::query()->updateOrCreate(['service_id' => $service->id, 'name' => $name], [
                    'name_2nd' => $name2,
                    'price' => $base * $multiplier,
                    'unit' => 'per project',
                    'unit_2nd' => 'প্রতি প্রকল্প',
                    'description' => $description,
                    'description_2nd' => $description2,
                    'features' => $features,
                    'features_2nd' => $features2,
                    'is_featured' => $featured,
                    'sort_order' => ($j + 1) * 10,
                    'is_visible' => true,
                ]);
            }
        }
    }
}
