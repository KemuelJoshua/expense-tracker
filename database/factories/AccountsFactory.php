<?php

namespace Database\Factories;

use App\Models\Accounts;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Accounts>
 */
class AccountsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_name' => fake()->randomElement([
                'BDO Payroll',
                'GCash Wallet',
                'Petty Cash',
                'BPI Savings',
            ]),
            'account_type' => fake()->randomElement([
                'bank',
                'e-wallet',
                'cash',
                'credit',
            ]),
            'balance' => fake()->randomFloat(4, 0, 250000),
            'initial_balance' => fake()->randomFloat(4, 0, 150000),
            'account_number' => fake()->optional()->numerify('####-####-####'),
            'bank_name' => fake()->optional()->randomElement([
                'BDO',
                'BPI',
                'Metrobank',
                'GCash',
            ]),
            'currency' => 'PHP',
            'is_active' => true,
            'is_default' => false,
            'user_id' => User::factory(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
