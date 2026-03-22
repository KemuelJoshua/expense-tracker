<script setup lang="ts">
import {
    CalendarDays,
    ChevronsLeftRight,
    CircleCheckBig,
    CirclePause,
    MoreHorizontal,
    ReceiptText,
    Wallet,
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
import { formatAmount, formatDate, formatPaymentDueDay } from '@/lib/formatters';
import type { CutoffGroup } from '../types/cutoff';

defineProps<{
    cutoff: CutoffGroup;
}>();
</script>

<template>
    <div class="w-full space-y-4 rounded-xl border bg-background p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-foreground">
                    {{ cutoff.label }}
                </h3>
                <p class="text-xs text-muted-foreground">
                    {{ cutoff.count }} expense{{ cutoff.count === 1 ? '' : 's' }} scheduled for this cutoff.
                </p>
            </div>

            <div class="text-right">
                <p class="text-xs uppercase tracking-wide text-muted-foreground">
                    Total
                </p>
                <p class="text-sm font-semibold text-foreground">
                    ₱{{ formatAmount(cutoff.total_amount) }}
                </p>
            </div>
        </div>

        <Table class="table-fixed">
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[30%] whitespace-normal">Name</TableHead>
                    <TableHead class="w-[34%] whitespace-normal">Schedule</TableHead>
                    <TableHead class="w-[16%] text-right">Amount</TableHead>
                    <TableHead class="w-[16%] text-right">Paid</TableHead>
                    <TableHead class="w-18 text-right">Action</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <template v-if="cutoff.items.length === 0">
                    <TableRow>
                        <TableCell colspan="5" class="py-10">
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
                        v-for="expense in cutoff.items"
                        :key="expense.id"
                        class="align-middle"
                    >
                        <TableCell class="align-top whitespace-normal">
                            <div class="flex min-w-0 items-center gap-3">
                                <TableIcon :text="expense.name" />

                                <div class="flex min-w-0 flex-col">
                                    <span class="wrap-break-word text-sm font-medium text-foreground">
                                        {{ expense.name }}
                                    </span>
                                    <span class="wrap-break-word text-xs text-muted-foreground">
                                        {{ expense.category ?? expense.type }}
                                    </span>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell class="align-top whitespace-normal text-sm">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <CalendarDays class="h-3.5 w-3.5" />
                                    <span class="wrap-break-word">{{ formatDate(expense.date_start) }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <ReceiptText class="h-3.5 w-3.5" />
                                    <span class="wrap-break-word">{{ formatDate(expense.date_end) }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <Wallet class="h-3.5 w-3.5" />
                                    <span class="wrap-break-word">{{ formatPaymentDueDay(expense.payment_due) }}</span>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell class="text-right">
                            <span class="text-sm font-semibold text-foreground tabular-nums">
                                ₱{{ formatAmount(expense.total_amount) }}
                            </span>
                        </TableCell>

                        <TableCell class="text-right">
                            <div class="inline-flex max-w-full items-center gap-2 rounded-lg bg-muted/40 px-3 py-2">
                                <Wallet class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm font-medium text-foreground tabular-nums">
                                    ₱{{ formatAmount(expense.paid_amount) }}
                                </span>
                            </div>
                        </TableCell>
                        
                        <TableCell class="whitespace-nowrap text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8">
                                        <MoreHorizontal class="h-4 w-4" />
                                        <span class="sr-only">Open actions</span>
                                    </Button>
                                </DropdownMenuTrigger>

                                <DropdownMenuContent align="end" class="w-36">
                                    <DropdownMenuItem class="gap-2">
                                        <CircleCheckBig class="h-3.5 w-3.5" />
                                        Pay
                                    </DropdownMenuItem>

                                    <DropdownMenuItem class="gap-2">
                                        <ChevronsLeftRight class="h-3.5 w-3.5" />
                                        Move
                                    </DropdownMenuItem>

                                    <DropdownMenuItem class="gap-2">
                                        <CirclePause class="h-3.5 w-3.5" />
                                        Skip
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
