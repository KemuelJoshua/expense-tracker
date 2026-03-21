<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'is_approved' => true,
        ]);

        $superAdmin->assignRole('SuperAdmin');

        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'is_approved' => true,
        ]);

        $user->assignRole('Admin');

        $this->call([
            ExpenseAndSavingsSeeder::class,
        ]);
    }
}
