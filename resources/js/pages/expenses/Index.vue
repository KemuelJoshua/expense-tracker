<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

import {
    edit,
    show,
    store,
    update,
} from '@/actions/App/Http/Controllers/ExpensesController';

import { useExpenseForm } from './composables/useExpenseForm';

import Create from './Create.vue';
import Show from './Show.vue';
import ExpenseTable from './partials/ExpenseTable.vue';
import ExpenseActionsDropdown from './partials/ExpenseActionsDropdown.vue';

import type { Expense, Filters, PaginatedExpenses } from './types/expense';

const open = ref(false);
const mode = ref<'create' | 'edit'>('create');
const selected_id = ref<number>();

const viewOpen = ref(false);
const selectedExpense = ref<Expense | null>(null);

const { form, resetForm } = useExpenseForm();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Expenses',
        href: '/expenses',
    },
];

defineProps<{
    expenses: PaginatedExpenses;
    filters: Filters;
}>();

const handleCreate = () => {
    mode.value = 'create';
    resetForm();
};

const handleClose = () => {
    resetForm();
    mode.value = 'create';
};

const handleEdit = async (id: number) => {
    resetForm();
    mode.value = 'edit';
    selected_id.value = id;

    const { data } = await axios.get(edit.url(id));

    form.name = data.name ?? '';
    form.total_amount = data.total_amount ?? '';
    form.paid_amount = data.paid_amount ?? 0;
    form.type = data.type ?? '';
    form.category = data.category ?? '';
    form.reference_no = data.reference_no ?? '';
    form.date_start = data.date_start ?? '';
    form.date_end = data.date_end ?? '';
    form.payment_due = data.payment_due ?? '';
    form.pay_in = data.pay_in ?? 'first';
    form.is_recurring = Number(data.is_recurring ?? 0);
    form.recurring_cycle = data.recurring_cycle ?? '';
    form.description = data.description ?? '';
    form.attachment = null;

    form.clearErrors();
    open.value = true;
};

const handleMap = async (id: number) => {
    const { data } = await axios.get(show.url(id));
    selectedExpense.value = data;
    viewOpen.value = true;
};

const handleView = async (id: number) => {
    const { data } = await axios.get(show.url(id));
    selectedExpense.value = data;
    viewOpen.value = true;
};

const submit = () => {
    if (mode.value === 'edit') {
        if (selected_id.value === undefined) {
            return;
        }

        const updateExpense = update.form.patch(selected_id.value);

        form.submit(updateExpense.method, updateExpense.action, {
            forceFormData: true,
            onSuccess: () => {
                resetForm();
                open.value = false;
                mode.value = 'create';
            },
        });
    } else {
        form.post(store.url(), {
            forceFormData: true,
            onSuccess: () => {
                resetForm();
                open.value = false;
                mode.value = 'create';
            },
        });
    }
};
</script>

<template>
    <Head title="Tracker | Expenses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <Show
                :open="viewOpen"
                :expense="selectedExpense"
                @update:open="viewOpen = $event"
            />
            <ExpenseTable
                :expenses="expenses.data"
                :filters="filters"
                :summary="{
                    from: expenses.from,
                    to: expenses.to,
                    total: expenses.total,
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

                <template #actions="{ expense }">
                    <ExpenseActionsDropdown
                        :expense="expense"
                        @map="handleMap"
                        @view="handleView"
                        @edit="handleEdit"
                    />
                </template>
            </ExpenseTable>
            <Pagination :pagination="expenses" />
        </div>
    </AppLayout>
</template>
