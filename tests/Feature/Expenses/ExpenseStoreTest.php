<?php

namespace Tests\Feature\Expenses;

use App\Models\Expenses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_create_an_expense_with_pay_in(): void
    {
        $user = User::factory()->create();

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
                'payment_due' => '2026-03-15',
                'pay_in' => 'first',
                'is_recurring' => true,
                'recurring_cycle' => 'monthly',
                'description' => 'Monthly internet bill',
            ])
            ->assertRedirect(route('expenses.index'));

        $expense = Expenses::query()->first();

        $this->assertNotNull($expense);
        $this->assertSame('first', $expense->pay_in);
        $this->assertSame($user->id, $expense->created_by);
    }

    public function test_pay_in_is_required_when_creating_an_expense(): void
    {
        $user = User::factory()->create();

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
}
