<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Eye, EyeOff, Search } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import TableIcon from '@/components/TableIcon.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatAmount } from '@/lib/formatters';
import { index as accountsIndex } from '@/routes/accounts';

import type { Account, Filters, Summary } from '../types/accounts';

const props = defineProps<{
    accounts: Account[];
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
const areBalancesVisible = ref(false);

const applySearch = (value: string): void => {
    isLoading.value = true;
    const normalizedSearch = value.trim();

    router.get(
        accountsIndex.url({
            query: normalizedSearch === '' ? {} : { search: normalizedSearch },
        }),
        {},
        {
            only: ['accounts', 'filters'],
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

const statusClasses = computed<Record<string, string>>(() => ({
    active: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
    inactive: 'border-slate-500/20 bg-slate-500/10 text-slate-700 dark:text-slate-300',
}));

const displayBalance = (
    currency: string,
    value: number | string | null | undefined,
): string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (!areBalancesVisible.value) {
        return `${currency} ••••••`;
    }

    return `${currency} ${formatAmount(value)}`;
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
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="areBalancesVisible = !areBalancesVisible"
                >
                    <component :is="areBalancesVisible ? EyeOff : Eye" class="size-4" />
                    {{ areBalancesVisible ? 'Hide balances' : 'Show balances' }}
                </Button>
                <slot name="buttons"></slot>
            </div>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Account</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Reference</TableHead>
                    <TableHead class="text-right">Balances</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <template v-if="isLoading">
                    <TableSkeleton :rows="props.accounts.length || 5" />
                </template>

                <template v-else-if="props.accounts.length === 0">
                    <TableRow>
                        <TableCell colspan="6" class="text-center">
                            <EmptyState
                                label="Empty result"
                                title="No accounts found"
                                description="Try a broader search or add a new account record."
                            />
                        </TableCell>
                    </TableRow>
                </template>

                <template v-else>
                    <TableRow
                        v-for="account in props.accounts"
                        :key="account.id"
                        class="border-border/60 transition-colors odd:bg-muted/10 hover:bg-muted/30"
                    >
                        <TableCell class="align-top">
                            <div class="flex items-start gap-3">
                                <TableIcon :text="account.account_name" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-foreground">
                                        {{ account.account_name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        #{{ account.id }}
                                        <span v-if="account.is_default"> • Default</span>
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell class="align-top">
                            <span
                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium capitalize"
                                :class="
                                    account.account_type === 'bank'
                                        ? 'border-sky-500/20 bg-sky-500/10 text-sky-700 dark:text-sky-300'
                                        : account.account_type === 'e-wallet'
                                          ? 'border-violet-500/20 bg-violet-500/10 text-violet-700 dark:text-violet-300'
                                          : account.account_type === 'cash'
                                            ? 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300'
                                            : 'border-border bg-muted text-foreground'
                                "
                            >
                                {{ account.account_type }}
                            </span>
                        </TableCell>

                        <TableCell class="align-top text-sm">
                            <div class="space-y-1">
                                <p v-if="account.bank_name" class="font-medium text-foreground">
                                    {{ account.bank_name }}
                                </p>
                                <p v-else class="text-muted-foreground">No bank name</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ account.account_number ?? 'No account number' }}
                                </p>
                            </div>
                        </TableCell>

                        <TableCell class="text-right align-top">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-foreground">
                                    {{ displayBalance(account.currency, account.balance) }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Initial {{ displayBalance(account.currency, account.initial_balance) }}
                                </p>
                            </div>
                        </TableCell>

                        <TableCell class="align-top">
                            <span
                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium"
                                :class="
                                    account.is_active
                                        ? statusClasses.active
                                        : statusClasses.inactive
                                "
                            >
                                {{ account.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </TableCell>

                        <TableCell class="text-right align-top">
                            <slot name="actions" :account="account" />
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
