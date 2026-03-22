export interface ExpenseOption {
    id: number;
    name: string;
    type: string;
}

export interface AccountOption {
    id: number;
    account_name: string;
    account_type: string;
}

export interface SpendIncome {
    id: number;
    entry_type: string;
    transaction_date: string;
    amount: number | string;
    description: string | null;
    expense_id: number | null;
    account_id: number | null;
    is_payroll: boolean | number;
    payroll_month: number | null;
    payroll_year: number | null;
    expense: ExpenseOption | null;
    account: AccountOption | null;
}

export interface SpendIncomeFormData {
    entry_type: string;
    transaction_date: string;
    amount: number | string;
    description: string;
    expense_reference: string;
    account_reference: string;
    is_payroll: number | string;
    payroll_month: string;
    payroll_year: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedSpendIncomes {
    data: SpendIncome[];
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

export interface SpendIncomeOptions {
    expenses: ExpenseOption[];
    accounts: AccountOption[];
}
