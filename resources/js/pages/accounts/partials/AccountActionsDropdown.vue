<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import { destroy } from '@/actions/App/Http/Controllers/AccountsController';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Account } from '../types/accounts';

const props = defineProps<{
    account: Pick<Account, 'id'>;
}>();

const emit = defineEmits<{
    (e: 'edit', id: number): void;
    (e: 'view', id: number): void;
}>();

const deleteDialogOpen = ref(false);

const viewAccount = (): void => {
    emit('view', props.account.id);
};

const editAccount = (): void => {
    emit('edit', props.account.id);
};

const confirmDeleteAccount = (): void => {
    router.delete(destroy.url(props.account.id), {
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
        title="Delete account?"
        description="This will permanently remove the account record."
        confirm-label="Delete account"
        @confirm="confirmDeleteAccount"
    />

    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-8 w-8">
                <MoreHorizontal class="h-4 w-4" />
                <span class="sr-only">Open actions</span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-40">
            <DropdownMenuItem @click="viewAccount">View</DropdownMenuItem>
            <DropdownMenuItem @click="editAccount">Edit</DropdownMenuItem>

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
