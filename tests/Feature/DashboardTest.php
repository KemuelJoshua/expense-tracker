<?php

namespace Tests\Feature;

use App\Models\Accounts;
use App\Models\Expenses;
use App\Models\SpendIncome;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard_with_analytics(): void
    {
        Carbon::setTestNow('2026-03-22 09:00:00');
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        Accounts::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'Main Payroll',
            'account_type' => 'bank',
            'balance' => 22000,
            'is_default' => true,
        ]);

        Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Office Internet',
            'type' => 'utilities',
            'total_amount' => 3200,
            'paid_amount' => 1200,
            'payment_due' => 25,
            'date_start' => '2026-01-01',
            'date_end' => null,
            'pay_in' => 'second',
            'is_recurring' => true,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'income',
            'transaction_date' => '2026-03-10',
            'amount' => 30000,
            'is_payroll' => true,
            'payroll_month' => 3,
            'payroll_year' => 2026,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'entry_type' => 'spend',
            'transaction_date' => '2026-03-15',
            'amount' => 8500,
            'description' => 'Vendor payment',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('analytics.focus_month.label', 'March 2026')
                ->where('analytics.overview.cash_on_hand', 22000)
                ->where('analytics.overview.monthly_income', 30000)
                ->where('analytics.overview.monthly_spend', 8500)
                ->where('analytics.overview.unpaid_commitments', 2000)
                ->where('analytics.overview.coverage_rate', 37.5)
                ->where('analytics.accounts.default_account_name', 'Main Payroll')
                ->has('analytics.cash_flow.series', 6)
                ->where('analytics.cash_flow.current_month_obligation', 3200)
                ->where('analytics.cash_flow.current_month_paid', 1200)
                ->where('analytics.expenses.due_soon.0.name', 'Office Internet')
                ->where('analytics.activity.payroll.year_total', 30000)
                ->where('analytics.activity.recent_entries.0.entry_type', 'spend'),
            );

        Carbon::setTestNow();
    }
}
