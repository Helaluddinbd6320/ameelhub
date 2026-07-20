<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 AmeelHub Database Seeding শুরু হচ্ছে...');
        $this->command->newLine();

        $this->call([
            RolesAndPermissionsSeeder::class,  // 1. আগে Roles
            SkillCategoriesSeeder::class,       // 2. Skill Categories
            SettingsSeeder::class,              // 3. Settings
            AdminUserSeeder::class,             // 4. সবশেষে Users (role depends on Spatie)
        ]);

        $this->command->newLine();
        $this->command->info('🎉 সব Seeding সম্পন্ন হয়েছে!');
    }
}