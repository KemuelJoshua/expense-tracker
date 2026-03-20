<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Expense } from '../types/expense';

const props = defineProps<{
    expense: Pick<Expense, 'id'>;
}>();

const emit = defineEmits<{
    (e: 'map', id: number): void;
    (e: 'edit', id: number): void;
    (e: 'view', id: number): void;
}>();

const viewExpense = () => {
    emit('view', props.expense.id);
};

const editExpense = () => {
    emit('edit', props.expense.id);
};

const deleteExpense = () => {
    if (!window.confirm('Are you sure you want to delete this expense?')) {
        return;
    }

    router.delete(`/expenses/${props.expense.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-8 w-8">
                <MoreHorizontal class="h-4 w-4" />
                <span class="sr-only">Open actions</span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-40">
            <DropdownMenuItem @click="viewExpense">View</DropdownMenuItem>

            <DropdownMenuItem @click="editExpense">Edit</DropdownMenuItem>

            <DropdownMenuSeparator />

            <DropdownMenuItem
                class="text-destructive focus:text-destructive"
                @click="deleteExpense"
            >
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
