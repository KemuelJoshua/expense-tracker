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

export const formatPaymentDueDay = (
    day: number | string | null,
): string => {
    if (day === null || day === undefined || day === '') {
        return '—';
    }

    const normalizedDay = Number(day);

    if (Number.isNaN(normalizedDay) || normalizedDay < 1 || normalizedDay > 31) {
        return String(day);
    }

    if (normalizedDay === 1) {
        return 'Day 1 (First day of the month)';
    }

    if (normalizedDay === 31) {
        return 'Day 31 (End of month)';
    }

    return `Day ${normalizedDay}`;
};
