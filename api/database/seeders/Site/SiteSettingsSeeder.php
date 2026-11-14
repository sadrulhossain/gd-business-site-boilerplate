<?php

namespace Database\Seeders\Site;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/** Sample business details. Run by hand: php artisan db:seed --class="Database\Seeders\Site\SiteSettingsSeeder" */
class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $day = ['open' => '09:00', 'close' => '18:00', 'closed' => false];
        $half = ['open' => '10:00', 'close' => '14:00', 'closed' => false];
        $off = ['open' => null, 'close' => null, 'closed' => true];

        Setting::current()->update([
            'site_name' => 'Northwind Consulting',
            'tagline' => 'Practical advice that helps small businesses grow',
            'address' => "12 Market Street\nSpringfield, 12345",
            'phone' => '+1 555 010 2030',
            'email' => 'hello@northwind.example',
            'hours' => ['mon' => $day, 'tue' => $day, 'wed' => $day, 'thu' => $day, 'fri' => $day, 'sat' => $half, 'sun' => $off],
            'social_links' => [
                'facebook' => 'https://facebook.com/northwind.example',
                'instagram' => 'https://instagram.com/northwind.example',
                'linkedin' => 'https://linkedin.com/company/northwind-example',
            ],
        ]);
    }
}
