<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import { destroy } from '@/actions/App/Http/Controllers/SpendIncomeController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { SpendIncome } from '../types/spend-income';

const props = defineProps<{
    spendIncome: Pick<SpendIncome, 'id'>;
}>();

const emit = defineEmits<{
    (e: 'view', id: number): void;
    (e: 'edit', id: number): void;
}>();

const deleteDialogOpen = ref(false);
</script>

<template>
    <ConfirmDeleteDialog
        v-model:open="deleteDialogOpen"
        title="Delete entry?"
        description="This will permanently remove the selected spend / income record."
        confirm-label="Delete entry"
        @confirm="
            router.delete(destroy.url(props.spendIncome.id), {
                preserveScroll: true,
                onFinish: () => {
                    deleteDialogOpen = false;
                },
            })
        "
    />

    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-8 w-8">
                <MoreHorizontal class="h-4 w-4" />
                <span class="sr-only">Open actions</span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-40">
            <DropdownMenuItem @click="emit('view', props.spendIncome.id)">View</DropdownMenuItem>
            <DropdownMenuItem @click="emit('edit', props.spendIncome.id)">Edit</DropdownMenuItem>

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
