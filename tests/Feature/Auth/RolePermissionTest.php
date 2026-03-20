<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\PermissionRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_the_roles_module(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('roles.index'))
            ->assertOk();
    }

    public function test_end_user_is_forbidden_from_accessing_the_roles_module_without_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $endUser = User::factory()->endUser()->create();

        $this->actingAs($endUser)
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();
        $role = Role::findByName('EndUser', 'web');

        $this->actingAs($admin)
            ->put(route('roles.update', $role), [
                'permissions' => ['dashboard.view', 'expenses.view'],
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertTrue($role->fresh()->hasPermissionTo('expenses.view'));
        $this->assertFalse($role->fresh()->hasPermissionTo('expenses.delete'));
    }

    public function test_super_admin_can_update_the_admin_role_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('SuperAdmin');

        $role = Role::findByName('Admin', 'web');

        $this->actingAs($superAdmin)
            ->put(route('roles.update', $role), [
                'permissions' => ['dashboard.view'],
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertEqualsCanonicalizing(
            PermissionRegistry::permissions(),
            $role->fresh()->permissions->pluck('name')->all(),
        );
    }

    public function test_admin_cannot_update_the_admin_role_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();
        $role = Role::findByName('Admin', 'web');

        $this->actingAs($admin)
            ->put(route('roles.update', $role), [
                'permissions' => ['dashboard.view'],
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_a_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('roles.store'), [
                'name' => 'Manager',
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', [
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_can_delete_a_custom_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();
        $role = Role::create([
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_admin_cannot_delete_a_protected_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->admin()->create();
        $role = Role::findByName('EndUser', 'web');

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $role))
            ->assertUnprocessable();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }
}
