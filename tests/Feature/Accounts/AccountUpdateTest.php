<?php

namespace Tests\Feature\Accounts;

use App\Models\Accounts;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_update_an_account(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $account = Accounts::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'Old Cash',
            'account_type' => 'cash',
            'balance' => 1200,
            'initial_balance' => 1000,
            'account_number' => null,
            'bank_name' => null,
            'currency' => 'PHP',
            'is_active' => true,
            'is_default' => false,
            'description' => 'Old description',
        ]);

        $this->actingAs($user)
            ->put(route('accounts.update', $account), [
                'account_name' => 'Updated BPI Savings',
                'account_type' => 'bank',
                'balance' => 14500,
                'initial_balance' => 8000,
                'account_number' => '9876-5432-1098',
                'bank_name' => 'BPI',
                'currency' => 'USD',
                'is_active' => false,
                'is_default' => true,
                'description' => 'Updated description',
            ])
            ->assertRedirect(route('accounts.index'));

        $account->refresh();

        $this->assertSame('Updated BPI Savings', $account->account_name);
        $this->assertSame('bank', $account->account_type);
        $this->assertSame('14500.0000', $account->balance);
        $this->assertSame('8000.0000', $account->initial_balance);
        $this->assertSame('9876-5432-1098', $account->account_number);
        $this->assertSame('BPI', $account->bank_name);
        $this->assertSame('USD', $account->currency);
        $this->assertFalse($account->is_active);
        $this->assertTrue($account->is_default);
        $this->assertSame('Updated description', $account->description);
    }

    public function test_updating_an_account_as_default_clears_the_previous_default(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $currentDefault = Accounts::factory()->create([
            'user_id' => $user->id,
            'is_default' => true,
        ]);

        $accountToPromote = Accounts::factory()->create([
            'user_id' => $user->id,
            'is_default' => false,
        ]);

        $this->actingAs($user)
            ->put(route('accounts.update', $accountToPromote), [
                'account_name' => $accountToPromote->account_name,
                'account_type' => $accountToPromote->account_type,
                'balance' => $accountToPromote->balance,
                'initial_balance' => $accountToPromote->initial_balance,
                'account_number' => $accountToPromote->account_number,
                'bank_name' => $accountToPromote->bank_name,
                'currency' => $accountToPromote->currency,
                'is_active' => $accountToPromote->is_active,
                'is_default' => true,
                'description' => $accountToPromote->description,
            ])
            ->assertRedirect(route('accounts.index'));

        $this->assertFalse((bool) $currentDefault->fresh()->is_default);
        $this->assertTrue((bool) $accountToPromote->fresh()->is_default);
    }

    public function test_show_returns_the_account_payload(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $account = Accounts::factory()->create();

        $this->actingAs($user)
            ->get(route('accounts.show', $account))
            ->assertOk()
            ->assertJson([
                'id' => $account->id,
                'account_name' => $account->account_name,
            ]);
    }

    public function test_edit_returns_the_account_payload(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $account = Accounts::factory()->create();

        $this->actingAs($user)
            ->get(route('accounts.edit', $account))
            ->assertOk()
            ->assertJson([
                'id' => $account->id,
                'account_name' => $account->account_name,
            ]);
    }

    public function test_an_authenticated_user_can_delete_an_account(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();
        $account = Accounts::factory()->create();

        $this->actingAs($user)
            ->delete(route('accounts.destroy', $account))
            ->assertRedirect(route('accounts.index'));

        $this->assertSoftDeleted('accounts', [
            'id' => $account->id,
        ]);
    }
}
