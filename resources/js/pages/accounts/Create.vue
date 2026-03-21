<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import AccountForm from './partials/AccountForm.vue';
import type { AccountFormData } from './types/accounts';

const props = defineProps<{
    open: boolean;
    form: AccountFormData & {
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

const handleCreateClick = (): void => {
    emit('create');
    emit('update:open', true);
};

const handleOpenChange = (value: boolean): void => {
    emit('update:open', value);

    if (!value) {
        emit('close');
    }
};

const handleSubmit = (): void => {
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
                Add Account
            </Button>
        </DialogTrigger>

        <DialogContent class="flex max-h-[90vh] w-full flex-col gap-0 p-0 sm:max-w-5xl">
            <DialogHeader class="border-b px-6 py-4">
                <DialogTitle class="text-lg font-semibold">
                    {{ props.mode === 'edit' ? 'Edit Account' : 'Add Account' }}
                </DialogTitle>

                <DialogDescription class="mt-1 text-sm text-muted-foreground">
                    {{
                        props.mode === 'edit'
                            ? 'Update the account details below.'
                            : 'Fill in the account details below.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <AccountForm v-model:form="formModel" />
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
                                  ? 'Update Account'
                                  : 'Save Account'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
