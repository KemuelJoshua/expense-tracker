<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import { Ellipsis, Trash, Move, SlidersHorizontal } from 'lucide-vue-next';
</script>

<template>
    <div class="w-full space-y-4 rounded-xl border bg-background p-4 shadow-sm">
        <!-- Title -->
        <h3 class="text-sm font-semibold text-muted-foreground">
            Expense (First Cuttoff)
        </h3>

        <!-- Table -->
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Category</TableHead>
                    <TableHead>Amount Due</TableHead>
                    <TableHead>Amount Paid</TableHead>
                    <TableHead>Remarks</TableHead>
                    <TableHead class="w-20 text-right">Action</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow>
                    <TableCell>
                        <Input placeholder="Internet" />
                    </TableCell>

                    <TableCell>
                        <Input type="number" placeholder="0.00" />
                    </TableCell>

                    <TableCell>
                        <Input type="number" placeholder="0.00" />
                    </TableCell>

                    <TableCell>
                        <Input placeholder="Optional" />
                    </TableCell>

                    <TableCell class="text-right">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon">
                                    <Ellipsis class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent align="end">
                                <DropdownMenuItem>
                                    <SlidersHorizontal class="mr-2 h-4 w-4" />
                                    Adjustment
                                </DropdownMenuItem>

                                <DropdownMenuItem>
                                    <Move class="mr-2 h-4 w-4" />
                                    Move
                                </DropdownMenuItem>

                                <DropdownMenuItem
                                    class="text-destructive focus:text-destructive"
                                >
                                    <Trash class="mr-2 h-4 w-4" />
                                    Remove
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
