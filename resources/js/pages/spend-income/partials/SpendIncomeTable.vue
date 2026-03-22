<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { onBeforeUnmount, ref, watch } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import TableIcon from '@/components/TableIcon.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatAmount, formatDate } from '@/lib/formatters';
import { index as spendIncomeIndex } from '@/routes/spend-income';
import type { Filters, SpendIncome } from '../types/spend-income';

const props = defineProps<{
    spendIncomes: SpendIncome[];
    filters: Filters;
}>();

const search = ref(props.filters.search ?? '');
const isLoading = ref(false);
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

const applySearch = (value: string): void => {
    isLoading.value = true;
    const normalizedSearch = value.trim();

    router.get(
        spendIncomeIndex.url({
            query: normalizedSearch === '' ? {} : { search: normalizedSearch },
        }),
        {},
        {
            only: ['spendIncomes', 'filters'],
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

const sourceLabel = (entry: SpendIncome): string => {
    if (entry.entry_type === 'spend') {
        return entry.expense?.name ?? 'Others';
    }

    return entry.account?.account_name ?? 'Others';
};

const detailLabel = (entry: SpendIncome): string => {
    if (entry.entry_type === 'income' && entry.is_payroll && entry.payroll_month && entry.payroll_year) {
        return new Date(entry.payroll_year, entry.payroll_month - 1, 1).toLocaleString('en-PH', {
            month: 'long',
            year: 'numeric',
        });
    }

    return entry.description || 'No description';
};
</script>

<template>
    <div class="overflow-hidden">
        <div class="flex items-center justify-between gap-4 border-b py-4">
            <div class="relative w-full max-w-sm">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="search"
                    type="search"
                    placeholder="Search..."
                    class="h-8 pl-9"
                    @input="scheduleSearch"
                />
            </div>

            <div class="flex items-center gap-2">
                <slot name="buttons" />
            </div>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Record</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead>Source</TableHead>
                    <TableHead>Details</TableHead>
                    <TableHead class="text-right">Amount</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <template v-if="isLoading">
                    <TableSkeleton :rows="props.spendIncomes.length || 5" />
                </template>

                <template v-else-if="props.spendIncomes.length === 0">
                    <TableRow>
                        <TableCell colspan="6" class="text-center">
                            <EmptyState
                                label="Empty result"
                                title="No entries found"
                                description="Try a broader search or add a new spend / income record."
                            />
                        </TableCell>
                    </TableRow>
                </template>

                <template v-else>
                    <TableRow
                        v-for="entry in props.spendIncomes"
                        :key="entry.id"
                        class="border-border/60 transition-colors odd:bg-muted/10 hover:bg-muted/30"
                    >
                        <TableCell class="align-top">
                            <div class="flex items-start gap-3">
                                <TableIcon :text="entry.entry_type" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold capitalize text-foreground">
                                        {{ entry.entry_type }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        #{{ entry.id }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell class="align-top text-sm">
                            {{ formatDate(entry.transaction_date) }}
                        </TableCell>

                        <TableCell class="align-top text-sm">
                            <div class="space-y-1">
                                <p class="font-medium text-foreground">
                                    {{ sourceLabel(entry) }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        entry.entry_type === 'spend'
                                            ? entry.expense?.type ?? 'No expense relation'
                                            : entry.account?.account_type ?? 'No account relation'
                                    }}
                                </p>
                            </div>
                        </TableCell>

                        <TableCell class="align-top text-sm text-muted-foreground">
                            {{ detailLabel(entry) }}
                        </TableCell>

                        <TableCell class="align-top text-right">
                            <span class="text-sm font-semibold text-foreground">
                                ₱{{ formatAmount(entry.amount) }}
                            </span>
                        </TableCell>

                        <TableCell class="align-top text-right">
                            <slot name="actions" :spendIncome="entry" />
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
