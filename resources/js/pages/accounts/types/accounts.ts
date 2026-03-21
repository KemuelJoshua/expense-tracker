export interface Account {
    id: number;
    account_name: string;
    account_type: string;
    balance: number | string;
    initial_balance: number | string;
    account_number: string | null;
    bank_name: string | null;
    currency: string;
    is_active: boolean | number;
    is_default: boolean | number;
    user_id: number;
    description: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface AccountFormData {
    account_name: string;
    account_type: string;
    balance: number | string;
    initial_balance: number | string;
    account_number: string;
    bank_name: string;
    currency: string;
    is_active: number;
    is_default: number;
    description: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedAccounts {
    data: Account[];
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
