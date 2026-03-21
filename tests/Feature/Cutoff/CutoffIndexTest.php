<?php

namespace Tests\Feature\Cutoff;

use App\Models\Expenses;
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
            'pay_in' => 'first',
            'paid_amount' => 400,
            'total_amount' => 1200,
        ]);

        Expenses::factory()->create([
            'created_by' => $user->id,
            'name' => 'Office Rent',
            'date_start' => '2026-03-18',
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
                ->where('cutoffs.first.count', 1)
                ->where('cutoffs.second.count', 1)
                ->where('cutoffs.first.items.0.name', 'Internet Bill')
                ->where('cutoffs.second.items.0.name', 'Office Rent'),
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
}
