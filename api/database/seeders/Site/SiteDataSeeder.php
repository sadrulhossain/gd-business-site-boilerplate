<?php

namespace Database\Seeders\Site;

use Illuminate\Database\Seeder;

/**
 * Runs every sample-data seeder in this folder. Deliberately NOT in DatabaseSeeder: run it, or any one of them, by hand.
 *
 *   php artisan db:seed --class="Database\Seeders\Site\SiteDataSeeder"
 */
class SiteDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SiteSettingsSeeder::class,
            SiteSectionsSeeder::class,
            SiteServicesSeeder::class,
            SiteTestimonialsSeeder::class,
            SiteTeamSeeder::class,
            SiteFaqsSeeder::class,
            SiteBlogSeeder::class,
        ]);
    }
}
