<?php

namespace Tests\Feature\Expenses;

use App\Models\Expenses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_update_an_expense(): void
    {
        $user = User::factory()->create();

        $expense = Expenses::factory()->create([
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
            'description' => 'Original description',
        ]);

        $this->actingAs($user)
            ->put(route('expenses.update', $expense), [
                'name' => 'Updated Internet',
                'total_amount' => 1500,
                'paid_amount' => 750,
                'type' => 'utilities',
                'category' => 'Operations',
                'reference_no' => 'INV-2002',
                'date_start' => '2026-03-05',
                'date_end' => '2026-04-05',
                'payment_due' => '2026-03-20',
                'pay_in' => 'second',
                'is_recurring' => true,
                'recurring_cycle' => 'yearly',
                'description' => 'Updated description',
            ])
            ->assertRedirect(route('expenses.index'));

        $expense->refresh();

        $this->assertSame('Updated Internet', $expense->name);
        $this->assertSame('1500.0000', $expense->total_amount);
        $this->assertSame('750.0000', $expense->paid_amount);
        $this->assertSame('Operations', $expense->category);
        $this->assertSame('INV-2002', $expense->reference_no);
        $this->assertSame('2026-03-05', $expense->date_start);
        $this->assertSame('2026-04-05', $expense->date_end);
        $this->assertSame('2026-03-20', $expense->payment_due);
        $this->assertSame('second', $expense->pay_in);
        $this->assertTrue((bool) $expense->is_recurring);
        $this->assertSame('yearly', $expense->recurring_cycle);
        $this->assertSame('Updated description', $expense->description);
    }

    public function test_an_authenticated_user_can_update_an_expense_with_method_spoofing_and_attachment(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $expense = Expenses::factory()->create([
            'attachment' => 'expenses/original-receipt.pdf',
        ]);

        $attachment = UploadedFile::fake()->create('updated-receipt.pdf', 120, 'application/pdf');

        $this->actingAs($user)
            ->post(route('expenses.update', $expense), [
                '_method' => 'PATCH',
                'name' => 'Updated Internet',
                'total_amount' => 1500,
                'paid_amount' => 750,
                'type' => 'utilities',
                'category' => 'Operations',
                'reference_no' => 'INV-2002',
                'date_start' => '2026-03-05',
                'date_end' => '2026-04-05',
                'payment_due' => '2026-03-20',
                'pay_in' => 'second',
                'is_recurring' => true,
                'recurring_cycle' => 'yearly',
                'description' => 'Updated description',
                'attachment' => $attachment,
            ])
            ->assertRedirect(route('expenses.index'));

        $expense->refresh();

        $this->assertSame('Updated Internet', $expense->name);
        $this->assertSame('second', $expense->pay_in);
        $this->assertNotNull($expense->attachment);
        Storage::disk('public')->assertExists($expense->attachment);
    }
}
