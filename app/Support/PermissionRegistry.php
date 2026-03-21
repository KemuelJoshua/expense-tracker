<?php

namespace App\Support;

class PermissionRegistry
{
    /**
     * @return array<string, array{label: string, permissions: array<string, string>}>
     */
    public static function modules(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'permissions' => [
                    'dashboard.view' => 'View dashboard',
                ],
            ],
            'expenses' => [
                'label' => 'Expenses',
                'permissions' => [
                    'expenses.view' => 'View expenses',
                    'expenses.create' => 'Create expenses',
                    'expenses.update' => 'Edit expenses',
                    'expenses.delete' => 'Delete expenses',
                ],
            ],
            'accounts' => [
                'label' => 'Accounts',
                'permissions' => [
                    'accounts.view' => 'View accounts',
                    'accounts.create' => 'Create accounts',
                    'accounts.update' => 'Edit accounts',
                    'accounts.delete' => 'Delete accounts',
                ],
            ],
            'cutoff' => [
                'label' => 'Cutoff',
                'permissions' => [
                    'cutoff.view' => 'View cutoff',
                ],
            ],
            'roles' => [
                'label' => 'Roles',
                'permissions' => [
                    'roles.view' => 'View roles',
                    'roles.create' => 'Create roles',
                    'roles.update' => 'Edit role permissions',
                    'roles.delete' => 'Delete roles',
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        $modulePermissions = array_map(
            static fn (array $module): array => array_keys($module['permissions']),
            array_values(self::modules()),
        );

        return array_values(
            array_merge(...$modulePermissions),
        );
    }

    /**
     * @return array<string, list<string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'SuperAdmin' => self::permissions(),
            'Admin' => self::permissions(),
            'EndUser' => [
                'dashboard.view',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function protectedRoles(): array
    {
        return [
            'SuperAdmin',
            'Admin',
            'EndUser',
        ];
    }

    /**
     * @return list<string>
     */
    public static function elevatedRoles(): array
    {
        return [
            'SuperAdmin',
            'Admin',
        ];
    }
}
