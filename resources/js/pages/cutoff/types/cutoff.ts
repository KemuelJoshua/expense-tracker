export interface CutoffExpense {
    id: number;
    name: string;
    type: string;
    category: string | null;
    reference_no: string | null;
    date_start: string;
    date_end: string;
    payment_due: number | string | null;
    total_amount: number | string;
    paid_amount: number | string;
    pay_in: string;
}

export interface CutoffGroup {
    key: string;
    label: string;
    count: number;
    total_amount: number | string;
    total_paid: number | string;
    items: CutoffExpense[];
}

export interface CutoffSummary {
    month: string;
    count: number;
    total_amount: number | string;
    total_paid: number | string;
}

export interface CutoffFilters {
    month: string;
}
