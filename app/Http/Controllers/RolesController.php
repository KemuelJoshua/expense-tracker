<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRolePermissionsRequest;
use App\Support\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(): Response
    {
        $this->authorizePermission('roles.view');

        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'is_protected' => in_array($role->name, PermissionRegistry::protectedRoles(), true),
            ])
            ->values();

        return Inertia::render('roles/Index', [
            'roles' => $roles,
            'modules' => collect(PermissionRegistry::modules())
                ->map(fn (array $module, string $key): array => [
                    'key' => $key,
                    'label' => $module['label'],
                    'permissions' => collect($module['permissions'])
                        ->map(fn (string $label, string $name): array => [
                            'name' => $name,
                            'label' => $label,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorizePermission('roles.create');

        Role::create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function update(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        $this->authorizePermission('roles.update');

        abort_if(
            in_array($role->name, PermissionRegistry::elevatedRoles(), true)
                && ! $request->user()?->hasRole('SuperAdmin'),
            403,
        );

        $permissions = in_array($role->name, PermissionRegistry::elevatedRoles(), true)
            ? PermissionRegistry::permissions()
            : $request->validated('permissions', []);

        $role->syncPermissions($permissions);

        return redirect()
            ->route('roles.index')
            ->with('success', "{$role->name} permissions updated successfully.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorizePermission('roles.delete');

        abort_if(
            in_array($role->name, PermissionRegistry::protectedRoles(), true),
            422,
            'This role cannot be deleted.',
        );

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "{$role->name} deleted successfully.");
    }
}
