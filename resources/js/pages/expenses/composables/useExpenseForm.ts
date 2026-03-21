import { useForm } from '@inertiajs/vue3';
import type { ExpenseFormData } from '../types/expense';

export function useExpenseForm() {
    const form = useForm<ExpenseFormData>({
        name: '',
        total_amount: '',
        paid_amount: 0,
        type: '',
        category: '',
        reference_no: '',
        date_start: '',
        date_end: '',
        payment_due: '',
        pay_in: '',
        payment_mode: '',
        is_recurring: 0,
        recurring_cycle: '',
        description: '',
        attachment: null,
    });

    const resetForm = () => {
        form.reset();
        form.clearErrors();
    };

    return {
        form,
        resetForm,
    };
}
