<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { formatAmount, formatPaymentDueDay } from '@/lib/formatters';
import type { CutoffExpense } from '../types/cutoff';

const props = defineProps<{
    open: boolean;
    expense: CutoffExpense | null;
    monthLabel: string;
    actionType: 'payment' | 'penalty';
}>();

const form = defineModel<{
    amount: number | string;
    transaction_date: string;
    description: string;
    processing: boolean;
    errors?: Record<string, string>;
}>('form', {
    required: true,
});

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'close'): void;
    (e: 'submit'): void;
}>();

const handleOpenChange = (value: boolean): void => {
    emit('update:open', value);

    if (!value) {
        emit('close');
    }
};

const dueAmount = computed(() => {
    return props.expense === null
        ? '0.00'
        : formatAmount(props.expense.effective_due_amount);
});

const baseDueAmount = computed(() => {
    return props.expense === null
        ? '0.00'
        : formatAmount(props.expense.total_amount);
});

const paidThisMonth = computed(() => {
    return props.expense === null
        ? '0.00'
        : formatAmount(props.expense.paid_this_month);
});

const currentMonthPaid = computed(() => {
    return props.expense === null
        ? '0.00'
        : formatAmount(props.expense.current_month_paid);
});

const carryoverAmount = computed(() => {
    return props.expense === null
        ? '0.00'
        : formatAmount(props.expense.carryover_amount);
});

const previousBalanceAmount = computed(() => {
    return props.expense === null
        ? '0.00'
        : formatAmount(props.expense.previous_balance_amount);
});

const remainingAmount = computed(() => {
    return props.expense === null
        ? '0.00'
        : formatAmount(props.expense.remaining_amount);
});

const dialogTitle = computed(() => {
    return props.actionType === 'penalty' ? 'Add penalty' : 'Record payment';
});

const dialogDescription = computed(() => {
    return props.actionType === 'penalty'
        ? `Add a penalty amount for this expense in ${props.monthLabel}. This affects only the selected month and will not turn into next month's carry-over credit.`
        : `Add a spend entry for this expense in ${props.monthLabel}. The record will be linked to the expense automatically.`;
});

const amountLabel = computed(() => {
    return props.actionType === 'penalty' ? 'Penalty Amount' : 'Amount';
});
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent
            class="flex max-h-[90vh] w-full flex-col gap-0 overflow-hidden p-0 sm:max-w-2xl"
        >
            <DialogHeader class="border-b px-6 py-5">
                <DialogTitle class="text-xl font-semibold tracking-tight">
                    {{ dialogTitle }}
                </DialogTitle>

                <DialogDescription class="mt-1 text-sm text-muted-foreground">
                    {{ dialogDescription }}
                </DialogDescription>
            </DialogHeader>

            <form
                @submit.prevent="emit('submit')"
                class="flex flex-1 flex-col overflow-hidden"
            >
                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">
                    <div
                        v-if="props.expense !== null"
                        class="grid gap-4 rounded-2xl border bg-card p-4 md:grid-cols-[minmax(0,1fr)_220px]"
                    >
                        <div class="space-y-3">
                            <div>
                                <p
                                    class="text-sm font-semibold text-foreground"
                                >
                                    {{ props.expense.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        props.expense.category ??
                                        props.expense.type
                                    }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <Badge variant="outline">
                                    {{
                                        formatPaymentDueDay(
                                            props.expense.payment_due,
                                        )
                                    }}
                                </Badge>

                                <Badge variant="secondary">
                                    {{ props.monthLabel }}
                                </Badge>
                            </div>
                        </div>

                        <div class="rounded-xl border bg-muted/30 p-4">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                {{
                                    props.actionType === 'penalty'
                                        ? 'Current amount due'
                                        : 'Remaining to pay'
                                }}
                            </p>
                            <p
                                class="mt-2 text-2xl font-semibold text-foreground"
                            >
                                ₱{{
                                    props.actionType === 'penalty'
                                        ? baseDueAmount
                                        : remainingAmount
                                }}
                            </p>
                            <p class="mt-2 text-sm text-muted-foreground">
                                <template v-if="props.actionType === 'penalty'">
                                    Penalties are tracked for this month only
                                    and do not become carry-over credit next
                                    month.
                                </template>
                                <template v-else>
                                    Current due after this month's payments,
                                    prior unpaid balance, and any carry-over
                                    credit from the previous month.
                                </template>
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="rounded-xl border bg-muted/20 p-4">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                {{
                                    props.actionType === 'penalty'
                                        ? 'Base Due'
                                        : 'Due This Month'
                                }}
                            </p>
                            <p
                                class="mt-2 text-base font-semibold text-foreground"
                            >
                                ₱{{
                                    props.actionType === 'penalty'
                                        ? baseDueAmount
                                        : dueAmount
                                }}
                            </p>
                        </div>

                        <div class="rounded-xl border bg-muted/20 p-4">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Paid This Month
                            </p>
                            <p
                                class="mt-2 text-base font-semibold text-foreground"
                            >
                                ₱{{ paidThisMonth }}
                            </p>
                        </div>

                        <div
                            v-if="
                                props.expense !== null &&
                                Number(props.expense.penalty_amount) > 0
                            "
                            class="rounded-xl border bg-muted/20 p-4"
                        >
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Penalty This Month
                            </p>
                            <p
                                class="mt-2 text-base font-semibold text-foreground"
                            >
                                ₱{{
                                    formatAmount(props.expense.penalty_amount)
                                }}
                            </p>
                        </div>

                        <div class="rounded-xl border bg-muted/20 p-4">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Previous Balance
                            </p>
                            <p
                                class="mt-2 text-base font-semibold text-foreground"
                            >
                                ₱{{ previousBalanceAmount }}
                            </p>
                        </div>

                        <div class="rounded-xl border bg-muted/20 p-4">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Current Entries
                            </p>
                            <p
                                class="mt-2 text-base font-semibold text-foreground"
                            >
                                ₱{{ currentMonthPaid }}
                            </p>
                        </div>

                        <div class="rounded-xl border bg-muted/20 p-4">
                            <p
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Carry-over
                            </p>
                            <p
                                class="mt-2 text-base font-semibold text-foreground"
                            >
                                ₱{{ carryoverAmount }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="cutoff-payment-amount">
                                {{ amountLabel }}
                                <span class="ml-1 text-destructive">*</span>
                            </Label>
                            <Input
                                id="cutoff-payment-amount"
                                v-model="form.amount"
                                type="number"
                                min="0"
                                step="0.0001"
                            />
                            <p
                                v-if="form.errors?.amount"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.amount }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="cutoff-payment-date">
                                Transaction Date
                                <span class="ml-1 text-destructive">*</span>
                            </Label>
                            <Input
                                id="cutoff-payment-date"
                                v-model="form.transaction_date"
                                type="date"
                            />
                            <p
                                v-if="form.errors?.transaction_date"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.transaction_date }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="cutoff-payment-description">Note</Label>
                        <Textarea
                            id="cutoff-payment-description"
                            v-model="form.description"
                            rows="3"
                            placeholder="Optional note for this payment"
                        />
                        <p
                            v-if="form.errors?.description"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>

                <DialogFooter class="border-t px-6 py-4 sm:justify-between">
                    <DialogClose as-child>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="form.processing"
                        >
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="min-w-40"
                    >
                        {{
                            form.processing
                                ? 'Saving...'
                                : props.actionType === 'penalty'
                                  ? 'Save Penalty'
                                  : 'Save Payment'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
