export const formatAmount = (amount: number | string): string => {
    return Number(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

export const formatDate = (date: string | null): string => {
    if (!date) {
        return 'Open-ended';
    }

    return new Intl.DateTimeFormat('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(date));
};