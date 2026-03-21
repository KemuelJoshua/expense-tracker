<?php

namespace Database\Factories;

use App\Models\Expenses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expenses>
 */
class ExpensesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-3 months', '+1 month');
        $type = fake()->randomElement(['loan', 'subscription', 'utilities', 'others']);
        $paymentMode = match ($type) {
            'loan' => fake()->randomElement(['one_time', 'installment']),
            'utilities' => fake()->randomElement(['fixed_monthly', 'variable_amount']),
            default => null,
        };
        $isRecurring = match ($type) {
            'loan' => $paymentMode === 'installment',
            'utilities' => true,
            default => fake()->boolean(),
        };

        return [
            'name' => fake()->sentence(3),
            'total_amount' => fake()->randomFloat(4, 500, 75000),
            'paid_amount' => fake()->randomFloat(4, 0, 25000),
            'type' => $type,
            'category' => fake()->optional()->randomElement(['Internet', 'Rent', 'Software', 'Operations']),
            'reference_no' => fake()->optional()->bothify('REF-####'),
            'date_start' => $startDate,
            'date_end' => $type === 'loan' && $paymentMode === 'installment'
                ? fake()->dateTimeBetween($startDate, '+6 months')
                : fake()->optional()->dateTimeBetween($startDate, '+6 months'),
            'payment_due' => fake()->optional()->numberBetween(1, 31),
            'payment_mode' => $paymentMode,
            'is_recurring' => $isRecurring,
            'recurring_cycle' => $isRecurring
                ? ($type === 'utilities' || ($type === 'loan' && $paymentMode === 'installment')
                    ? 'monthly'
                    : fake()->randomElement(['monthly', 'yearly']))
                : null,
            'description' => fake()->optional()->sentence(),
            'attachment' => null,
            'created_by' => User::factory(),
        ];
    }
}
