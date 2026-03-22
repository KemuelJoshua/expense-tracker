<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import {
    edit,
    show,
    store,
    update,
} from '@/actions/App/Http/Controllers/SpendIncomeController';
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { useSpendIncomeForm } from './composables/useSpendIncomeForm';
import Create from './Create.vue';
import SpendIncomeActionsDropdown from './partials/SpendIncomeActionsDropdown.vue';
import SpendIncomeTable from './partials/SpendIncomeTable.vue';
import Show from './Show.vue';
import type {
    Filters,
    PaginatedSpendIncomes,
    SpendIncome,
    SpendIncomeOptions,
} from './types/spend-income';

defineProps<{
    spendIncomes: PaginatedSpendIncomes;
    filters: Filters;
    options: SpendIncomeOptions;
}>();

const open = ref(false);
const mode = ref<'create' | 'edit'>('create');
const selectedId = ref<number>();
const viewOpen = ref(false);
const selectedSpendIncome = ref<SpendIncome | null>(null);

const { form, resetForm } = useSpendIncomeForm();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Spend & Income',
        href: '/spend-income',
    },
];

const handleCreate = (type: 'spend' | 'income'): void => {
    mode.value = 'create';
    resetForm();
    form.entry_type = type;
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

    form.entry_type = data.entry_type ?? '';
    form.transaction_date = data.transaction_date ?? '';
    form.amount = data.amount ?? '';
    form.description = data.description ?? '';
    form.expense_reference = data.expense_reference ?? 'others';
    form.account_reference = data.account_reference ?? 'others';
    form.is_payroll = String(Number(data.is_payroll ?? 0));
    form.payroll_month = data.payroll_month ? String(data.payroll_month) : '';
    form.payroll_year = data.payroll_year ? String(data.payroll_year) : '';

    form.clearErrors();
    open.value = true;
};

const handleView = async (id: number): Promise<void> => {
    const { data } = await axios.get(show.url(id));
    selectedSpendIncome.value = data;
    viewOpen.value = true;
};

const submit = (): void => {
    if (mode.value === 'edit') {
        if (selectedId.value === undefined) {
            return;
        }

        const updateEntry = update.form.put(selectedId.value);

        form.submit(updateEntry.method, updateEntry.action, {
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
    <Head title="Tracker | Spend & Income" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <Show :open="viewOpen" :spend-income="selectedSpendIncome" @update:open="viewOpen = $event" />

            <SpendIncomeTable
                :spend-incomes="spendIncomes.data"
                :filters="filters"
            >
                <template #buttons>
                    <Create
                        :open="open"
                        :form="form"
                        :mode="mode"
                        :options="options"
                        @update:open="open = $event"
                        @create="handleCreate"
                        @submit="submit"
                        @close="handleClose"
                    />
                </template>

                <template #actions="{ spendIncome }">
                    <SpendIncomeActionsDropdown
                        :spend-income="spendIncome"
                        @view="handleView"
                        @edit="handleEdit"
                    />
                </template>
            </SpendIncomeTable>

            <Pagination :pagination="spendIncomes" />
        </div>
    </AppLayout>
</template>
