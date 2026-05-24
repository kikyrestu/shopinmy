<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin User for Filament Access
        User::firstOrCreate(
            ['email' => 'admin@shopinmy.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password123'),
                // If you use Spatie Permission, you can assign role here:
                // ->assignRole('super_admin')
            ]
        );
    }
}
