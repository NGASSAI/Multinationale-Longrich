<?php


namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['client', 'admin', 'super_admin'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@longrich.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('changeme123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');
    }
}
