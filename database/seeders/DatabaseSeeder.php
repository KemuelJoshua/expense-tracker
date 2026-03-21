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
        ]);

        $superAdmin->assignRole('SuperAdmin');

        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ]);

        $user->assignRole('Admin');

        $this->call([
            ExpenseAndSavingsSeeder::class,
        ]);
    }
}
