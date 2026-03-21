<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { destroy } from '@/actions/App/Http/Controllers/AccountsController';
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

const viewAccount = (): void => {
    emit('view', props.account.id);
};

const editAccount = (): void => {
    emit('edit', props.account.id);
};

const deleteAccount = (): void => {
    if (!window.confirm('Are you sure you want to delete this account?')) {
        return;
    }

    router.delete(destroy.url(props.account.id), {
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
            <DropdownMenuItem @click="viewAccount">View</DropdownMenuItem>
            <DropdownMenuItem @click="editAccount">Edit</DropdownMenuItem>

            <DropdownMenuSeparator />

            <DropdownMenuItem
                class="text-destructive focus:text-destructive"
                @click="deleteAccount"
            >
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
