import { useForm } from '@inertiajs/vue3';
import type { AccountFormData } from '../types/accounts';

export function useAccountForm() {
    const form = useForm<AccountFormData>({
        account_name: '',
        account_type: '',
        balance: '',
        initial_balance: '',
        account_number: '',
        bank_name: '',
        currency: 'PHP',
        is_active: 1,
        is_default: 0,
        description: '',
    });

    const resetForm = (): void => {
        form.reset();
        form.clearErrors();
    };

    return {
        form,
        resetForm,
    };
}
