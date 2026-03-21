<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import ExpenseForm from './partials/ExpenseForm.vue';
import type { ExpenseFormData } from './types/expense';

const props = defineProps<{
    open: boolean;
    form: ExpenseFormData & {
        processing: boolean;
        errors?: Record<string, string>;
    };
    mode: 'create' | 'edit';
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'create'): void;
    (e: 'submit'): void;
    (e: 'close'): void;
}>();

const handleCreateClick = () => {
    emit('create');
    emit('update:open', true);
};

const handleOpenChange = (value: boolean) => {
    emit('update:open', value);

    if (!value) {
        emit('close');
    }
};

const handleSubmit = () => {
    emit('submit');
};

const formModel = computed({
    get: () => props.form,
    set: () => {},
});

</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogTrigger as-child>
            <Button @click="handleCreateClick">
                <Plus class="mr-2 h-4 w-4" />
                Add Expense
            </Button>
        </DialogTrigger>

        <DialogContent
            class="flex max-h-[90vh] w-full flex-col gap-0 p-0 sm:max-w-5xl"
        >
            <DialogHeader class="border-b px-6 py-4">
                <DialogTitle class="text-lg font-semibold">
                    {{ props.mode === 'edit' ? 'Edit Expense' : 'Add Expense' }}
                </DialogTitle>

                <DialogDescription class="mt-1 text-sm text-muted-foreground">
                    {{
                        props.mode === 'edit'
                            ? 'Update the expense details below.'
                            : 'Fill in the expense details below.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form
                @submit.prevent="handleSubmit"
                class="flex flex-1 flex-col overflow-hidden"
            >
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <ExpenseForm v-model:form="formModel" />
                </div>

                <DialogFooter class="border-t px-6 py-4 sm:justify-between">
                    <DialogClose as-child>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="props.form.processing"
                        >
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        :disabled="props.form.processing"
                        class="min-w-35"
                    >
                        {{
                            props.form.processing
                                ? 'Saving...'
                                : props.mode === 'edit'
                                  ? 'Update Expense'
                                  : 'Save Expense'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
