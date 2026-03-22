import { useForm } from '@inertiajs/vue3';
import type { SpendIncomeFormData } from '../types/spend-income';

export function useSpendIncomeForm() {
    const today = new Date().toISOString().slice(0, 10);

    const form = useForm<SpendIncomeFormData>({
        entry_type: 'spend',
        transaction_date: today,
        amount: '',
        description: '',
        expense_reference: 'others',
        account_reference: 'others',
        is_payroll: '0',
        payroll_month: '',
        payroll_year: '',
    });

    const resetForm = (): void => {
        form.reset();
        form.clearErrors();
        form.entry_type = 'spend';
        form.transaction_date = today;
        form.expense_reference = 'others';
        form.account_reference = 'others';
        form.is_payroll = '0';
    };

    return {
        form,
        resetForm,
    };
}
