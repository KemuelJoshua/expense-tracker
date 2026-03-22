<?php

namespace Tests\Feature\SpendIncome;

use App\Models\Accounts;
use App\Models\Expenses;
use App\Models\SpendIncome;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpendIncomeUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_an_income_entry_and_link_it_to_an_account(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $account = Accounts::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'Payroll Account',
            'balance' => 5000,
        ]);
        $otherAccount = Accounts::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'Savings Account',
            'balance' => 2000,
        ]);
        $entry = SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'income',
            'account_id' => $otherAccount->id,
            'amount' => 1000,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        $otherAccount->update([
            'balance' => 3000,
        ]);

        $this->actingAs($user)
            ->put(route('spend-income.update', $entry), [
                'entry_type' => 'income',
                'transaction_date' => '2026-04-01',
                'amount' => 32000,
                'description' => 'Updated payroll income',
                'account_reference' => (string) $account->id,
                'is_payroll' => 1,
                'payroll_month' => 4,
                'payroll_year' => 2026,
            ])
            ->assertRedirect(route('spend-income.index'));

        $this->assertDatabaseHas('spend_incomes', [
            'id' => $entry->id,
            'account_id' => $account->id,
            'is_payroll' => true,
            'payroll_month' => 4,
            'payroll_year' => 2026,
        ]);

        $this->assertSame('37000.0000', $account->fresh()->balance);
        $this->assertSame('2000.0000', $otherAccount->fresh()->balance);
    }

    public function test_deleting_a_linked_spend_entry_reverts_the_expense_paid_amount(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'paid_amount' => 2500,
        ]);
        $entry = SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'expense_id' => $expense->id,
            'amount' => 500,
        ]);

        $this->actingAs($user)
            ->delete(route('spend-income.destroy', $entry))
            ->assertRedirect(route('spend-income.index'));

        $this->assertSame('2000.0000', $expense->fresh()->paid_amount);
    }
}
