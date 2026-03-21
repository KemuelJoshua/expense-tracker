<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

import { edit, show, store, update } from '@/actions/App/Http/Controllers/AccountsController';
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { useAccountForm } from './composables/useAccountForm';
import Create from './Create.vue';
import AccountActionsDropdown from './partials/AccountActionsDropdown.vue';
import AccountsTable from './partials/AccountsTable.vue';
import Show from './Show.vue';
import type { Account, Filters, PaginatedAccounts } from './types/accounts';

const open = ref(false);
const mode = ref<'create' | 'edit'>('create');
const selectedId = ref<number>();
const viewOpen = ref(false);
const selectedAccount = ref<Account | null>(null);

const { form, resetForm } = useAccountForm();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Accounts',
        href: '/accounts',
    },
];

defineProps<{
    accounts: PaginatedAccounts;
    filters: Filters;
}>();

const handleCreate = (): void => {
    mode.value = 'create';
    resetForm();
};

const handleClose = (): void => {
    resetForm();
    mode.value = 'create';
};

const handleEdit = async (id: number): Promise<void> => {
    resetForm();
    mode.value = 'edit';
    selectedId.value = id;

    const { data } = await axios.get(edit.url(id));

    form.account_name = data.account_name ?? '';
    form.account_type = data.account_type ?? '';
    form.balance = data.balance ?? '';
    form.initial_balance = data.initial_balance ?? '';
    form.account_number = data.account_number ?? '';
    form.bank_name = data.bank_name ?? '';
    form.currency = data.currency ?? 'PHP';
    form.is_active = Number(data.is_active ?? 1);
    form.is_default = Number(data.is_default ?? 0);
    form.description = data.description ?? '';

    form.clearErrors();
    open.value = true;
};

const handleView = async (id: number): Promise<void> => {
    const { data } = await axios.get(show.url(id));
    selectedAccount.value = data;
    viewOpen.value = true;
};

const submit = (): void => {
    if (mode.value === 'edit') {
        if (selectedId.value === undefined) {
            return;
        }

        const updateAccount = update.form.patch(selectedId.value);

        form.submit(updateAccount.method, updateAccount.action, {
            onSuccess: () => {
                resetForm();
                open.value = false;
                mode.value = 'create';
            },
        });

        return;
    }

    form.post(store.url(), {
        onSuccess: () => {
            resetForm();
            open.value = false;
            mode.value = 'create';
        },
    });
};
</script>

<template>
    <Head title="Accounts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <Show
                :open="viewOpen"
                :account="selectedAccount"
                @update:open="viewOpen = $event"
                @close="selectedAccount = null"
            />

            <AccountsTable
                :accounts="accounts.data"
                :filters="filters"
                :summary="{
                    from: accounts.from,
                    to: accounts.to,
                    total: accounts.total,
                }"
            >
                <template #buttons>
                    <Create
                        :open="open"
                        :form="form"
                        :mode="mode"
                        @update:open="open = $event"
                        @create="handleCreate"
                        @submit="submit"
                        @close="handleClose"
                    />
                </template>

                <template #actions="{ account }">
                    <AccountActionsDropdown
                        :account="account"
                        @view="handleView"
                        @edit="handleEdit"
                    />
                </template>
            </AccountsTable>

            <Pagination :pagination="accounts" />
        </div>
    </AppLayout>
</template>
