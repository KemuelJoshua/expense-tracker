<?php

namespace Database\Seeders;

use App\Models\Accounts;
use App\Models\Expenses;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseAndSavingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::query()->where('email', 'admin@gmail.com')->first();

        if (! $adminUser instanceof User) {
            $adminUser = User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
            ]);

            $adminUser->assignRole('Admin');
        }

        Expenses::factory()
            ->count(10)
            ->sequence(
                ['pay_in' => 'first'],
                ['pay_in' => 'first'],
                ['pay_in' => 'first'],
                ['pay_in' => 'first'],
                ['pay_in' => 'first'],
                ['pay_in' => 'first'],
                ['pay_in' => 'second'],
                ['pay_in' => 'second'],
                ['pay_in' => 'second'],
                ['pay_in' => 'second'],
            )
            ->create([
                'created_by' => $adminUser->id,
            ]);

        Accounts::factory()
            ->count(2)
            ->sequence(
                [
                    'account_name' => 'Emergency Savings',
                    'account_type' => 'bank',
                    'bank_name' => 'BPI',
                    'is_default' => true,
                ],
                [
                    'account_name' => 'Travel Savings',
                    'account_type' => 'bank',
                    'bank_name' => 'BDO',
                    'is_default' => false,
                ],
            )
            ->create([
                'user_id' => $adminUser->id,
                'currency' => 'PHP',
                'is_active' => true,
            ]);
    }
}
