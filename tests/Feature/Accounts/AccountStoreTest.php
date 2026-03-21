<?php

namespace Tests\Feature\Accounts;

use App\Models\Accounts;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_create_an_account(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('accounts.store'), [
                'account_name' => 'BDO Payroll',
                'account_type' => 'bank',
                'balance' => 18000,
                'initial_balance' => 12000,
                'account_number' => '1234-5678-9012',
                'bank_name' => 'BDO',
                'currency' => 'PHP',
                'is_active' => true,
                'is_default' => true,
                'description' => 'Primary payroll account',
            ])
            ->assertRedirect(route('accounts.index'));

        $account = Accounts::query()->first();

        $this->assertNotNull($account);
        $this->assertSame('BDO Payroll', $account->account_name);
        $this->assertSame('bank', $account->account_type);
        $this->assertSame('18000.0000', $account->balance);
        $this->assertSame('12000.0000', $account->initial_balance);
        $this->assertSame($user->id, $account->user_id);
        $this->assertTrue($account->is_default);
    }

    public function test_default_account_creation_clears_existing_default_for_the_same_user(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $existingDefault = Accounts::factory()->create([
            'user_id' => $user->id,
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->post(route('accounts.store'), [
                'account_name' => 'GCash Wallet',
                'account_type' => 'e-wallet',
                'balance' => 5000,
                'initial_balance' => 5000,
                'account_number' => '09171234567',
                'bank_name' => 'GCash',
                'currency' => 'PHP',
                'is_active' => true,
                'is_default' => true,
                'description' => 'Daily wallet',
            ])
            ->assertRedirect(route('accounts.index'));

        $this->assertFalse((bool) $existingDefault->fresh()->is_default);
        $this->assertSame(1, Accounts::query()->where('user_id', $user->id)->where('is_default', true)->count());
    }

    public function test_account_name_and_currency_are_required_when_creating_an_account(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->from(route('accounts.index'))
            ->post(route('accounts.store'), [
                'account_type' => 'bank',
                'balance' => 1000,
                'initial_balance' => 1000,
            ])
            ->assertRedirect(route('accounts.index'))
            ->assertSessionHasErrors(['account_name', 'currency']);
    }
}
