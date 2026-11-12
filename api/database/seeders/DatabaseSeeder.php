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
            ServicesModuleSeeder::class,
            TestimonialsModuleSeeder::class,
            TeamModuleSeeder::class,
            GalleryModuleSeeder::class,
            FaqsModuleSeeder::class,
            BlogModuleSeeder::class,
            ContactModuleSeeder::class,
            SettingsModuleSeeder::class,
            SuperadminSeeder::class,
            OauthClientSeeder::class,
        ]);
    }
}
