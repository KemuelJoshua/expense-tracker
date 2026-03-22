<script setup lang="ts">
import {
    ChevronsLeftRight,
    CircleCheckBig,
    MoreHorizontal,
    OctagonAlert,
} from 'lucide-vue-next';
import EmptyState from '@/components/EmptyState.vue';
import TableIcon from '@/components/TableIcon.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatAmount, formatPaymentDueDay } from '@/lib/formatters';
import type { CutoffExpense, CutoffGroup } from '../types/cutoff';

const props = defineProps<{
    cutoff: CutoffGroup;
    canPay: boolean;
}>();

const emit = defineEmits<{
    (e: 'pay', expense: CutoffExpense): void;
    (e: 'penalty', expense: CutoffExpense): void;
}>();

const openPayDialog = (expense: CutoffExpense): void => {
    emit('pay', expense);
};

const openPenaltyDialog = (expense: CutoffExpense): void => {
    emit('penalty', expense);
};
</script>

<template>
    <div class="w-full space-y-4 rounded-xl border bg-background p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    {{ props.cutoff.label }}
                </h3>
                <p class="text-xs text-muted-foreground">
                    {{ props.cutoff.count }} expense{{
                        props.cutoff.count === 1 ? '' : 's'
                    }}
                    scheduled for this cutoff.
                </p>
            </div>

            <div class="text-right">
                <p
                    class="text-xs tracking-wide text-muted-foreground uppercase"
                >
                    Total
                </p>
                <p class="text-sm font-semibold text-foreground">
                    ₱{{ formatAmount(props.cutoff.total_amount) }}
                </p>
            </div>
        </div>

        <Table class="table-fixed">
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[40%] whitespace-normal"
                        >Name</TableHead
                    >
                    <TableHead class="w-[20%] text-right">Amount Due</TableHead>
                    <TableHead class="w-[30%] text-right">Paid</TableHead>
                    <TableHead class="w-18 text-right">Action</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <template v-if="props.cutoff.items.length === 0">
                    <TableRow>
                        <TableCell colspan="4" class="py-10">
                            <EmptyState
                                label="No expenses"
                                title="Nothing scheduled here"
                                description="There are no expenses assigned to this cutoff for the selected month."
                            />
                        </TableCell>
                    </TableRow>
                </template>

                <template v-else>
                    <TableRow
                        v-for="expense in props.cutoff.items"
                        :key="expense.id"
                        class="align-middle"
                    >
                        <TableCell class="align-top whitespace-normal">
                            <div class="flex min-w-0 items-center gap-3">
                                <TableIcon :text="expense.name" />

                                <div class="flex min-w-0 flex-col">
                                    <span
                                        class="text-sm font-medium wrap-break-word text-foreground"
                                    >
                                        {{ expense.name }}
                                    </span>
                                    <span
                                        class="text-xs wrap-break-word text-muted-foreground"
                                    >
                                        {{ expense.category ?? expense.type }}
                                    </span>
                                    <span
                                        class="text-xs wrap-break-word text-muted-foreground"
                                    >
                                        {{
                                            formatPaymentDueDay(
                                                expense.payment_due,
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell class="text-right">
                            <span
                                class="text-sm font-semibold text-foreground tabular-nums"
                            >
                                ₱{{
                                    formatAmount(expense.effective_due_amount)
                                }}
                            </span>
                            <span
                                v-if="Number(expense.penalty_amount) > 0"
                                class="mt-1 block text-xs text-muted-foreground"
                            >
                                Includes ₱{{
                                    formatAmount(expense.penalty_amount)
                                }}
                                penalty
                            </span>
                        </TableCell>

                        <TableCell class="text-right">
                            <div
                                class="inline-flex max-w-full flex-col items-end"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-destructive tabular-nums"
                                    >
                                        ₱{{
                                            formatAmount(
                                                expense.paid_this_month,
                                            )
                                        }}
                                    </span>
                                </div>

                                <span
                                    class="mt-1 text-xs text-muted-foreground"
                                >
                                    Bal: ₱{{
                                        formatAmount(expense.remaining_amount)
                                    }}
                                </span>

                                <span
                                    v-if="Number(expense.carryover_amount) > 0"
                                    class="mt-1 text-xs text-muted-foreground"
                                >
                                    +₱{{
                                        formatAmount(expense.carryover_amount)
                                    }}
                                </span>
                            </div>
                        </TableCell>

                        <TableCell class="text-right whitespace-nowrap">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                    >
                                        <MoreHorizontal class="h-4 w-4" />
                                        <span class="sr-only"
                                            >Open actions</span
                                        >
                                    </Button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent align="end" class="w-36">
                                    <DropdownMenuItem
                                        v-if="props.canPay"
                                        class="gap-2"
                                        @click="openPayDialog(expense)"
                                    >
                                        <CircleCheckBig class="h-3.5 w-3.5" />
                                        Pay
                                    </DropdownMenuItem>

                                    <DropdownMenuItem class="gap-2">
                                        <ChevronsLeftRight
                                            class="h-3.5 w-3.5"
                                        />
                                        Move
                                    </DropdownMenuItem>

                                    <DropdownMenuItem
                                        class="gap-2"
                                        @click="openPenaltyDialog(expense)"
                                    >
                                        <OctagonAlert class="h-3.5 w-3.5" />
                                        Add Penalty
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
