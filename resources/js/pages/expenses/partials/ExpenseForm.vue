<script setup lang="ts">
import { computed, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { ExpenseFormData } from '../types/expense';

const form = defineModel<ExpenseFormData & {
    errors?: Record<string, string>;
}>('form', {
    required: true,
});

const isLoan = computed(() => form.value.type === 'loan');
const isUtilities = computed(() => form.value.type === 'utilities');
const requiresPaymentMode = computed(() => isLoan.value || isUtilities.value);
const showsRecurringSection = computed(() => !isLoan.value && !isUtilities.value);
const showsDateEnd = computed(() => {
    return !isUtilities.value;
});
const amountLabel = computed(() => {
    if (isUtilities.value && form.value.payment_mode === 'variable_amount') {
        return 'Current / Estimated Amount';
    }

    if (isLoan.value && form.value.payment_mode === 'one_time') {
        return 'One-Time Payment Amount';
    }

    return 'Total Amount';
});
const paymentModeLabel = computed(() => {
    return isLoan.value ? 'Loan Payment Setup' : 'Utility Billing Setup';
});
const paymentDueOptions = Array.from({ length: 31 }, (_, index) => {
    const day = index + 1;

    if (day === 1) {
        return {
            value: '1',
            label: '1 - First day of the month',
        };
    }

    if (day === 31) {
        return {
            value: '31',
            label: '31 - End of month',
        };
    }

    return {
        value: String(day),
        label: String(day),
    };
});

watch(
    () => form.value.type,
    (type) => {
        if (type === 'loan') {
            form.value.payment_mode = '';
            form.value.is_recurring = 0;
            form.value.recurring_cycle = '';

            return;
        }

        if (type === 'utilities') {
            form.value.payment_mode = '';
            form.value.is_recurring = 1;
            form.value.recurring_cycle = 'monthly';
            form.value.date_end = '';

            return;
        }

        form.value.payment_mode = '';
    },
);

watch(
    () => form.value.payment_mode,
    (paymentMode) => {
        if (form.value.type === 'loan') {
            if (paymentMode === 'installment') {
                form.value.is_recurring = 1;
                form.value.recurring_cycle = 'monthly';

                return;
            }

            form.value.is_recurring = 0;
            form.value.recurring_cycle = '';

            return;
        }

        if (form.value.type === 'utilities') {
            form.value.is_recurring = 1;
            form.value.recurring_cycle = 'monthly';
            form.value.date_end = '';
        }
    },
);

watch(
    () => form.value.is_recurring,
    (isRecurring) => {
        if (showsRecurringSection.value && Number(isRecurring) === 0) {
            form.value.recurring_cycle = '';
        }
    },
);
</script>

<template>
    <div class="mx-auto w-full space-y-6">
        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    Expense Details
                </h3>
                <p class="text-sm text-muted-foreground">
                    Basic information about the expense.
                </p>
            </div>

            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="name">
                        Expense Name
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="e.g. Office Internet"
                    />
                    <p v-if="form.errors?.name" class="text-sm text-red-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label>
                            Type
                            <span class="ml-1 text-destructive">*</span>
                        </Label>
                        <Select v-model="form.type">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="loan">Loan</SelectItem>
                                <SelectItem value="subscription">Subscription</SelectItem>
                                <SelectItem value="utilities">Utilities</SelectItem>
                                <SelectItem value="others">Others</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors?.type" class="text-sm text-red-500">
                            {{ form.errors.type }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="category">Category</Label>
                        <Input
                            id="category"
                            v-model="form.category"
                            placeholder="e.g. Internet, Rent, Software"
                        />
                        <p v-if="form.errors?.category" class="text-sm text-red-500">
                            {{ form.errors.category }}
                        </p>
                    </div>
                </div>

                <div v-if="requiresPaymentMode" class="grid gap-2">
                    <Label>
                        {{ paymentModeLabel }}
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Select v-model="form.payment_mode">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select payment setup" />
                        </SelectTrigger>
                        <SelectContent v-if="isLoan">
                            <SelectItem value="one_time">One-time payment</SelectItem>
                            <SelectItem value="installment">Installment / term-based</SelectItem>
                        </SelectContent>
                        <SelectContent v-else>
                            <SelectItem value="fixed_monthly">Fixed amount monthly</SelectItem>
                            <SelectItem value="variable_amount">No fixed amount</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors?.payment_mode" class="text-sm text-red-500">
                        {{ form.errors.payment_mode }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="reference_no">Reference No</Label>
                    <Input
                        id="reference_no"
                        v-model="form.reference_no"
                        placeholder="Invoice / Loan ID"
                    />
                    <p v-if="form.errors?.reference_no" class="text-sm text-red-500">
                        {{ form.errors.reference_no }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    Amount
                </h3>
                <p class="text-sm text-muted-foreground">
                    Payment and total values.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="total_amount">
                        {{ amountLabel }}
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Input
                        id="total_amount"
                        v-model="form.total_amount"
                        type="number"
                        step="0.0001"
                        min="0"
                    />
                    <p v-if="form.errors?.total_amount" class="text-sm text-red-500">
                        {{ form.errors.total_amount }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="paid_amount">Paid Amount</Label>
                    <Input
                        id="paid_amount"
                        v-model="form.paid_amount"
                        type="number"
                        step="0.0001"
                        min="0"
                    />
                    <p v-if="form.errors?.paid_amount" class="text-sm text-red-500">
                        {{ form.errors.paid_amount }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    Schedule
                </h3>
                <p class="text-sm text-muted-foreground">
                    Dates and payout schedule.
                </p>
            </div>

            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="date_start">
                            Start Date
                            <span class="ml-1 text-destructive">*</span>
                        </Label>
                        <Input id="date_start" v-model="form.date_start" type="date" />
                        <p v-if="form.errors?.date_start" class="text-sm text-red-500">
                            {{ form.errors.date_start }}
                        </p>
                    </div>

                    <div v-if="showsDateEnd" class="grid gap-2">
                        <Label for="date_end">
                            {{
                                isLoan
                                    ? 'End Date (required for loan)'
                                    : 'End Date (leave blank for monthly/recurring)'
                            }}
                        </Label>
                        <Input id="date_end" v-model="form.date_end" type="date" />
                        <p v-if="form.errors?.date_end" class="text-sm text-red-500">
                            {{ form.errors.date_end }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label>Payment Due Day</Label>
                        <Select v-model="form.payment_due">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select due day" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in paymentDueOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors?.payment_due" class="text-sm text-red-500">
                            {{ form.errors.payment_due }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label>
                            Pay in
                            <span class="ml-1 text-destructive">*</span>
                        </Label>
                        <Select v-model="form.pay_in">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select payout schedule" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="'first'">First</SelectItem>
                                <SelectItem :value="'second'">Second</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors?.pay_in" class="text-sm text-red-500">
                            {{ form.errors.pay_in }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showsRecurringSection"
            class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]"
        >
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    Recurring
                </h3>
                <p class="text-sm text-muted-foreground">
                    Set repeat options for this expense.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label>Recurring</Label>
                    <Select v-model="form.is_recurring">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Recurring?" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="0">No</SelectItem>
                            <SelectItem :value="1">Yes</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors?.is_recurring" class="text-sm text-red-500">
                        {{ form.errors.is_recurring }}
                    </p>
                </div>

                <div v-if="Number(form.is_recurring) === 1" class="grid gap-2">
                    <Label>Recurring Cycle</Label>
                    <Select v-model="form.recurring_cycle">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Cycle" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="monthly">Monthly</SelectItem>
                            <SelectItem value="yearly">Yearly</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors?.recurring_cycle" class="text-sm text-red-500">
                        {{ form.errors.recurring_cycle }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    Additional Details
                </h3>
                <p class="text-sm text-muted-foreground">
                    Notes and supporting file.
                </p>
            </div>

            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Add notes about this expense"
                    />
                    <p v-if="form.errors?.description" class="text-sm text-red-500">
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="attachment">Attachment</Label>
                    <Input
                        id="attachment"
                        type="file"
                        @input="form.attachment = ($event.target as HTMLInputElement).files?.[0] ?? null"
                    />
                    <p v-if="form.errors?.attachment" class="text-sm text-red-500">
                        {{ form.errors.attachment }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
