<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import { destroy } from '@/actions/App/Http/Controllers/ExpensesController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
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

const deleteDialogOpen = ref(false);

const viewExpense = () => {
    emit('view', props.expense.id);
};

const editExpense = () => {
    emit('edit', props.expense.id);
};

const confirmDeleteExpense = (): void => {
    router.delete(destroy.url(props.expense.id), {
        preserveScroll: true,
        onFinish: () => {
            deleteDialogOpen.value = false;
        },
    });
};
</script>

<template>
    <ConfirmDeleteDialog
        v-model:open="deleteDialogOpen"
        title="Delete expense?"
        description="This will permanently remove the expense record."
        confirm-label="Delete expense"
        @confirm="confirmDeleteExpense"
    />

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
                @click="deleteDialogOpen = true"
            >
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
