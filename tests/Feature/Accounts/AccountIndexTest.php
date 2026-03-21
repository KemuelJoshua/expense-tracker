<?php

namespace Tests\Feature\Accounts;

use App\Models\Accounts;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_index_returns_paginated_accounts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        Accounts::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('accounts/Index')
                ->has('accounts.data', 3)
                ->where('filters.search', ''),
            );
    }

    public function test_accounts_index_filters_accounts_using_the_search_query(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        Accounts::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'GCash Wallet',
            'account_type' => 'e-wallet',
            'bank_name' => 'GCash',
            'account_number' => '09171234567',
        ]);

        Accounts::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'Petty Cash',
            'account_type' => 'cash',
            'bank_name' => null,
            'account_number' => null,
        ]);

        $this->actingAs($user)
            ->get(route('accounts.index', ['search' => 'gcash']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('accounts/Index')
                ->where('filters.search', 'gcash')
                ->has('accounts.data', 1)
                ->where('accounts.data.0.account_name', 'GCash Wallet'),
            );
    }

    public function test_accounts_index_only_returns_the_authenticated_users_accounts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $otherUser = User::factory()->admin()->create();

        Accounts::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'My Account',
        ]);

        Accounts::factory()->create([
            'user_id' => $otherUser->id,
            'account_name' => 'Other Account',
        ]);

        $this->actingAs($user)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('accounts.data', 1)
                ->where('accounts.data.0.account_name', 'My Account'),
            );
    }
}
