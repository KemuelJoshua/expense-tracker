<?php

namespace Tests\Feature\Expenses;

use App\Models\Expenses;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_create_an_expense_with_pay_in(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('expenses.store'), [
                'name' => 'Office Internet',
                'total_amount' => 1250,
                'paid_amount' => 500,
                'type' => 'utilities',
                'category' => 'Connectivity',
                'reference_no' => 'INV-1001',
                'date_start' => '2026-03-01',
                'date_end' => '2026-03-31',
                'payment_due' => 15,
                'pay_in' => 'first',
                'payment_mode' => 'fixed_monthly',
                'is_recurring' => true,
                'recurring_cycle' => 'monthly',
                'description' => 'Monthly internet bill',
            ])
            ->assertRedirect(route('expenses.index'));

        $expense = Expenses::query()->first();

        $this->assertNotNull($expense);
        $this->assertSame('first', $expense->pay_in);
        $this->assertSame('fixed_monthly', $expense->payment_mode);
        $this->assertTrue((bool) $expense->is_recurring);
        $this->assertSame('monthly', $expense->recurring_cycle);
        $this->assertSame($user->id, $expense->created_by);
    }

    public function test_pay_in_is_required_when_creating_an_expense(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->from(route('expenses.index'))
            ->post(route('expenses.store'), [
                'name' => 'Office Internet',
                'total_amount' => 1250,
                'type' => 'utilities',
                'date_start' => '2026-03-01',
            ])
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHasErrors(['pay_in']);
    }

    public function test_installment_loan_requires_end_date_when_creating_an_expense(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->from(route('expenses.index'))
            ->post(route('expenses.store'), [
                'name' => 'Laptop Loan',
                'total_amount' => 24000,
                'type' => 'loan',
                'date_start' => '2026-03-01',
                'payment_due' => 15,
                'pay_in' => 'first',
                'payment_mode' => 'installment',
            ])
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHasErrors(['date_end']);
    }

    public function test_one_time_loan_clears_recurring_values_when_creating_an_expense(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('expenses.store'), [
                'name' => 'One-Time Loan Settlement',
                'total_amount' => 5000,
                'paid_amount' => 0,
                'type' => 'loan',
                'category' => 'Settlement',
                'reference_no' => 'LN-101',
                'date_start' => '2026-03-01',
                'date_end' => '2026-06-01',
                'payment_due' => 15,
                'pay_in' => 'second',
                'payment_mode' => 'one_time',
                'is_recurring' => true,
                'recurring_cycle' => 'yearly',
            ])
            ->assertRedirect(route('expenses.index'));

        $expense = Expenses::query()->latest('id')->first();

        $this->assertNotNull($expense);
        $this->assertSame('one_time', $expense->payment_mode);
        $this->assertFalse((bool) $expense->is_recurring);
        $this->assertNull($expense->recurring_cycle);
        $this->assertNull($expense->date_end);
    }

    public function test_utilities_require_a_valid_payment_mode_when_creating_an_expense(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->from(route('expenses.index'))
            ->post(route('expenses.store'), [
                'name' => 'Water Bill',
                'total_amount' => 950,
                'type' => 'utilities',
                'date_start' => '2026-03-01',
                'payment_due' => 12,
                'pay_in' => 'first',
            ])
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHasErrors(['payment_mode']);
    }
}
