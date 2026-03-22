<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { store as cutoffStore } from '@/actions/App/Http/Controllers/CutoffController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as cutoffIndex } from '@/routes/cutoff';
import type { BreadcrumbItem } from '@/types';
import type { Auth } from '@/types/auth';
import { useFilterForm } from './composables/useFilterForm';
import Filter from './partials/Filter.vue';
import PayDialog from './partials/PayDialog.vue';
import Sheet from './partials/Sheet.vue';
import type {
    CutoffExpense,
    CutoffFilters,
    CutoffGroup,
    CutoffSummary,
} from './types/cutoff';

const props = defineProps<{
    cutoffs: Record<'first' | 'second', CutoffGroup>;
    filters: CutoffFilters;
    summary: CutoffSummary;
}>();

const page = usePage();
const auth = computed<Auth>(() => page.props.auth as Auth);
const { form } = useFilterForm();
const paymentDialogOpen = ref(false);
const selectedExpense = ref<CutoffExpense | null>(null);
const actionType = ref<'payment' | 'penalty'>('payment');

const paymentForm = useForm({
    action_type: 'payment' as 'payment' | 'penalty',
    month: props.filters.month,
    expense_id: '',
    transaction_date: '',
    amount: '',
    description: '',
});

form.date = props.filters.month;

const canCreatePayment = computed(() => {
    return (
        auth.value.user?.permissions.includes('spend_income.create') ?? false
    );
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cutoff',
        href: cutoffIndex(),
    },
];

const fetchCutoff = (): void => {
    router.get(
        cutoffIndex.url({
            query: {
                month: form.date,
            },
        }),
        {},
        {
            only: ['cutoffs', 'filters', 'summary'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

watch(
    () => props.filters.month,
    (month) => {
        form.date = month;
        paymentForm.month = month;
    },
);

const formatCurrency = (value: number | string): string => {
    return Number(value).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const buildDefaultTransactionDate = (
    month: string,
    paymentDue: number | string | null,
): string => {
    const [year, monthNumber] = month.split('-').map(Number);
    const today = new Date();

    if (today.getFullYear() === year && today.getMonth() + 1 === monthNumber) {
        return today.toISOString().slice(0, 10);
    }

    const dueDay = Number(paymentDue);
    const lastDayOfMonth = new Date(year, monthNumber, 0).getDate();
    const day =
        Number.isNaN(dueDay) || dueDay < 1
            ? 1
            : Math.min(dueDay, lastDayOfMonth);

    return `${month}-${String(day).padStart(2, '0')}`;
};

const openActionDialog = (
    expense: CutoffExpense,
    type: 'payment' | 'penalty',
): void => {
    selectedExpense.value = expense;
    actionType.value = type;
    paymentDialogOpen.value = true;
    paymentForm.reset();
    paymentForm.clearErrors();
    paymentForm.action_type = type;
    paymentForm.month = props.filters.month;
    paymentForm.expense_id = String(expense.id);
    paymentForm.transaction_date = buildDefaultTransactionDate(
        props.filters.month,
        expense.payment_due,
    );
    paymentForm.amount =
        type === 'payment'
            ? Number(expense.remaining_amount) > 0
                ? Number(expense.remaining_amount).toFixed(2)
                : ''
            : '';
    paymentForm.description =
        type === 'penalty'
            ? `Penalty for ${expense.name}`
            : `Cutoff payment for ${expense.name}`;
};

const closePayDialog = (): void => {
    paymentDialogOpen.value = false;
    selectedExpense.value = null;
    actionType.value = 'payment';
    paymentForm.reset();
    paymentForm.clearErrors();
    paymentForm.action_type = 'payment';
    paymentForm.month = props.filters.month;
};

const submitPayment = (): void => {
    paymentForm.post(cutoffStore.url(), {
        preserveScroll: true,
        onSuccess: () => {
            closePayDialog();
        },
    });
};
</script>

<template>
    <Head title="Tracker | Cutoff" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full px-4 py-2">
            <div class="space-y-5">
                <PayDialog
                    :open="paymentDialogOpen"
                    :expense="selectedExpense"
                    :month-label="summary.month"
                    :action-type="actionType"
                    v-model:form="paymentForm"
                    @update:open="paymentDialogOpen = $event"
                    @close="closePayDialog"
                    @submit="submitPayment"
                />

                <div class="py-5">
                    <div class="mb-3">
                        <h2 class="text-sm font-medium text-foreground">
                            Filter
                        </h2>
                        <p class="text-xs text-muted-foreground sm:text-sm">
                            Choose a month to load the cutoff summary and
                            grouped expenses.
                        </p>
                    </div>

                    <Filter v-model:date="form.date">
                        <Button class="w-full sm:w-auto" @click="fetchCutoff">
                            Load Cutoff
                        </Button>
                    </Filter>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-xl border bg-background p-4 shadow-sm">
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Selected Month
                        </p>
                        <p class="mt-2 text-lg font-semibold text-foreground">
                            {{ summary.month }}
                        </p>
                    </div>

                    <div class="rounded-xl border bg-background p-4 shadow-sm">
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Total Expenses
                        </p>
                        <p class="mt-2 text-lg font-semibold text-foreground">
                            {{ summary.count }}
                        </p>
                    </div>

                    <div class="rounded-xl border bg-background p-4 shadow-sm">
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Total Amount
                        </p>
                        <p class="mt-2 text-lg font-semibold text-foreground">
                            ₱{{ formatCurrency(summary.total_amount) }}
                        </p>
                    </div>

                    <div class="rounded-xl border bg-background p-4 shadow-sm">
                        <p
                            class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            Paid This Month
                        </p>
                        <p class="mt-2 text-lg font-semibold text-foreground">
                            ₱{{ formatCurrency(summary.total_paid) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <Sheet
                        :cutoff="cutoffs.first"
                        :can-pay="canCreatePayment"
                        @pay="(expense) => openActionDialog(expense, 'payment')"
                        @penalty="
                            (expense) => openActionDialog(expense, 'penalty')
                        "
                    />
                    <Sheet
                        :cutoff="cutoffs.second"
                        :can-pay="canCreatePayment"
                        @pay="(expense) => openActionDialog(expense, 'payment')"
                        @penalty="
                            (expense) => openActionDialog(expense, 'penalty')
                        "
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
