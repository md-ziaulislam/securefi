<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@securofi.tech'],
            [
                'name' => 'Ziaul Islam',
                'password' => Hash::make('Password@123'),
                'bio' => 'Founder & Lead Developer of SecuroFi.Tech. Passionate about AI, web security, and fintech architecture.',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $superAdmin->assignRole('Super Admin');
    }
}
