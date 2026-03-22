<?php

namespace Tests\Feature\SpendIncome;

use App\Models\Accounts;
use App\Models\Expenses;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpendIncomeStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_spend_entry_linked_to_an_expense(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Loan Payment',
            'paid_amount' => 500,
        ]);

        $this->actingAs($user)
            ->post(route('spend-income.store'), [
                'entry_type' => 'spend',
                'transaction_date' => '2026-03-21',
                'amount' => 1500,
                'description' => 'Loan deduction',
                'expense_reference' => (string) $expense->id,
                'is_payroll' => 0,
            ])
            ->assertRedirect(route('spend-income.index'));

        $this->assertDatabaseHas('spend_incomes', [
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_payroll' => false,
        ]);

        $this->assertSame('2000.0000', $expense->fresh()->paid_amount);
    }

    public function test_user_can_create_an_income_entry_with_other_account_and_payroll_month_year(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        Accounts::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('spend-income.store'), [
                'entry_type' => 'income',
                'transaction_date' => '2026-03-25',
                'amount' => 25000,
                'description' => 'Payroll received',
                'account_reference' => 'others',
                'is_payroll' => 1,
                'payroll_month' => 3,
                'payroll_year' => 2026,
            ])
            ->assertRedirect(route('spend-income.index'));

        $this->assertDatabaseHas('spend_incomes', [
            'user_id' => $user->id,
            'entry_type' => 'income',
            'account_id' => null,
            'is_payroll' => true,
            'payroll_month' => 3,
            'payroll_year' => 2026,
        ]);
    }

    public function test_user_can_create_an_income_entry_linked_to_an_account_and_add_to_its_balance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $account = Accounts::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000,
        ]);

        $this->actingAs($user)
            ->post(route('spend-income.store'), [
                'entry_type' => 'income',
                'transaction_date' => '2026-03-25',
                'amount' => 2500,
                'description' => 'Cash received',
                'account_reference' => (string) $account->id,
                'is_payroll' => 0,
            ])
            ->assertRedirect(route('spend-income.index'));

        $this->assertSame('3500.0000', $account->fresh()->balance);
    }
}
