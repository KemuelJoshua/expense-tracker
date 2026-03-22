<script setup lang="ts">
import { ArrowDownCircle, ArrowUpCircle } from 'lucide-vue-next';
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
import SpendIncomeForm from './partials/SpendIncomeForm.vue';
import type { SpendIncomeFormData, SpendIncomeOptions } from './types/spend-income';

type EntryType = 'spend' | 'income';

const props = defineProps<{
    open: boolean;
    form: SpendIncomeFormData & {
        processing: boolean;
        errors?: Record<string, string>;
    };
    mode: 'create' | 'edit';
    options: SpendIncomeOptions;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'create', type: EntryType): void;
    (e: 'submit'): void;
    (e: 'close'): void;
}>();

const handleCreateClick = (type: EntryType): void => {
    emit('create', type);
    emit('update:open', true);
};

const handleOpenChange = (value: boolean): void => {
    emit('update:open', value);

    if (!value) {
        emit('close');
    }
};

const formModel = computed({
    get: () => props.form,
    set: () => {},
});
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <div class="flex items-center gap-2">
            <DialogTrigger as-child>
                <Button class="bg-amber-600 hover:bg-amber-500" @click="handleCreateClick('spend')">
                    <ArrowUpCircle class="mr-2 h-4 w-4" />
                    Add Spend
                </Button>
            </DialogTrigger>

            <DialogTrigger as-child>
                <Button class="bg-green-600 hover:bg-green-500" @click="handleCreateClick('income')">
                    <ArrowDownCircle class="mr-2 h-4 w-4" />
                    Add Income
                </Button>
            </DialogTrigger>
        </div>

        <DialogContent class="flex max-h-[90vh] w-full flex-col gap-0 p-0 sm:max-w-5xl">
            <DialogHeader class="border-b px-6 py-4">
                <DialogTitle class="text-lg font-semibold">
                    {{
                        props.mode === 'edit'
                            ? 'Edit Spend / Income'
                            : props.form.entry_type === 'income'
                              ? 'Add Income'
                              : 'Add Spend'
                    }}
                </DialogTitle>

                <DialogDescription class="mt-1 text-sm text-muted-foreground">
                    {{
                        props.form.entry_type === 'income'
                            ? 'Record an income entry with optional payroll and account details.'
                            : 'Record a spend entry with an optional linked expense.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="emit('submit')" class="flex flex-1 flex-col overflow-hidden">
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <SpendIncomeForm
                        v-model:form="formModel"
                        :options="props.options"
                        :type-disabled="true"
                    />
                </div>

                <DialogFooter class="border-t px-6 py-4 sm:justify-between">
                    <DialogClose as-child>
                        <Button type="button" variant="outline" :disabled="props.form.processing">
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button type="submit" :disabled="props.form.processing" class="min-w-40">
                        {{
                            props.form.processing
                                ? 'Saving...'
                                : props.mode === 'edit'
                                  ? 'Update Entry'
                                  : 'Save Entry'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
