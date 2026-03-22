<?php

namespace Tests\Feature\SpendIncome;

use App\Models\SpendIncome;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SpendIncomeIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_entries_for_the_authenticated_user(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $otherUser = User::factory()->admin()->create();

        SpendIncome::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        SpendIncome::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->get(route('spend-income.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('spend-income/Index')
                ->has('spendIncomes.data', 2)
                ->where('filters.search', '')
                ->has('options.expenses')
                ->has('options.accounts'),
            );
    }

    public function test_index_returns_newest_entries_first(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'transaction_date' => '2026-03-20',
            'entry_type' => 'spend',
            'description' => 'Older entry',
        ]);

        SpendIncome::factory()->create([
            'user_id' => $user->id,
            'transaction_date' => '2026-03-10',
            'entry_type' => 'income',
            'description' => 'Newest entry',
        ]);

        $this->actingAs($user)
            ->get(route('spend-income.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('spendIncomes.data.0.description', 'Newest entry')
                ->where('spendIncomes.data.1.description', 'Older entry'),
            );
    }
}
