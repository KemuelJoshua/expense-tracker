<?php

namespace Tests\Feature\Feature\Security;

use App\Models\Accounts;
use App\Models\Expenses;
use App\Models\SpendIncome;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SensitiveDataEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_financial_values_are_encrypted_at_rest(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $account = Accounts::factory()->create([
            'user_id' => $user->id,
            'balance' => 18000,
            'initial_balance' => 12000,
            'account_number' => '1234-5678-9012',
        ]);

        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'total_amount' => 1250,
            'paid_amount' => 500,
        ]);

        $entry = SpendIncome::factory()->create([
            'user_id' => $user->id,
            'amount' => 2500,
        ]);

        $rawAccount = DB::table('accounts')->where('id', $account->id)->first();
        $rawExpense = DB::table('expenses')->where('id', $expense->id)->first();
        $rawEntry = DB::table('spend_incomes')->where('id', $entry->id)->first();

        $this->assertNotSame('18000.0000', $rawAccount->balance);
        $this->assertNotSame('12000.0000', $rawAccount->initial_balance);
        $this->assertNotSame('1234-5678-9012', $rawAccount->account_number);
        $this->assertNotSame('1250.0000', $rawExpense->total_amount);
        $this->assertNotSame('500.0000', $rawExpense->paid_amount);
        $this->assertNotSame('2500.0000', $rawEntry->amount);

        $this->assertSame('18000.0000', $account->fresh()->balance);
        $this->assertSame('12000.0000', $account->fresh()->initial_balance);
        $this->assertSame('1234-5678-9012', $account->fresh()->account_number);
        $this->assertSame('1250.0000', $expense->fresh()->total_amount);
        $this->assertSame('500.0000', $expense->fresh()->paid_amount);
        $this->assertSame('2500.0000', $entry->fresh()->amount);
    }
}
