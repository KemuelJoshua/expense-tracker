export interface Expense {
    id: number;
    name: string;
    total_amount: number | string;
    paid_amount: number | string;
    type: string;
    category: string | null;
    reference_no: string | null;
    date_start: string;
    date_end: string | null;
    payment_due: number | string | null;
    pay_in: string;
    payment_mode: string | null;
    is_recurring: boolean | number;
    recurring_cycle: string | null;
    description: string | null;
    attachment: string | null;
    created_by: number | null;
    created_at?: string;
    updated_at?: string;
}

export interface ExpenseFormData {
    name: string;
    total_amount: number | string;
    paid_amount: number | string;
    type: string;
    category: string;
    reference_no: string;
    date_start: string;
    date_end: string;
    payment_due: number | string;
    pay_in: string;
    payment_mode: string;
    is_recurring: number;
    recurring_cycle: string;
    description: string;
    attachment: File | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedExpenses {
    data: Expense[];
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    per_page: number;
    to: number | null;
    total: number;
}

export interface Filters {
    search: string;
}

export interface Summary {
  from: number | null;
  to: number | null;
  total: number;
}
