<?php

namespace Tests\Feature\Database;

use App\Models\Accounts;
use App\Models\Expenses;
use Database\Seeders\ExpenseAndSavingsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseAndSavingsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_and_savings_seeder_creates_ten_expenses_and_two_savings_accounts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ExpenseAndSavingsSeeder::class);

        $this->assertSame(10, Expenses::query()->count());
        $this->assertSame(2, Accounts::query()->count());
        $this->assertSame(
            2,
            Accounts::query()
                ->whereIn('account_name', ['Emergency Savings', 'Travel Savings'])
                ->count(),
        );
        $this->assertSame(
            1,
            Accounts::query()
                ->where('account_name', 'Emergency Savings')
                ->where('is_default', true)
                ->count(),
        );
    }
}
