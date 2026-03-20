<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import type { Expense } from './types/expense';

const props = defineProps<{
    open: boolean;
    expense: Expense | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'close'): void;
}>();

const handleOpenChange = (value: boolean) => {
    emit('update:open', value);

    if (!value) {
        emit('close');
    }
};

const displayValue = (
    value: number | string | null | undefined,
): number | string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return value;
};

const formatCurrency = (
    value: number | string | null | undefined,
): number | string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    const number = Number(value);

    if (Number.isNaN(number)) {
        return value;
    }

    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(number);
};

const recurringLabel = computed(() => {
    return props.expense?.is_recurring ? 'Yes' : 'No';
});
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent
            class="flex max-h-[90vh] w-full flex-col gap-0 overflow-hidden p-0 sm:max-w-5xl"
        >
            <DialogHeader class="border-b px-6 py-5">
                <DialogTitle class="text-xl font-semibold tracking-tight">
                    Expense Details
                </DialogTitle>

                <DialogDescription class="mt-1 text-sm text-muted-foreground">
                    Review the complete expense information below.
                </DialogDescription>
            </DialogHeader>

            <div class="flex-1 overflow-y-auto">
                <div class="mx-auto w-full max-w-4xl px-6 py-6">
                    <div class="space-y-8">
                        <!-- Summary -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="rounded-lg border bg-muted/30 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                    Total Amount
                                </p>
                                <p class="mt-1 text-lg font-semibold text-foreground">
                                    {{ formatCurrency(props.expense?.total_amount) }}
                                </p>
                            </div>

                            <div class="rounded-lg border bg-muted/30 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                    Paid Amount
                                </p>
                                <p class="mt-1 text-lg font-semibold text-foreground">
                                    {{ formatCurrency(props.expense?.paid_amount) }}
                                </p>
                            </div>

                            <div class="rounded-lg border bg-muted/30 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                    Recurring
                                </p>
                                <p class="mt-1 text-lg font-semibold text-foreground">
                                    {{ recurringLabel }}
                                </p>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <section class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">
                                    Basic Information
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Main details of this expense.
                                </p>
                            </div>

                            <div
                                class="grid gap-6 md:grid-cols-[180px_minmax(0,1fr)]"
                            >
                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Name</p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.expense?.name) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Type</p>
                                    <p class="font-medium capitalize text-foreground">
                                        {{ displayValue(props.expense?.type) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Category</p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.expense?.category) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">
                                        Reference No.
                                    </p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.expense?.reference_no) }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <Separator />

                        <!-- Schedule -->
                        <section class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">
                                    Schedule
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Timeline and payment schedule.
                                </p>
                            </div>

                            <div
                                class="grid gap-6 md:grid-cols-[180px_minmax(0,1fr)]"
                            >
                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Date Start</p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.expense?.date_start) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Date End</p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.expense?.date_end) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Payment Due</p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.expense?.payment_due) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Pay In</p>
                                    <p class="font-medium capitalize text-foreground">
                                        {{ displayValue(props.expense?.pay_in) }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <Separator />

                        <!-- Recurring -->
                        <section class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">
                                    Recurring Setup
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Repeat settings for this expense.
                                </p>
                            </div>

                            <div
                                class="grid gap-6 md:grid-cols-[180px_minmax(0,1fr)]"
                            >
                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Recurring</p>
                                    <p class="font-medium text-foreground">
                                        {{ recurringLabel }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">
                                        Recurring Cycle
                                    </p>
                                    <p class="font-medium capitalize text-foreground">
                                        {{
                                            props.expense?.is_recurring
                                                ? displayValue(props.expense?.recurring_cycle)
                                                : '—'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <Separator />

                        <!-- Additional Details -->
                        <section class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">
                                    Additional Details
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Notes and supporting file.
                                </p>
                            </div>

                            <div class="space-y-6">
                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Attachment</p>
                                    <p class="font-medium text-foreground break-all">
                                        {{ displayValue(props.expense?.attachment) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Description</p>
                                    <p
                                        class="whitespace-pre-line leading-6 text-foreground"
                                    >
                                        {{ displayValue(props.expense?.description) }}
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <DialogFooter class="border-t px-6 py-4 sm:justify-end">
                <DialogClose as-child>
                    <Button type="button" variant="outline">
                        Close
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>