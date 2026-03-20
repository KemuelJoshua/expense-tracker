<script setup lang="ts">
import { router, Head, useForm, usePage } from '@inertiajs/vue3';
import { ShieldCheck, Trash2 } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import {
    store,
    update,
    destroy,
} from '@/actions/App/Http/Controllers/RolesController';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
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

const roleValue = (roleId: number): string => `role-${roleId}`;

const rolePermissionCount = (roleId: number): number => {
    return selectedPermissions[roleId]?.length ?? 0;
};
</script>

<template>
    <Head title="Tracker | Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <section class="overflow-hidden rounded-xl border bg-background">
                <div class="border-b px-4 py-4 md:px-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div class="space-y-1.5">
                            <div class="inline-flex items-center gap-2 text-xs font-medium text-primary">
                                <ShieldCheck class="size-4" />
                                Access control
                            </div>
                            <div>
                                <h1 class="text-lg font-semibold tracking-tight">
                                    Roles and permissions
                                </h1>
                                <p class="text-xs text-muted-foreground sm:text-sm">
                                    Configure module access in a compact role matrix.
                                </p>
                            </div>
                        </div>

                        <form
                            v-if="canCreateRoles"
                            class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md"
                            @submit.prevent="createRole"
                        >
                            <div class="min-w-0 flex-1 space-y-1.5">
                                <Input
                                    v-model="creatingRole.name"
                                    placeholder="Add new role"
                                    class="h-9"
                                />
                                <p
                                    v-if="creatingRole.errors.name"
                                    class="text-xs text-destructive"
                                >
                                    {{ creatingRole.errors.name }}
                                </p>
                            </div>
                            <Button
                                type="submit"
                                :disabled="creatingRole.processing"
                                size="sm"
                                class="sm:self-start"
                            >
                                {{ creatingRole.processing ? 'Creating...' : 'Add role' }}
                            </Button>
                        </form>
                    </div>
                </div>

                <Accordion
                    type="multiple"
                    class="divide-y"
                >
                    <AccordionItem
                        v-for="role in props.roles"
                        :key="role.id"
                        :value="roleValue(role.id)"
                        class="border-0 px-4 md:px-5"
                    >
                        <AccordionTrigger class="gap-3 py-3 hover:no-underline">
                            <div class="flex min-w-0 flex-1 flex-col gap-2 pr-2 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0 space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-sm font-semibold">{{ role.name }}</h2>
                                        <Badge
                                            v-if="role.is_protected"
                                            variant="secondary"
                                            class="h-5 px-2 text-[10px] uppercase tracking-[0.14em]"
                                        >
                                            Protected
                                        </Badge>
                                        <Badge
                                            v-if="isElevatedRole(role.name)"
                                            variant="outline"
                                            class="h-5 px-2 text-[10px] uppercase tracking-[0.14em]"
                                        >
                                            Elevated
                                        </Badge>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-muted-foreground">
                                        <span>{{ rolePermissionCount(role.id) }} assigned</span>
                                        <span>{{ props.modules.length }} modules</span>
                                        <span v-if="isElevatedRole(role.name)">
                                            Only SuperAdmin can edit this role
                                        </span>
                                    </div>
                                </div>

                                <div class="hidden shrink-0 items-center gap-2 md:flex">
                                    <Button
                                        v-if="canManageRolePermissions(role)"
                                        size="sm"
                                        :disabled="savingRoleId === role.id"
                                        @click.stop="saveRolePermissions(role)"
                                    >
                                        {{ savingRoleId === role.id ? 'Saving...' : 'Save' }}
                                    </Button>

                                    <Button
                                        v-if="!role.is_protected && canDeleteRoles"
                                        variant="outline"
                                        size="icon-sm"
                                        @click.stop="deleteRole(role)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </div>
                        </AccordionTrigger>

                        <AccordionContent class="pb-3">
                            <div class="space-y-3">
                                <p class="text-[11px] text-muted-foreground">
                                    {{
                                        isElevatedRole(role.name)
                                            ? 'Elevated roles keep full access by design.'
                                            : 'Expand a module and toggle only the permissions this role needs.'
                                    }}
                                </p>

                                <div class="grid gap-2 xl:grid-cols-2">
                                    <div
                                        v-for="module in props.modules"
                                        :key="`${role.id}-${module.key}`"
                                        class="rounded-lg border bg-muted/10"
                                    >
                                        <div class="flex items-center justify-between gap-3 border-b px-3 py-2">
                                            <h3 class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">
                                                {{ module.label }}
                                            </h3>
                                            <span class="text-[10px] text-muted-foreground">
                                                {{ module.permissions.length }}
                                            </span>
                                        </div>

                                        <div class="grid gap-px bg-border">
                                            <label
                                                v-for="permission in module.permissions"
                                                :key="permission.name"
                                                class="flex items-center gap-2.5 bg-background px-3 py-2"
                                            >
                                                <Checkbox
                                                    :model-value="hasPermission(role.id, permission.name)"
                                                    :disabled="!canManageRolePermissions(role)"
                                                    @update:model-value="
                                                        togglePermission(role.id, permission.name, $event === true)
                                                    "
                                                />
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium leading-4">
                                                        {{ permission.label }}
                                                    </p>
                                                    <p class="truncate text-[10px] text-muted-foreground">
                                                        {{ permission.name }}
                                                    </p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 md:hidden">
                                    <Button
                                        v-if="canManageRolePermissions(role)"
                                        size="sm"
                                        :disabled="savingRoleId === role.id"
                                        @click="saveRolePermissions(role)"
                                    >
                                        {{ savingRoleId === role.id ? 'Saving...' : 'Save' }}
                                    </Button>

                                    <Button
                                        v-if="!role.is_protected && canDeleteRoles"
                                        variant="outline"
                                        size="icon-sm"
                                        @click="deleteRole(role)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </section>
        </div>
    </AppLayout>
</template>
