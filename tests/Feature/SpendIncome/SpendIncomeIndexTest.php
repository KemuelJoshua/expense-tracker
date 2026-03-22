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
}
