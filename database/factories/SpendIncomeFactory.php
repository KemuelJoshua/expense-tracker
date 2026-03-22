<?php

namespace Database\Factories;

use App\Models\SpendIncome;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpendIncome>
 */
class SpendIncomeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entryType = fake()->randomElement(['spend', 'income']);
        $isPayroll = $entryType === 'income' ? fake()->boolean() : false;

        return [
            'user_id' => User::factory(),
            'entry_type' => $entryType,
            'transaction_date' => fake()->date(),
            'amount' => fake()->randomFloat(4, 100, 50000),
            'description' => fake()->optional()->sentence(),
            'expense_id' => null,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => $isPayroll,
            'payroll_month' => $isPayroll ? fake()->numberBetween(1, 12) : null,
            'payroll_year' => $isPayroll ? (int) fake()->year() : null,
        ];
    }
}
