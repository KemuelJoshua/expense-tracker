import { ref } from 'vue';

const open = ref(false);
const mode = ref<'create' | 'edit'>('create');
const expenseId = ref<number | null>(null);

export function useExpenseDialog() {
    const openDialog = () => {
        open.value = true;
    };

    const openCreateDialog = () => {
        mode.value = 'create';
        expenseId.value = null;
        open.value = true;
    };

    const openEditDialog = (id: number) => {
        mode.value = 'edit';
        expenseId.value = id;
        open.value = true;
    };

    const closeDialog = () => {
        open.value = false;
    };

    const resetDialog = () => {
        mode.value = 'create';
        expenseId.value = null;
        open.value = false;
    };

    const toggleDialog = () => {
        open.value = !open.value;
    };

    return {
        expenseId,
        mode,
        open,
        openDialog,
        openCreateDialog,
        openEditDialog,
        closeDialog,
        resetDialog,
        toggleDialog,
    };
}
