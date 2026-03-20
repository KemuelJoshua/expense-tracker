<script setup lang="ts">
import { router, Head, useForm, usePage } from '@inertiajs/vue3';
import { ShieldCheck, Trash2 } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import {
    store,
    update,
    destroy,
} from '@/actions/App/Http/Controllers/RolesController';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type PermissionOption = {
    name: string;
    label: string;
};

type PermissionModule = {
    key: string;
    label: string;
    permissions: PermissionOption[];
};

type RoleItem = {
    id: number;
    name: string;
    permissions: string[];
    is_protected: boolean;
};

type AuthUser = {
    roles: string[];
    permissions: string[];
};

const props = defineProps<{
    roles: RoleItem[];
    modules: PermissionModule[];
}>();

const page = usePage<{
    auth: {
        user: AuthUser;
    };
    flash?: {
        success?: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/roles',
    },
];

const selectedPermissions = reactive<Record<number, string[]>>(
    Object.fromEntries(
        props.roles.map((role) => [role.id, [...role.permissions]]),
    ),
);

const savingRoleId = ref<number | null>(null);
const creatingRole = useForm({
    name: '',
});

const flashSuccess = computed(() => page.props.flash?.success ?? null);
const userRoles = computed(() => page.props.auth.user.roles ?? []);
const userPermissions = computed(() => page.props.auth.user.permissions ?? []);
const canCreateRoles = computed(() => userPermissions.value.includes('roles.create'));
const canUpdateRoles = computed(() => userPermissions.value.includes('roles.update'));
const canDeleteRoles = computed(() => userPermissions.value.includes('roles.delete'));
const isSuperAdmin = computed(() => userRoles.value.includes('SuperAdmin'));

const hasPermission = (roleId: number, permission: string): boolean => {
    return selectedPermissions[roleId]?.includes(permission) ?? false;
};

const isElevatedRole = (roleName: string): boolean => {
    return ['SuperAdmin', 'Admin'].includes(roleName);
};

const canManageRolePermissions = (role: RoleItem): boolean => {
    if (!canUpdateRoles.value) {
        return false;
    }

    if (isElevatedRole(role.name)) {
        return isSuperAdmin.value;
    }

    return true;
};

const createRole = (): void => {
    creatingRole.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            creatingRole.reset();
        },
    });
};

const togglePermission = (
    roleId: number,
    permission: string,
    checked: boolean,
): void => {
    const currentPermissions = selectedPermissions[roleId] ?? [];

    selectedPermissions[roleId] = checked
        ? [...new Set([...currentPermissions, permission])]
        : currentPermissions.filter(
              (existingPermission) => existingPermission !== permission,
          );
};

const saveRolePermissions = (role: RoleItem): void => {
    savingRoleId.value = role.id;

    router.put(
        update.url(role.id),
        {
            permissions: selectedPermissions[role.id] ?? [],
        },
        {
            preserveScroll: true,
            onFinish: () => {
                savingRoleId.value = null;
            },
        },
    );
};

const deleteRole = (role: RoleItem): void => {
    if (
        role.is_protected ||
        !window.confirm(`Delete the ${role.name} role?`)
    ) {
        return;
    }

    router.delete(destroy.url(role.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tracker | Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 text-sm font-medium text-primary">
                        <ShieldCheck class="size-4" />
                        Access control
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Roles and permissions
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Admin always keeps full access. Update the EndUser role by module permission.
                        </p>
                    </div>
                </div>

                <p v-if="flashSuccess" class="text-sm font-medium text-primary">
                    {{ flashSuccess }}
                </p>
            </div>

            <section
                v-if="canCreateRoles"
                class="rounded-2xl border bg-background p-6 shadow-sm"
            >
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Create role</h2>
                        <p class="text-sm text-muted-foreground">
                            Add a new role, then configure its module permissions below.
                        </p>
                    </div>

                    <form
                        class="flex w-full flex-col gap-3 md:max-w-md md:flex-row"
                        @submit.prevent="createRole"
                    >
                        <div class="flex-1 space-y-2">
                            <Input
                                v-model="creatingRole.name"
                                placeholder="Role name"
                            />
                            <p
                                v-if="creatingRole.errors.name"
                                class="text-sm text-destructive"
                            >
                                {{ creatingRole.errors.name }}
                            </p>
                        </div>
                        <Button
                            type="submit"
                            :disabled="creatingRole.processing"
                        >
                            {{ creatingRole.processing ? 'Creating...' : 'Add role' }}
                        </Button>
                    </form>
                </div>
            </section>

            <div class="grid gap-6">
                <section
                    v-for="role in props.roles"
                    :key="role.id"
                    class="rounded-2xl border bg-background p-6 shadow-sm"
                >
                    <div class="flex flex-col gap-4 border-b pb-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">{{ role.name }}</h2>
                            <p class="text-sm text-muted-foreground">
                                {{
                                    isElevatedRole(role.name)
                                        ? 'Only SuperAdmin can manage this elevated role. Elevated roles always keep full access.'
                                        : 'Toggle module permissions for users assigned to this role.'
                                }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                v-if="canManageRolePermissions(role)"
                                :disabled="savingRoleId === role.id"
                                @click="saveRolePermissions(role)"
                            >
                                {{ savingRoleId === role.id ? 'Saving...' : 'Save permissions' }}
                            </Button>

                            <Button
                                v-if="!role.is_protected && canDeleteRoles"
                                variant="outline"
                                size="icon"
                                @click="deleteRole(role)"
                            >
                                <Trash2 class="size-4" />
                            </Button>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 xl:grid-cols-2">
                        <div
                            v-for="module in props.modules"
                            :key="`${role.id}-${module.key}`"
                            class="space-y-4 rounded-xl border bg-muted/20 p-4"
                        >
                            <div>
                                <h3 class="text-sm font-semibold">{{ module.label }}</h3>
                                <p class="text-xs text-muted-foreground">
                                    Module access and actions for {{ role.name }}.
                                </p>
                            </div>

                            <div class="grid gap-3">
                                <label
                                    v-for="permission in module.permissions"
                                    :key="permission.name"
                                    class="flex items-start gap-3 rounded-lg bg-background px-3 py-3"
                                >
                                    <Checkbox
                                        :model-value="hasPermission(role.id, permission.name)"
                                        :disabled="!canManageRolePermissions(role)"
                                        @update:model-value="
                                            togglePermission(role.id, permission.name, $event === true)
                                        "
                                    />
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium">
                                            {{ permission.label }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ permission.name }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
