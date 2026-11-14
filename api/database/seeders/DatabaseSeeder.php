<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            // Each module seeds its own permissions + menu rows. Add new module seeders here.
            DashboardModuleSeeder::class,
            SectionsModuleSeeder::class,
            HeroModuleSeeder::class,
            AboutModuleSeeder::class,
            ServicesModuleSeeder::class,
            PricingModuleSeeder::class,
            TestimonialsModuleSeeder::class,
            TeamModuleSeeder::class,
            GalleryModuleSeeder::class,
            FaqsModuleSeeder::class,
            BlogModuleSeeder::class,
            ContactModuleSeeder::class,
            SettingsModuleSeeder::class,
            AccessModuleSeeder::class,
            SuperadminSeeder::class,
            OauthClientSeeder::class,
        ]);
    }
}
