<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = withDefaults(defineProps<{
    open: boolean;
    title?: string;
    description?: string;
    confirmLabel?: string;
    processing?: boolean;
}>(), {
    title: 'Confirm deletion',
    description: 'This action cannot be undone.',
    confirmLabel: 'Delete',
    processing: false,
});

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
}>();

const handleOpenChange = (value: boolean): void => {
    emit('update:open', value);
};

const handleConfirm = (): void => {
    emit('confirm');
};
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>{{ props.title }}</DialogTitle>
                <DialogDescription>
                    {{ props.description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="props.processing"
                    @click="emit('update:open', false)"
                >
                    Cancel
                </Button>

                <Button
                    type="button"
                    variant="destructive"
                    :disabled="props.processing"
                    @click="handleConfirm"
                >
                    {{ props.processing ? 'Deleting...' : props.confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
