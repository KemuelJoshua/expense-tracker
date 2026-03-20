<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Search, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
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

const formatAmount = (amount: number | string): string => {
  return Number(amount).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const formatType = (type: string): string => {
  return type.charAt(0).toUpperCase() + type.slice(1);
};

const formatDate = (date: string | null): string => {
  if (!date) {
    return 'Open-ended';
  }

  return new Intl.DateTimeFormat('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(date));
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

const clearSearch = (): void => {
  if (searchTimeout !== null) {
    clearTimeout(searchTimeout);
  }

  if (search.value === '' && props.filters.search === '') {
    return;
  }

  search.value = '';
  applySearch('');
};
</script>

<template>
  <div class="overflow-hidden">
    <div class="flex items-center justify-between gap-4 border-b py-4">
      <!-- Search -->
      <div class="relative w-full max-w-sm">
        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

        <Input v-model="search" type="search" placeholder="Search..." class="h-10 pr-8 pl-9" @input="scheduleSearch" />

        <Button v-if="search !== ''" type="button" variant="ghost" size="icon"
          class="absolute top-1/2 right-1 h-7 w-7 -translate-y-1/2" @click="clearSearch">
          <X class="h-4 w-4" />
        </Button>
      </div>

      <!-- Buttons -->
      <div class="flex items-center gap-2">
        <slot name="buttons"></slot>
      </div>
    </div>

    <Table>
      <TableHeader>
        <TableRow class="bg-muted/30 hover:bg-muted/30">
          <TableHead class="px-6 text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
            Expense
          </TableHead>
          <TableHead class="px-6 text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
            Type
          </TableHead>
          <TableHead class="px-6 text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
            Schedule
          </TableHead>
          <TableHead class="px-6 text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
            Reference
          </TableHead>
          <TableHead class="px-6 text-right text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
            Amounts
          </TableHead>
          <TableHead class="px-6 text-right text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase">
            Actions
          </TableHead>
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
              <EmptyState label="Empty result" title="No expenses found"
                description="Try a broader search or add a new expense record." />
            </TableCell>
          </TableRow>
        </template>

        <!-- Actual rows -->
        <template v-else>
          <TableRow v-for="expense in expenses" :key="expense.id"
            class="border-border/60 transition-colors odd:bg-muted/10 hover:bg-muted/30">
            <TableCell class="px-6 align-top">
              <div class="space-y-1">
                <div class="flex items-start gap-3">
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-sm font-semibold text-primary">
                    {{
                      expense.name.charAt(0).toUpperCase()
                    }}
                  </div>
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-foreground">
                      {{ expense.name }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                      #{{ expense.id }}
                      <span v-if="expense.category">
                        • {{ expense.category }}
                      </span>
                    </p>
                  </div>
                </div>
              </div>
            </TableCell>

            <TableCell class="px-6 align-top">
              <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium" :class="typeClasses[expense.type] ??
                'border-border bg-muted text-foreground'
                ">
                {{ formatType(expense.type) }}
              </span>
            </TableCell>

            <TableCell class="px-6 align-top">
              <div class="space-y-1 text-sm">
                <p class="font-medium text-foreground">
                  {{ formatDate(expense.date_start) }}
                </p>
                <p class="text-xs text-muted-foreground">
                  Until {{ formatDate(expense.date_end) }}
                </p>
              </div>
            </TableCell>

            <TableCell class="px-6 align-top text-sm">
              <div class="space-y-1">
                <p v-if="expense.reference_no" class="font-medium text-foreground">
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

            <TableCell class="px-6 text-right align-top">
              <div class="space-y-1">
                <p class="text-sm font-semibold text-foreground">
                  ₱{{ formatAmount(expense.total_amount) }}
                </p>
                <p class="text-xs text-muted-foreground">
                  Paid ₱{{
                    formatAmount(expense.paid_amount)
                  }}
                </p>
              </div>
            </TableCell>

            <TableCell class="px-6 text-right align-top">
              <slot name="actions" :expense="expense" />
            </TableCell>
          </TableRow>
        </template>
      </TableBody>
    </Table>
  </div>
</template>
