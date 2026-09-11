<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            AdminUserSeeder::class,
            CategorySeeder::class,
            ThemeSettingSeeder::class,
            GeneralSettingSeeder::class,
            DefaultPageSeeder::class,
            DemoContentSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
