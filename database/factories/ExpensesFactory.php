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
        $isRecurring = fake()->boolean();

        return [
            'name' => fake()->sentence(3),
            'total_amount' => fake()->randomFloat(4, 500, 75000),
            'paid_amount' => fake()->randomFloat(4, 0, 25000),
            'type' => fake()->randomElement(['loan', 'subscription', 'utilities', 'others']),
            'category' => fake()->optional()->randomElement(['Internet', 'Rent', 'Software', 'Operations']),
            'reference_no' => fake()->optional()->bothify('REF-####'),
            'date_start' => $startDate,
            'date_end' => fake()->optional()->dateTimeBetween($startDate, '+6 months'),
            'payment_due' => fake()->optional()->dateTimeBetween($startDate, '+3 months'),
            'is_recurring' => $isRecurring,
            'recurring_cycle' => $isRecurring ? fake()->randomElement(['monthly', 'yearly']) : null,
            'description' => fake()->optional()->sentence(),
            'attachment' => null,
            'created_by' => User::factory(),
        ];
    }
}
