<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { formatAmount, formatDate } from '@/lib/formatters';
import type { SpendIncome } from './types/spend-income';

const props = defineProps<{
    open: boolean;
    spendIncome: SpendIncome | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const sourceLabel = computed(() => {
    if (props.spendIncome?.entry_type === 'spend') {
        return props.spendIncome.expense?.name ?? 'Others';
    }

    return props.spendIncome?.account?.account_name ?? 'Others';
});

const payrollLabel = computed(() => {
    if (!props.spendIncome?.is_payroll) {
        return 'No';
    }

    if (props.spendIncome.payroll_month === null || props.spendIncome.payroll_year === null) {
        return 'Yes';
    }

    const date = new Date(props.spendIncome.payroll_year, props.spendIncome.payroll_month - 1, 1);

    return `Yes • ${date.toLocaleString('en-PH', { month: 'long', year: 'numeric' })}`;
});

const displayAmount = computed(() => {
    if (props.spendIncome?.amount === null || props.spendIncome?.amount === undefined) {
        return '—';
    }

    return `₱${formatAmount(props.spendIncome.amount)}`;
});

const displayDate = computed(() => {
    return formatDate(props.spendIncome?.transaction_date ?? null);
});
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent class="flex max-h-[90vh] w-full flex-col gap-0 overflow-hidden p-0 sm:max-w-3xl">
            <DialogHeader class="border-b px-6 py-5">
                <DialogTitle class="text-xl font-semibold tracking-tight">
                    Spend / Income Details
                </DialogTitle>

                <DialogDescription class="mt-1 text-sm text-muted-foreground">
                    Review the selected record below.
                </DialogDescription>
            </DialogHeader>

            <div class="flex-1 overflow-y-auto px-6 py-6">
                <div class="space-y-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-lg border bg-muted/30 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                Type
                            </p>
                            <p class="mt-1 text-lg font-semibold capitalize text-foreground">
                                {{ props.spendIncome?.entry_type ?? '—' }}
                            </p>
                        </div>

                        <div class="rounded-lg border bg-muted/30 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                Date
                            </p>
                            <p class="mt-1 text-lg font-semibold text-foreground">
                                {{ displayDate }}
                            </p>
                        </div>

                        <div class="rounded-lg border bg-muted/30 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                Amount
                            </p>
                            <p class="mt-1 text-lg font-semibold text-foreground">
                                {{ displayAmount }}
                            </p>
                        </div>
                    </div>

                    <Separator />

                    <section class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">
                                {{ props.spendIncome?.entry_type === 'spend' ? 'Expense' : 'Account' }}
                            </p>
                            <p class="font-medium text-foreground">
                                {{ sourceLabel }}
                            </p>
                        </div>

                        <div v-if="props.spendIncome?.entry_type === 'income'" class="space-y-1">
                            <p class="text-sm text-muted-foreground">Payroll</p>
                            <p class="font-medium text-foreground">
                                {{ payrollLabel }}
                            </p>
                        </div>
                    </section>

                    <Separator />

                    <section class="space-y-2">
                        <h3 class="text-sm font-semibold text-foreground">Description</h3>
                        <p class="text-sm leading-6 text-foreground">
                            {{ props.spendIncome?.description || 'No description provided.' }}
                        </p>
                    </section>
                </div>
            </div>

            <DialogFooter class="border-t px-6 py-4">
                <DialogClose as-child>
                    <Button type="button" variant="outline">Close</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
