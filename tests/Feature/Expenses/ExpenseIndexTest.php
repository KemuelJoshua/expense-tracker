<?php

namespace Tests\Feature\Expenses;

use App\Models\Expenses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExpenseIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_expenses_index_returns_paginated_expenses(): void
    {
        $user = User::factory()->create();

        Expenses::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('expenses/Index')
                ->has('expenses.data', 3)
                ->where('filters.search', ''),
            );
    }

    public function test_expenses_index_filters_expenses_using_the_search_query(): void
    {
        $user = User::factory()->create();

        Expenses::factory()->create([
            'name' => 'Office Internet',
            'type' => 'utilities',
            'category' => 'Connectivity',
            'reference_no' => 'INV-1001',
        ]);

        Expenses::factory()->create([
            'name' => 'Team Lunch',
            'type' => 'others',
            'category' => 'Meals',
            'reference_no' => 'INV-2002',
        ]);

        $this->actingAs($user)
            ->get(route('expenses.index', ['search' => 'internet']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('expenses/Index')
                ->where('filters.search', 'internet')
                ->has('expenses.data', 1)
                ->where('expenses.data.0.name', 'Office Internet'),
            );
    }
}
