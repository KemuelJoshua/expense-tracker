import { useForm } from '@inertiajs/vue3';

export function useFilterForm() {
    const form = useForm<{
        date: string;
        type: string;
    }>({
        date: new Date().toISOString().slice(0, 7),
        type: '1st',
    });

    return {
        form,
    };
}
