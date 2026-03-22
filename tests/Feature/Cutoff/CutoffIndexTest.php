<?php

namespace Tests\Feature\Cutoff;

use App\Models\Expenses;
use App\Models\SpendIncome;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CutoffIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_cutoff_index_returns_grouped_first_and_second_cutoff_data_for_the_selected_month(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Internet Bill',
            'date_start' => '2026-03-05',
            'date_end' => null,
            'pay_in' => 'first',
            'paid_amount' => 400,
            'total_amount' => 1200,
        ]);

        Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Office Rent',
            'date_start' => '2026-03-18',
            'date_end' => null,
            'pay_in' => 'second',
            'paid_amount' => 1000,
            'total_amount' => 5000,
        ]);

        Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Out of Range Expense',
            'date_start' => '2026-04-01',
            'pay_in' => 'first',
        ]);

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-03']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('cutoff/Index')
                ->where('filters.month', '2026-03')
                ->where('summary.month', 'March 2026')
                ->where('summary.count', 2)
                ->where('summary.total_paid', 1400)
                ->where('cutoffs.first.count', 1)
                ->where('cutoffs.second.count', 1)
                ->where('cutoffs.first.items.0.name', 'Internet Bill')
                ->where('cutoffs.first.items.0.paid_this_month', 400)
                ->where('cutoffs.first.items.0.remaining_amount', 800)
                ->where('cutoffs.second.items.0.name', 'Office Rent')
                ->where('cutoffs.second.items.0.paid_this_month', 1000),
            );
    }

    public function test_cutoff_index_only_includes_the_authenticated_users_expenses(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $otherUser = User::factory()->admin()->create();

        Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'My Cutoff Expense',
            'date_start' => '2026-03-05',
            'date_end' => null,
            'pay_in' => 'first',
        ]);

        Expenses::factory()->create([
            'created_by' => $otherUser->id,
            'name' => 'Other User Expense',
            'date_start' => '2026-03-06',
            'pay_in' => 'first',
        ]);

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-03']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.count', 1)
                ->where('cutoffs.first.count', 1)
                ->where('cutoffs.first.items.0.name', 'My Cutoff Expense'),
            );
    }

    public function test_cutoff_index_applies_previous_month_excess_to_the_selected_month_paid_total(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Equipment Loan',
            'date_start' => '2026-02-01',
            'date_end' => null,
            'pay_in' => 'first',
            'total_amount' => 1000,
            'paid_amount' => 1500,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-02-05',
            'amount' => 1200,
            'description' => 'Advance payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-03-08',
            'amount' => 300,
            'description' => 'March payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-03']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.total_paid', 500)
                ->where('cutoffs.first.items.0.current_month_paid', 300)
                ->where('cutoffs.first.items.0.carryover_amount', 200)
                ->where('cutoffs.first.items.0.paid_this_month', 500)
                ->where('cutoffs.first.items.0.remaining_amount', 500),
            );
    }

    public function test_cutoff_index_carries_previous_unpaid_balance_into_the_selected_month_due(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Equipment Lease',
            'date_start' => '2026-02-01',
            'date_end' => null,
            'pay_in' => 'first',
            'total_amount' => 1000,
            'paid_amount' => 700,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-02-05',
            'amount' => 700,
            'description' => 'Partial February payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-03-08',
            'amount' => 200,
            'description' => 'March payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-03']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.total_amount', 1300)
                ->where('summary.total_paid', 200)
                ->where('cutoffs.first.total_amount', 1300)
                ->where('cutoffs.first.items.0.previous_balance_amount', 300)
                ->where('cutoffs.first.items.0.effective_due_amount', 1300)
                ->where('cutoffs.first.items.0.current_month_paid', 200)
                ->where('cutoffs.first.items.0.carryover_amount', 0)
                ->where('cutoffs.first.items.0.remaining_amount', 1100),
            );
    }

    public function test_cutoff_penalty_affects_only_the_selected_month_and_does_not_become_future_carryover(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Water Utility',
            'date_start' => '2026-02-01',
            'date_end' => null,
            'pay_in' => 'first',
            'total_amount' => 1000,
            'paid_amount' => 1200,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-02-05',
            'amount' => 1200,
            'description' => 'February payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-03-04',
            'amount' => 100,
            'description' => 'March penalty',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => true,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-03']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('cutoffs.first.items.0.penalty_amount', 100)
                ->where('cutoffs.first.items.0.carryover_amount', 200)
                ->where('cutoffs.first.items.0.current_month_paid', 0)
                ->where('cutoffs.first.items.0.remaining_amount', 900),
            );

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-04']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('cutoffs.first.items.0.penalty_amount', 0)
                ->where('cutoffs.first.items.0.previous_balance_amount', 800)
                ->where('cutoffs.first.items.0.carryover_amount', 0)
                ->where('cutoffs.first.items.0.remaining_amount', 1800),
            );
    }

    public function test_cutoff_keeps_an_ended_loan_visible_when_it_has_unpaid_balance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Bike Loan',
            'type' => 'loan',
            'date_start' => '2026-01-01',
            'date_end' => '2026-03-31',
            'pay_in' => 'first',
            'total_amount' => 1000,
            'paid_amount' => 2200,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-01-05',
            'amount' => 1000,
            'description' => 'January payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-02-05',
            'amount' => 1000,
            'description' => 'February payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-03-05',
            'amount' => 200,
            'description' => 'March partial payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-04']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.count', 1)
                ->where('summary.total_amount', 800)
                ->where('cutoffs.first.items.0.name', 'Bike Loan')
                ->where('cutoffs.first.items.0.total_amount', 0)
                ->where('cutoffs.first.items.0.previous_balance_amount', 800)
                ->where('cutoffs.first.items.0.remaining_amount', 800),
            );
    }

    public function test_cutoff_hides_an_ended_loan_when_it_is_fully_paid(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Phone Loan',
            'type' => 'loan',
            'date_start' => '2026-01-01',
            'date_end' => '2026-03-31',
            'pay_in' => 'first',
            'total_amount' => 1000,
            'paid_amount' => 3000,
        ]);

        foreach (['2026-01-05', '2026-02-05', '2026-03-05'] as $date) {
            SpendIncome::factory()->create([
                'user_id' => $user->id,
                'entry_type' => 'spend',
                'transaction_date' => $date,
                'amount' => 1000,
                'description' => 'Loan payment',
                'expense_id' => $expense->id,
                'account_id' => null,
                'is_penalty' => false,
                'is_payroll' => false,
                'payroll_month' => null,
                'payroll_year' => null,
            ]);
        }

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-04']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.count', 0)
                ->where('cutoffs.first.count', 0)
                ->where('cutoffs.second.count', 0),
            );
    }

    public function test_cutoff_does_not_include_a_fully_paid_one_time_loan_in_the_next_month(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Appliance Loan',
            'type' => 'loan',
            'payment_mode' => 'one_time',
            'date_start' => '2026-03-01',
            'date_end' => '2026-06-30',
            'pay_in' => 'first',
            'total_amount' => 1000,
            'paid_amount' => 1000,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-03-10',
            'amount' => 1000,
            'description' => 'Full payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-04']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.count', 0)
                ->where('summary.total_amount', 0)
                ->where('cutoffs.first.count', 0)
                ->where('cutoffs.second.count', 0),
            );
    }

    public function test_cutoff_carries_an_unpaid_one_time_loan_balance_into_the_next_month(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Appliance Loan',
            'type' => 'loan',
            'payment_mode' => 'one_time',
            'date_start' => '2026-03-01',
            'date_end' => '2026-06-30',
            'pay_in' => 'first',
            'total_amount' => 1000,
            'paid_amount' => 400,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-03-10',
            'amount' => 400,
            'description' => 'Partial payment',
            'expense_id' => $expense->id,
            'account_id' => null,
            'is_penalty' => false,
            'is_payroll' => false,
            'payroll_month' => null,
            'payroll_year' => null,
        ]);

        $this->actingAs($user)
            ->get(route('cutoff.index', ['month' => '2026-04']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.count', 1)
                ->where('summary.total_amount', 600)
                ->where('cutoffs.first.items.0.name', 'Appliance Loan')
                ->where('cutoffs.first.items.0.total_amount', 0)
                ->where('cutoffs.first.items.0.previous_balance_amount', 600)
                ->where('cutoffs.first.items.0.effective_due_amount', 600)
                ->where('cutoffs.first.items.0.remaining_amount', 600),
            );
    }
}
