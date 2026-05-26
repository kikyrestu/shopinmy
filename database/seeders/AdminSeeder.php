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
        // Pastikan role 'super_admin' ada di database
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

        // Buat atau Update akun Super Admin untuk Filament Access
        $admin = User::updateOrCreate(
            ['email' => config('app.admin_email', 'admin@shopinmy.com')],
            [
                'name' => 'Super Admin',
                'password' => config('app.admin_password', 'password123'), // TANPA bcrypt! Laravel Auto-Cast handles hashing.
            ]
        );

        // Beri role super_admin jika belum punya
        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }
    }
}
