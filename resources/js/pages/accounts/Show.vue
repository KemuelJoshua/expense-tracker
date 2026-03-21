<script setup lang="ts">
import { Eye, EyeOff } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
import { formatAmount } from '@/lib/formatters';
import type { Account } from './types/accounts';

const props = defineProps<{
    open: boolean;
    account: Account | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'close'): void;
}>();

const areBalancesVisible = ref(false);

const handleOpenChange = (value: boolean): void => {
    emit('update:open', value);

    if (!value) {
        areBalancesVisible.value = false;
        emit('close');
    }
};

const displayValue = (value: number | string | null | undefined): number | string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return value;
};

const formatCurrency = (value: number | string | null | undefined): number | string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return `${props.account?.currency ?? 'PHP'} ${formatAmount(value)}`;
};

const statusLabel = computed(() => {
    return props.account?.is_active ? 'Active' : 'Inactive';
});

const defaultLabel = computed(() => {
    return props.account?.is_default ? 'Yes' : 'No';
});

const displayBalance = (value: number | string | null | undefined): number | string => {
    if (areBalancesVisible.value) {
        return formatCurrency(value);
    }

    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return `${props.account?.currency ?? 'PHP'} ••••••`;
};
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent
            class="flex max-h-[90vh] w-full flex-col gap-0 overflow-hidden p-0 sm:max-w-4xl"
        >
            <DialogHeader class="border-b px-6 py-5">
                <DialogTitle class="text-xl font-semibold tracking-tight">
                    Account Details
                </DialogTitle>

                <DialogDescription class="mt-1 text-sm text-muted-foreground">
                    Review the complete account information below.
                </DialogDescription>
            </DialogHeader>

            <div class="flex-1 overflow-y-auto">
                <div class="mx-auto w-full max-w-4xl px-6 py-6">
                    <div class="space-y-8">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="rounded-lg border bg-muted/30 px-4 py-3 sm:col-span-3">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                            Balance Privacy
                                        </p>
                                        <p class="mt-1 text-sm text-muted-foreground">
                                            Hide or reveal sensitive account balances.
                                        </p>
                                    </div>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="areBalancesVisible = !areBalancesVisible"
                                    >
                                        <component
                                            :is="areBalancesVisible ? EyeOff : Eye"
                                            class="size-4"
                                        />
                                        {{ areBalancesVisible ? 'Hide balances' : 'Show balances' }}
                                    </Button>
                                </div>
                            </div>

                            <div class="rounded-lg border bg-muted/30 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                    Current Balance
                                </p>
                                <p class="mt-1 text-lg font-semibold text-foreground">
                                    {{ displayBalance(props.account?.balance) }}
                                </p>
                            </div>

                            <div class="rounded-lg border bg-muted/30 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                    Initial Balance
                                </p>
                                <p class="mt-1 text-lg font-semibold text-foreground">
                                    {{ displayBalance(props.account?.initial_balance) }}
                                </p>
                            </div>

                            <div class="rounded-lg border bg-muted/30 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                    Default Account
                                </p>
                                <p class="mt-1 text-lg font-semibold text-foreground">
                                    {{ defaultLabel }}
                                </p>
                            </div>
                        </div>

                        <Separator />

                        <section class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">
                                    Account Information
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Core account metadata and identifiers.
                                </p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Account Name</p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.account?.account_name) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Account Type</p>
                                    <p class="font-medium capitalize text-foreground">
                                        {{ displayValue(props.account?.account_type) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Bank Name</p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.account?.bank_name) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Account Number</p>
                                    <p class="font-medium text-foreground">
                                        {{ displayValue(props.account?.account_number) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Currency</p>
                                    <p class="font-medium uppercase text-foreground">
                                        {{ displayValue(props.account?.currency) }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Status</p>
                                    <p class="font-medium text-foreground">
                                        {{ statusLabel }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <Separator />

                        <section class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">
                                    Description
                                </h3>
                            </div>

                            <p class="text-sm leading-6 text-foreground">
                                {{ displayValue(props.account?.description) }}
                            </p>
                        </section>
                    </div>
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
