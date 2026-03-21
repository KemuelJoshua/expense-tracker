<script setup lang="ts">
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
import type { AccountFormData } from '../types/accounts';

const form = defineModel<AccountFormData & {
    errors?: Record<string, string>;
}>('form', {
    required: true,
});
</script>

<template>
    <div class="mx-auto w-full space-y-6">
        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Account Details</h3>
                <p class="text-sm text-muted-foreground">
                    Basic information about the account.
                </p>
            </div>

            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="account_name">
                        Account Name
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Input
                        id="account_name"
                        v-model="form.account_name"
                        placeholder="e.g. BDO Payroll"
                    />
                    <p v-if="form.errors?.account_name" class="text-sm text-red-500">
                        {{ form.errors.account_name }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label>
                            Account Type
                            <span class="ml-1 text-destructive">*</span>
                        </Label>
                        <Select v-model="form.account_type">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select account type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="bank">Bank</SelectItem>
                                <SelectItem value="e-wallet">E-Wallet</SelectItem>
                                <SelectItem value="cash">Cash</SelectItem>
                                <SelectItem value="credit">Credit</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors?.account_type" class="text-sm text-red-500">
                            {{ form.errors.account_type }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="currency">
                            Currency
                            <span class="ml-1 text-destructive">*</span>
                        </Label>
                        <Input
                            id="currency"
                            v-model="form.currency"
                            maxlength="3"
                            placeholder="PHP"
                        />
                        <p v-if="form.errors?.currency" class="text-sm text-red-500">
                            {{ form.errors.currency }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="bank_name">Bank Name</Label>
                        <Input
                            id="bank_name"
                            v-model="form.bank_name"
                            placeholder="e.g. BPI"
                        />
                        <p v-if="form.errors?.bank_name" class="text-sm text-red-500">
                            {{ form.errors.bank_name }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="account_number">Account Number</Label>
                        <Input
                            id="account_number"
                            v-model="form.account_number"
                            placeholder="e.g. 1234-5678-9012"
                        />
                        <p v-if="form.errors?.account_number" class="text-sm text-red-500">
                            {{ form.errors.account_number }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Balances</h3>
                <p class="text-sm text-muted-foreground">
                    Initial and current amounts for the account.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="initial_balance">
                        Initial Balance
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Input
                        id="initial_balance"
                        v-model="form.initial_balance"
                        type="number"
                        step="0.0001"
                        min="0"
                    />
                    <p v-if="form.errors?.initial_balance" class="text-sm text-red-500">
                        {{ form.errors.initial_balance }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="balance">
                        Current Balance
                        <span class="ml-1 text-destructive">*</span>
                    </Label>
                    <Input
                        id="balance"
                        v-model="form.balance"
                        type="number"
                        step="0.0001"
                        min="0"
                    />
                    <p v-if="form.errors?.balance" class="text-sm text-red-500">
                        {{ form.errors.balance }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 border-b pb-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Status</h3>
                <p class="text-sm text-muted-foreground">
                    Control whether the account is active and default.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label>Status</Label>
                    <Select v-model="form.is_active">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="1">Active</SelectItem>
                            <SelectItem :value="0">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors?.is_active" class="text-sm text-red-500">
                        {{ form.errors.is_active }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label>Default Account</Label>
                    <Select v-model="form.is_default">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Is this the default account?" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="0">No</SelectItem>
                            <SelectItem :value="1">Yes</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors?.is_default" class="text-sm text-red-500">
                        {{ form.errors.is_default }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-[180px_minmax(0,1fr)]">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Additional Details</h3>
                <p class="text-sm text-muted-foreground">
                    Optional notes for future reference.
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Optional notes about this account"
                    rows="5"
                />
                <p v-if="form.errors?.description" class="text-sm text-red-500">
                    {{ form.errors.description }}
                </p>
            </div>
        </div>
    </div>
</template>
