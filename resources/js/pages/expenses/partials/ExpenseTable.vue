<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import TableIcon from '@/components/TableIcon.vue';
import { Input } from '@/components/ui/input';
import { formatAmount, formatDate } from '@/lib/formatters';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index as expensesIndex } from '@/routes/expenses';
import EmptyState from '@/components/EmptyState.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';

import type { Expense, Filters, Summary } from '../types/expense';

const props = defineProps<{
    expenses: Expense[];
    filters: Filters;
    summary: Summary;
}>();

const search = ref(props.filters.search ?? '');
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(
    () => props.filters.search,
    (value) => {
        search.value = value ?? '';
    },
);

onBeforeUnmount(() => {
    if (searchTimeout !== null) {
        clearTimeout(searchTimeout);
    }
});

const isLoading = ref(false);

const formatType = (type: string): string => {
    return type.charAt(0).toUpperCase() + type.slice(1);
};

const typeClasses = computed<Record<string, string>>(() => ({
    loan: 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
    subscription:
        'border-sky-500/20 bg-sky-500/10 text-sky-700 dark:text-sky-300',
    utilities:
        'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
    others: 'border-slate-500/20 bg-slate-500/10 text-slate-700 dark:text-slate-300',
}));

const applySearch = (value: string): void => {
    isLoading.value = true;
    const normalizedSearch = value.trim();

    router.get(
        expensesIndex.url({
            query: normalizedSearch === '' ? {} : { search: normalizedSearch },
        }),
        {},
        {
            only: ['expenses', 'filters'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const scheduleSearch = (): void => {
    if (searchTimeout !== null) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = window.setTimeout(() => {
        if (search.value.trim() === (props.filters.search ?? '')) {
            return;
        }

        applySearch(search.value);
    }, 300);
};
</script>

<template>
    <div class="overflow-hidden">
        <div class="flex items-center justify-between gap-4 border-b py-4">
            <!-- Search -->
            <div class="relative w-full max-w-sm">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    type="search"
                    placeholder="Search..."
                    class="h-8 pl-9"
                    @input="scheduleSearch"
                />
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <slot name="buttons"></slot>
            </div>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Expense</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Schedule</TableHead>
                    <TableHead>Reference</TableHead>
                    <TableHead class="text-right">Amounts</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <!-- Loading rows -->
                <template v-if="isLoading">
                    <TableSkeleton :rows="expenses.length || 5" />
                </template>

                <!-- Empty state -->
                <template v-else-if="expenses.length === 0">
                    <TableRow>
                        <TableCell colspan="6" class="text-center">
                            <EmptyState
                                label="Empty result"
                                title="No expenses found"
                                description="Try a broader search or add a new expense record."
                            />
                        </TableCell>
                    </TableRow>
                </template>

                <!-- Actual rows -->
                <template v-else>
                    <TableRow
                        v-for="expense in expenses"
                        :key="expense.id"
                        class="border-border/60 transition-colors odd:bg-muted/10 hover:bg-muted/30"
                    >
                        <TableCell class="align-top">
                            <div class="space-y-1">
                                <div class="flex items-start gap-3">
                                    <TableIcon :text="expense.name" />
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-semibold text-foreground"
                                        >
                                            {{ expense.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            #{{ expense.id }}
                                            <span v-if="expense.category">
                                                • {{ expense.category }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell>
                            <span
                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium"
                                :class="
                                    typeClasses[expense.type] ??
                                    'border-border bg-muted text-foreground'
                                "
                            >
                                {{ formatType(expense.type) }}
                            </span>
                        </TableCell>

                        <TableCell class="align-top">
                            <div class="space-y-1 text-sm">
                                <p class="font-medium text-foreground">
                                    {{ formatDate(expense.date_start) }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Until {{ formatDate(expense.date_end) }}
                                </p>
                            </div>
                        </TableCell>

                        <TableCell class="align-top text-sm">
                            <div class="space-y-1">
                                <p
                                    v-if="expense.reference_no"
                                    class="font-medium text-foreground"
                                >
                                    {{ expense.reference_no }}
                                </p>
                                <p v-else class="text-muted-foreground">
                                    No reference
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ expense.category ?? 'Uncategorized' }}
                                </p>
                            </div>
                        </TableCell>

                        <TableCell class="text-right align-top">
                            <div class="space-y-1">
                                <p
                                    class="text-sm font-semibold text-foreground"
                                >
                                    ₱{{ formatAmount(expense.total_amount) }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Paid ₱{{
                                        formatAmount(expense.paid_amount)
                                    }}
                                </p>
                            </div>
                        </TableCell>

                        <TableCell class="text-right align-top">
                            <slot name="actions" :expense="expense" />
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
