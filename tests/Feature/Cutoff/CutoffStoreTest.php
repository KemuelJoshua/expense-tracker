<?php

namespace Tests\Feature\Cutoff;

use App\Models\Expenses;
use App\Models\SpendIncome;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CutoffStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_cutoff_store_creates_a_linked_payment_entry_and_updates_the_expense_balance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Office Internet',
            'date_start' => '2026-03-01',
            'date_end' => null,
            'pay_in' => 'first',
            'paid_amount' => 250,
            'total_amount' => 1000,
        ]);

        $this->actingAs($user)
            ->post(route('cutoff.store'), [
                'action_type' => 'payment',
                'month' => '2026-03',
                'expense_id' => $expense->id,
                'transaction_date' => '2026-03-18',
                'amount' => '400.25',
                'description' => 'Partial payment from cutoff page',
            ])
            ->assertRedirect(route('cutoff.index', ['month' => '2026-03']));

        $expense->refresh();
        $payment = SpendIncome::query()->sole();

        $this->assertSame(650.25, (float) $expense->paid_amount);
        $this->assertSame($user->id, $payment->user_id);
        $this->assertSame('spend', $payment->entry_type);
        $this->assertSame($expense->id, $payment->expense_id);
        $this->assertFalse($payment->is_penalty);
        $this->assertSame('2026-03-18', $payment->transaction_date?->toDateString());
        $this->assertSame(400.25, (float) $payment->amount);
        $this->assertSame('Partial payment from cutoff page', $payment->description);
    }

    public function test_cutoff_store_creates_a_penalty_without_increasing_the_paid_balance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Office Internet',
            'date_start' => '2026-03-01',
            'date_end' => null,
            'pay_in' => 'first',
            'paid_amount' => 250,
            'total_amount' => 1000,
        ]);

        $this->actingAs($user)
            ->post(route('cutoff.store'), [
                'action_type' => 'penalty',
                'month' => '2026-03',
                'expense_id' => $expense->id,
                'transaction_date' => '2026-03-20',
                'amount' => '75.50',
                'description' => 'Late fee',
            ])
            ->assertRedirect(route('cutoff.index', ['month' => '2026-03']));

        $expense->refresh();
        $penalty = SpendIncome::query()->sole();

        $this->assertSame(250.0, (float) $expense->paid_amount);
        $this->assertTrue($penalty->is_penalty);
        $this->assertSame(75.50, (float) $penalty->amount);
        $this->assertSame('Late fee', $penalty->description);
    }

    public function test_cutoff_store_rejects_payment_dates_outside_the_selected_month(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $expense = Expenses::factory()->create([
            'created_by' => $user->id,
            'date_start' => '2026-03-01',
            'date_end' => null,
            'pay_in' => 'first',
        ]);

        $this->actingAs($user)
            ->from(route('cutoff.index', ['month' => '2026-03']))
            ->post(route('cutoff.store'), [
                'action_type' => 'payment',
                'month' => '2026-03',
                'expense_id' => $expense->id,
                'transaction_date' => '2026-04-01',
                'amount' => '100',
                'description' => 'Invalid month payment',
            ])
            ->assertRedirect(route('cutoff.index', ['month' => '2026-03']))
            ->assertSessionHasErrors('transaction_date');

        $this->assertDatabaseCount('spend_incomes', 0);
    }
}
