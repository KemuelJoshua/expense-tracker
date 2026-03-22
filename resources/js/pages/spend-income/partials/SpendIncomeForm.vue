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
import type {
    SpendIncomeFormData,
    SpendIncomeOptions,
} from '../types/spend-income';

const props = defineProps<{
    options: SpendIncomeOptions;
    typeDisabled?: boolean;
}>();

const form = defineModel<SpendIncomeFormData & {
    errors?: Record<string, string>;
}>('form', {
    required: true,
});

const isSpend = computed(() => form.value.entry_type === 'spend');
const isIncome = computed(() => form.value.entry_type === 'income');
const showsPayrollFields = computed(() => isIncome.value && Number(form.value.is_payroll) === 1);

const monthOptions = [
    { value: '1', label: 'January' },
    { value: '2', label: 'February' },
    { value: '3', label: 'March' },
    { value: '4', label: 'April' },
    { value: '5', label: 'May' },
    { value: '6', label: 'June' },
    { value: '7', label: 'July' },
    { value: '8', label: 'August' },
    { value: '9', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' },
];

watch(
    () => form.value.entry_type,
    (entryType) => {
        if (entryType === 'spend') {
            form.value.account_reference = 'others';
            form.value.is_payroll = '0';
            form.value.payroll_month = '';
            form.value.payroll_year = '';

            return;
        }

        if (entryType === 'income') {
            form.value.expense_reference = 'others';

            return;
        }

        form.value.expense_reference = 'others';
        form.value.account_reference = 'others';
        form.value.is_payroll = '0';
        form.value.payroll_month = '';
        form.value.payroll_year = '';
    },
);

watch(
    () => form.value.is_payroll,
    (isPayroll) => {
        if (Number(isPayroll) === 0) {
            form.value.payroll_month = '';
            form.value.payroll_year = '';
        }
    },
);
</script>

<template>
    <div class="mx-auto w-full space-y-6">
        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Entry Type</h3>
                <p class="text-sm text-muted-foreground">
                    Choose whether this record is a spend or an income.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label>
                        Record Type
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Select v-model="form.entry_type">
                        <SelectTrigger class="w-full" :disabled="props.typeDisabled">
                            <SelectValue placeholder="Choose spend or income" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="spend">Spend</SelectItem>
                            <SelectItem value="income">Income</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors?.entry_type" class="text-sm text-red-500">
                        {{ form.errors.entry_type }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="transaction_date">
                        {{ isIncome ? 'Date Receive' : 'Date Spend' }}
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Input id="transaction_date" v-model="form.transaction_date" type="date" />
                    <p v-if="form.errors?.transaction_date" class="text-sm text-red-500">
                        {{ form.errors.transaction_date }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Details</h3>
                <p class="text-sm text-muted-foreground">
                    Attach this record to an expense or account when needed.
                </p>
            </div>

            <div class="grid gap-4">
                <div v-if="isSpend" class="grid gap-2">
                    <Label>
                        Expense
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Select v-model="form.expense_reference">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select an expense or Others" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="others">Others</SelectItem>
                            <SelectItem
                                v-for="expense in props.options.expenses"
                                :key="expense.id"
                                :value="String(expense.id)"
                            >
                                {{ expense.name }} ({{ expense.type }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors?.expense_reference" class="text-sm text-red-500">
                        {{ form.errors.expense_reference }}
                    </p>
                </div>

                <template v-if="isIncome">
                    <div class="grid gap-2 sm:max-w-sm">
                        <Label>
                            From Payroll?
                            <span class="ml-1 text-destructive">*</span>
                        </Label>
                        <Select v-model="form.is_payroll">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Choose yes or no" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="1">Yes</SelectItem>
                                <SelectItem value="0">No</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors?.is_payroll" class="text-sm text-red-500">
                            {{ form.errors.is_payroll }}
                        </p>
                    </div>

                    <div v-if="showsPayrollFields" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label>
                                Payroll Month
                                <span class="ml-1 text-destructive">*</span>
                            </Label>
                            <Select v-model="form.payroll_month">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select month" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="month in monthOptions"
                                        :key="month.value"
                                        :value="month.value"
                                    >
                                        {{ month.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors?.payroll_month" class="text-sm text-red-500">
                                {{ form.errors.payroll_month }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="payroll_year">
                                Payroll Year
                                <span class="ml-1 text-destructive">*</span>
                            </Label>
                            <Input
                                id="payroll_year"
                                v-model="form.payroll_year"
                                type="number"
                                min="2000"
                                max="2100"
                            />
                            <p v-if="form.errors?.payroll_year" class="text-sm text-red-500">
                                {{ form.errors.payroll_year }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label>
                            Account
                            <span class="ml-1 text-destructive">*</span>
                        </Label>
                        <Select v-model="form.account_reference">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select an account or Others" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="others">Others</SelectItem>
                                <SelectItem
                                    v-for="account in props.options.accounts"
                                    :key="account.id"
                                    :value="String(account.id)"
                                >
                                    {{ account.account_name }} ({{ account.account_type }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors?.account_reference" class="text-sm text-red-500">
                            {{ form.errors.account_reference }}
                        </p>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Amount</h3>
                <p class="text-sm text-muted-foreground">
                    The amount recorded for this spend or income.
                </p>
            </div>

            <div class="grid gap-4 sm:max-w-sm">
                <div class="grid gap-2">
                    <Label for="amount">
                        Amount
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Input id="amount" v-model="form.amount" type="number" step="0.0001" min="0" />
                    <p v-if="form.errors?.amount" class="text-sm text-red-500">
                        {{ form.errors.amount }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Description</h3>
                <p class="text-sm text-muted-foreground">
                    Optional notes for the transaction.
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Add extra context if needed"
                    rows="4"
                />
                <p v-if="form.errors?.description" class="text-sm text-red-500">
                    {{ form.errors.description }}
                </p>
            </div>
        </div>
    </div>
</template>
