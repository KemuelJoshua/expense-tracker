<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowDownRight,
    ArrowUpRight,
    CalendarClock,
    CircleDollarSign,
    Landmark,
    PiggyBank,
    ReceiptText,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatAmount, formatDate } from '@/lib/formatters';
import { dashboard } from '@/routes';
import { index as accountsIndex } from '@/routes/accounts';
import { index as cutoffIndex } from '@/routes/cutoff';
import { index as expensesIndex } from '@/routes/expenses';
import { index as spendIncomeIndex } from '@/routes/spend-income';
import type { BreadcrumbItem } from '@/types';

type MonthlySeries = {
    month: string;
    label: string;
    income: number;
    spend: number;
    net: number;
};

type BalanceAccount = {
    id: number;
    account_name: string;
    account_type: string;
    bank_name: string | null;
    currency: string;
    balance: number;
    is_active: boolean;
    is_default: boolean;
};

type AccountMix = {
    type: string;
    count: number;
    total_balance: number;
    share: number;
};

type DueSoonExpense = {
    id: number;
    name: string;
    type: string;
    pay_in: string;
    payment_due: number | null;
    next_due_date: string;
    days_until_due: number;
    total_amount: number;
    paid_amount: number;
    remaining_balance: number;
};

type OutstandingExpense = {
    id: number;
    name: string;
    type: string;
    pay_in: string;
    total_amount: number;
    paid_amount: number;
    remaining_balance: number;
};

type RecentEntry = {
    id: number;
    entry_type: 'spend' | 'income';
    transaction_date: string | null;
    amount: number;
    description: string | null;
    source_label: string;
    detail_label: string;
    is_payroll: boolean;
};

type DashboardAnalytics = {
    generated_at: string;
    focus_month: {
        label: string;
        start: string;
        end: string;
    };
    overview: {
        cash_on_hand: number;
        monthly_income: number;
        monthly_spend: number;
        monthly_net: number;
        unpaid_commitments: number;
        active_accounts: number;
        total_accounts: number;
        open_expenses: number;
        recurring_expenses: number;
        coverage_rate: number;
        changes: {
            income: number | null;
            spend: number | null;
            net: number | null;
        };
    };
    cash_flow: {
        series: MonthlySeries[];
        current_month_obligation: number;
        current_month_paid: number;
    };
    accounts: {
        default_account_name: string | null;
        mix: AccountMix[];
        top_balances: BalanceAccount[];
    };
    expenses: {
        due_soon: DueSoonExpense[];
        top_outstanding: OutstandingExpense[];
    };
    activity: {
        recent_entries: RecentEntry[];
        payroll: {
            year_total: number;
            entries: number;
            latest_label: string | null;
        };
    };
};

const { analytics } = defineProps<{
    analytics: DashboardAnalytics;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const focusActions = [
    {
        label: 'Review expenses',
        href: expensesIndex(),
    },
    {
        label: 'Inspect cash flow',
        href: spendIncomeIndex(),
    },
    {
        label: 'Open accounts',
        href: accountsIndex(),
    },
    {
        label: 'Check cutoff',
        href: cutoffIndex(),
    },
];

const overviewCards = computed(() => [
    {
        title: 'Cash on hand',
        value: analytics.overview.cash_on_hand,
        subtitle: `${analytics.overview.active_accounts} active of ${analytics.overview.total_accounts} accounts`,
        change: null as number | null,
        icon: Wallet,
        tone: 'primary',
    },
    {
        title: 'Income this month',
        value: analytics.overview.monthly_income,
        subtitle: 'Tracked income entries for the focus month',
        change: analytics.overview.changes.income,
        icon: ArrowUpRight,
        tone: 'chart-2',
    },
    {
        title: 'Spend this month',
        value: analytics.overview.monthly_spend,
        subtitle: `${analytics.overview.open_expenses} expenses still carrying balance`,
        change: analytics.overview.changes.spend,
        icon: ArrowDownRight,
        tone: 'chart-3',
    },
    {
        title: 'Unpaid commitments',
        value: analytics.overview.unpaid_commitments,
        subtitle: `${analytics.overview.recurring_expenses} recurring obligations in the stack`,
        change: analytics.overview.changes.net,
        icon: ReceiptText,
        tone: analytics.overview.monthly_net >= 0 ? 'chart-4' : 'destructive',
    },
]);

const trendMax = computed(() => {
    return Math.max(
        1,
        ...analytics.cash_flow.series.map((item) =>
            Math.max(item.income, item.spend),
        ),
    );
});

const obligationProgress = computed(() => {
    const total = analytics.cash_flow.current_month_obligation;

    if (total <= 0) {
        return 0;
    }

    return Math.min(
        (analytics.cash_flow.current_month_paid / total) * 100,
        100,
    );
});

const netSparklinePath = computed(() => {
    const values = analytics.cash_flow.series.map((item) => item.net);

    if (values.length === 0) {
        return '';
    }

    const width = 260;
    const height = 72;
    const min = Math.min(...values);
    const max = Math.max(...values);
    const range = max - min || 1;

    return values
        .map((value, index) => {
            const x = (index / Math.max(values.length - 1, 1)) * width;
            const y = height - ((value - min) / range) * height;

            return `${index === 0 ? 'M' : 'L'} ${x.toFixed(2)} ${y.toFixed(2)}`;
        })
        .join(' ');
});

const mixLead = computed(() => analytics.accounts.mix[0] ?? null);

const payrollHighlight = computed(() => {
    if (analytics.activity.payroll.entries === 0) {
        return 'No payroll income recorded yet this year.';
    }

    const latestLabel =
        analytics.activity.payroll.latest_label ?? 'recent months';

    return `${analytics.activity.payroll.entries} payroll entries logged, latest in ${latestLabel}.`;
});

const formatCurrency = (amount: number): string => {
    return `PHP ${formatAmount(amount)}`;
};

const formatChange = (change: number | null): string => {
    if (change === null) {
        return 'New vs last month';
    }

    if (change === 0) {
        return 'Flat vs last month';
    }

    const direction = change > 0 ? '+' : '';

    return `${direction}${change.toFixed(1)}% vs last month`;
};

const changeToneClass = (change: number | null, invert = false): string => {
    if (change === null || change === 0) {
        return 'bg-muted text-muted-foreground ring-1 ring-border';
    }

    const isPositive = change > 0;
    const isGood = invert ? !isPositive : isPositive;

    return isGood
        ? 'bg-primary/10 text-primary ring-1 ring-primary/20'
        : 'bg-destructive/10 text-destructive ring-1 ring-destructive/20';
};

const balanceBarClass = (account: BalanceAccount): string => {
    if (account.is_default) {
        return 'from-primary via-chart-3 to-chart-4';
    }

    if (account.is_active) {
        return 'from-chart-2 via-chart-3 to-chart-4';
    }

    return 'from-muted-foreground/60 via-muted-foreground/45 to-muted-foreground/30';
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="min-h-full bg-[radial-gradient(circle_at_top,color-mix(in_oklab,var(--color-primary)_14%,transparent),transparent_28%),radial-gradient(circle_at_90%_10%,color-mix(in_oklab,var(--color-chart-3)_12%,transparent),transparent_22%)] p-4 md:p-6"
        >
            <div class="mx-auto flex max-w-7xl flex-col gap-6">
                <Card
                    class="relative overflow-hidden border-border bg-linear-to-br from-primary via-primary to-chart-3 px-6 py-7 text-primary-foreground shadow-sm md:px-8 md:py-9"
                >
                    <div
                        class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,color-mix(in_oklab,var(--color-primary-foreground)_18%,transparent),transparent_30%),radial-gradient(circle_at_bottom_left,color-mix(in_oklab,var(--color-chart-2)_22%,transparent),transparent_28%)]"
                    />

                    <div
                        class="relative grid gap-8 xl:grid-cols-[1.15fr_0.85fr]"
                    >
                        <div class="space-y-5">
                            <Badge
                                variant="secondary"
                                class="border border-primary-foreground/15 bg-primary-foreground/10 px-3 py-1 text-[11px] font-semibold tracking-[0.22em] text-primary-foreground uppercase hover:bg-primary-foreground/10"
                            >
                                Financial Command Center
                            </Badge>

                            <div class="space-y-3">
                                <h1
                                    class="max-w-4xl text-3xl font-semibold tracking-tight text-balance md:text-5xl"
                                >
                                    A live operating view of cash, obligations,
                                    and transaction momentum.
                                </h1>
                                <p
                                    class="max-w-3xl text-sm leading-7 text-primary-foreground/80 md:text-base"
                                >
                                    {{ analytics.focus_month.label }} focuses on
                                    cash position, account concentration,
                                    due-soon expenses, payroll flow, and the
                                    most recent movements across the tracker.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <Button
                                    v-for="action in focusActions"
                                    :key="action.label"
                                    as-child
                                    size="sm"
                                    variant="secondary"
                                    class="rounded-full border border-primary-foreground/15 bg-primary-foreground/10 px-4 text-primary-foreground shadow-none transition hover:bg-primary-foreground/15 hover:text-primary-foreground"
                                >
                                    <Link :href="action.href">
                                        {{ action.label }}
                                    </Link>
                                </Button>
                            </div>

                            <div class="grid gap-3 pt-2 md:grid-cols-3">
                                <div
                                    class="rounded-2xl border border-primary-foreground/15 bg-primary-foreground/10 p-4 backdrop-blur-sm"
                                >
                                    <p
                                        class="text-xs tracking-[0.2em] text-primary-foreground/60 uppercase"
                                    >
                                        Monthly Net
                                    </p>
                                    <p class="mt-3 text-2xl font-semibold">
                                        {{
                                            formatCurrency(
                                                analytics.overview.monthly_net,
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="mt-2 text-sm text-primary-foreground/75"
                                    >
                                        {{
                                            analytics.overview.monthly_net >= 0
                                                ? 'Positive net flow this month.'
                                                : 'Spending is outrunning income this month.'
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-primary-foreground/15 bg-primary-foreground/10 p-4 backdrop-blur-sm"
                                >
                                    <p
                                        class="text-xs tracking-[0.2em] text-primary-foreground/60 uppercase"
                                    >
                                        Coverage Rate
                                    </p>
                                    <p class="mt-3 text-2xl font-semibold">
                                        {{
                                            analytics.overview.coverage_rate.toFixed(
                                                1,
                                            )
                                        }}%
                                    </p>
                                    <p
                                        class="mt-2 text-sm text-primary-foreground/75"
                                    >
                                        Current-month obligations already
                                        covered by paid balances.
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-primary-foreground/15 bg-primary-foreground/10 p-4 backdrop-blur-sm"
                                >
                                    <p
                                        class="text-xs tracking-[0.2em] text-primary-foreground/60 uppercase"
                                    >
                                        Payroll YTD
                                    </p>
                                    <p class="mt-3 text-2xl font-semibold">
                                        {{
                                            formatCurrency(
                                                analytics.activity.payroll
                                                    .year_total,
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="mt-2 text-sm text-primary-foreground/75"
                                    >
                                        {{ payrollHighlight }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 self-start">
                            <div
                                class="rounded-[1.75rem] border border-primary-foreground/15 bg-primary-foreground/10 p-5 backdrop-blur-sm"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="text-xs tracking-[0.2em] text-primary-foreground/60 uppercase"
                                        >
                                            Snapshot
                                        </p>
                                        <p class="mt-2 text-xl font-semibold">
                                            {{ analytics.focus_month.label }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-full border border-primary-foreground/15 bg-primary-foreground/10 p-2 text-primary-foreground"
                                    >
                                        <Activity class="size-5" />
                                    </div>
                                </div>

                                <dl
                                    class="mt-5 grid gap-4 text-sm text-primary-foreground/80"
                                >
                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <dt>Default account</dt>
                                        <dd
                                            class="font-medium text-primary-foreground"
                                        >
                                            {{
                                                analytics.accounts
                                                    .default_account_name ??
                                                'Not assigned'
                                            }}
                                        </dd>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <dt>Open expenses</dt>
                                        <dd
                                            class="font-medium text-primary-foreground"
                                        >
                                            {{
                                                analytics.overview.open_expenses
                                            }}
                                        </dd>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <dt>Recurring expense count</dt>
                                        <dd
                                            class="font-medium text-primary-foreground"
                                        >
                                            {{
                                                analytics.overview
                                                    .recurring_expenses
                                            }}
                                        </dd>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <dt>Generated</dt>
                                        <dd
                                            class="font-medium text-primary-foreground"
                                        >
                                            {{ analytics.generated_at }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div
                                class="rounded-[1.75rem] border border-primary-foreground/15 bg-primary-foreground/10 p-5 backdrop-blur-sm"
                            >
                                <p
                                    class="text-xs tracking-[0.2em] text-primary-foreground/60 uppercase"
                                >
                                    Risk Lens
                                </p>
                                <p
                                    class="mt-2 text-base font-medium text-primary-foreground"
                                >
                                    {{
                                        analytics.overview.unpaid_commitments >
                                        analytics.overview.cash_on_hand
                                            ? 'Commitments currently exceed liquid balances.'
                                            : 'Liquid balances currently cover the unpaid commitment stack.'
                                    }}
                                </p>
                                <div
                                    class="mt-4 h-2.5 overflow-hidden rounded-full bg-primary-foreground/15"
                                >
                                    <div
                                        class="h-full rounded-full bg-linear-to-r from-primary via-chart-2 to-chart-4"
                                        :style="{
                                            width: `${Math.min((analytics.overview.cash_on_hand / Math.max(analytics.overview.unpaid_commitments || 1, 1)) * 100, 100)}%`,
                                        }"
                                    />
                                </div>
                                <div
                                    class="mt-3 flex items-center justify-between text-xs text-primary-foreground/75"
                                >
                                    <span>Cash buffer</span>
                                    <span>
                                        {{
                                            analytics.overview
                                                .unpaid_commitments > 0
                                                ? `${((analytics.overview.cash_on_hand / analytics.overview.unpaid_commitments) * 100).toFixed(1)}% of unpaid commitments`
                                                : 'No unpaid commitments'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <Card
                        v-for="card in overviewCards"
                        :key="card.title"
                        class="overflow-hidden border-border bg-card/95 p-5 shadow-sm backdrop-blur-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    {{ card.title }}
                                </p>
                                <p
                                    class="text-2xl font-semibold tracking-tight text-foreground"
                                >
                                    {{ formatCurrency(card.value) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl p-3"
                                :class="{
                                    'bg-primary/10 text-primary':
                                        card.tone === 'primary',
                                    'bg-chart-2/15 text-chart-2':
                                        card.tone === 'chart-2',
                                    'bg-chart-3/15 text-chart-3':
                                        card.tone === 'chart-3',
                                    'bg-chart-4/15 text-chart-4':
                                        card.tone === 'chart-4',
                                    'bg-destructive/10 text-destructive':
                                        card.tone === 'destructive',
                                }"
                            >
                                <component :is="card.icon" class="size-5" />
                            </div>
                        </div>

                        <div
                            class="mt-5 flex items-center justify-between gap-3"
                        >
                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    changeToneClass(
                                        card.change,
                                        card.title === 'Spend this month',
                                    )
                                "
                            >
                                {{ formatChange(card.change) }}
                            </span>
                            <span
                                class="text-right text-xs leading-5 text-muted-foreground"
                            >
                                {{ card.subtitle }}
                            </span>
                        </div>
                    </Card>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.45fr_0.95fr]">
                    <Card
                        class="border-border bg-card/95 p-6 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    Six-Month Flow
                                </p>
                                <h2
                                    class="mt-2 text-2xl font-semibold tracking-tight text-foreground"
                                >
                                    Income, spend, and monthly net movement
                                </h2>
                            </div>
                            <div
                                class="rounded-full bg-muted px-4 py-2 text-xs font-medium text-muted-foreground"
                            >
                                {{ analytics.cash_flow.series.length }} monthly
                                points
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4">
                            <div
                                class="flex flex-wrap items-center gap-4 text-xs font-medium text-muted-foreground"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <span
                                        class="h-2.5 w-2.5 rounded-full bg-chart-2"
                                    />
                                    Income
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <span
                                        class="h-2.5 w-2.5 rounded-full bg-chart-3"
                                    />
                                    Spend
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <span
                                        class="h-2.5 w-2.5 rounded-full bg-chart-4"
                                    />
                                    Net sparkline
                                </span>
                            </div>

                            <div class="grid gap-4 md:grid-cols-[1.1fr_0.9fr]">
                                <div class="rounded-[1.4rem] bg-muted p-4">
                                    <div
                                        class="grid h-72 grid-cols-6 items-end gap-3"
                                    >
                                        <div
                                            v-for="point in analytics.cash_flow
                                                .series"
                                            :key="point.label"
                                            class="flex h-full flex-col items-center justify-end gap-2"
                                        >
                                            <div
                                                class="flex h-full items-end gap-1.5"
                                            >
                                                <div
                                                    class="w-4 rounded-t-full bg-linear-to-t from-chart-2 to-primary"
                                                    :style="{
                                                        height: `${Math.max((point.income / trendMax) * 100, point.income > 0 ? 7 : 0)}%`,
                                                    }"
                                                />
                                                <div
                                                    class="w-4 rounded-t-full bg-linear-to-t from-chart-3 to-chart-4"
                                                    :style="{
                                                        height: `${Math.max((point.spend / trendMax) * 100, point.spend > 0 ? 7 : 0)}%`,
                                                    }"
                                                />
                                            </div>
                                            <div class="space-y-1 text-center">
                                                <p
                                                    class="text-xs font-semibold text-foreground"
                                                >
                                                    {{ point.month }}
                                                </p>
                                                <p
                                                    class="text-[11px] text-muted-foreground"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            point.net,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-[1.4rem] bg-accent p-4 text-accent-foreground"
                                >
                                    <div
                                        class="flex items-start justify-between gap-4"
                                    >
                                        <div>
                                            <p
                                                class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                            >
                                                Net Trace
                                            </p>
                                            <p
                                                class="mt-2 text-lg font-semibold"
                                            >
                                                {{
                                                    formatCurrency(
                                                        analytics.overview
                                                            .monthly_net,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <CircleDollarSign
                                            class="mt-1 size-5 text-muted-foreground"
                                        />
                                    </div>

                                    <svg
                                        viewBox="0 0 260 72"
                                        class="mt-5 h-20 w-full"
                                        fill="none"
                                        preserveAspectRatio="none"
                                    >
                                        <path
                                            d="M 0 36 L 260 36"
                                            stroke="rgba(255,255,255,0.12)"
                                            stroke-width="1"
                                            stroke-dasharray="4 4"
                                        />
                                        <path
                                            :d="netSparklinePath"
                                            stroke="url(#dashboardNetGradient)"
                                            stroke-width="4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <defs>
                                            <linearGradient
                                                id="dashboardNetGradient"
                                                x1="0"
                                                y1="0"
                                                x2="260"
                                                y2="0"
                                            >
                                                <stop
                                                    offset="0%"
                                                    stop-color="#2dd4bf"
                                                />
                                                <stop
                                                    offset="100%"
                                                    stop-color="#38bdf8"
                                                />
                                            </linearGradient>
                                        </defs>
                                    </svg>

                                    <div
                                        class="mt-4 grid gap-3 text-sm text-muted-foreground"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                >Current-month obligation</span
                                            >
                                            <span
                                                class="font-medium text-foreground"
                                            >
                                                {{
                                                    formatCurrency(
                                                        analytics.cash_flow
                                                            .current_month_obligation,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span>Already paid</span>
                                            <span
                                                class="font-medium text-foreground"
                                            >
                                                {{
                                                    formatCurrency(
                                                        analytics.cash_flow
                                                            .current_month_paid,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="h-2.5 overflow-hidden rounded-full bg-background"
                                        >
                                            <div
                                                class="h-full rounded-full bg-linear-to-r from-primary via-chart-2 to-chart-4"
                                                :style="{
                                                    width: `${obligationProgress}%`,
                                                }"
                                            />
                                        </div>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                analytics.overview.coverage_rate.toFixed(
                                                    1,
                                                )
                                            }}% coverage rate for active
                                            expenses in the focus month.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <div class="grid gap-6">
                        <Card
                            class="border-border bg-card/95 p-6 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p
                                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >
                                        Account Mix
                                    </p>
                                    <h2
                                        class="mt-2 text-xl font-semibold tracking-tight text-foreground"
                                    >
                                        Where balances are concentrated
                                    </h2>
                                </div>
                                <Landmark
                                    class="mt-1 size-5 text-muted-foreground"
                                />
                            </div>

                            <div
                                v-if="mixLead"
                                class="mt-5 rounded-[1.35rem] bg-muted p-4"
                            >
                                <p
                                    class="text-xs tracking-[0.16em] text-muted-foreground uppercase"
                                >
                                    Largest bucket
                                </p>
                                <p
                                    class="mt-2 text-lg font-semibold text-foreground"
                                >
                                    {{ mixLead.type }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ mixLead.share.toFixed(1) }}% of total
                                    balances across
                                    {{ mixLead.count }} account(s).
                                </p>
                            </div>

                            <div class="mt-5 space-y-4">
                                <div
                                    v-for="mix in analytics.accounts.mix"
                                    :key="mix.type"
                                    class="space-y-2"
                                >
                                    <div
                                        class="flex items-center justify-between gap-3 text-sm"
                                    >
                                        <div>
                                            <p
                                                class="font-medium text-foreground capitalize"
                                            >
                                                {{ mix.type }}
                                            </p>
                                            <p class="text-muted-foreground">
                                                {{ mix.count }} account(s)
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="font-medium text-foreground"
                                            >
                                                {{
                                                    formatCurrency(
                                                        mix.total_balance,
                                                    )
                                                }}
                                            </p>
                                            <p class="text-muted-foreground">
                                                {{ mix.share.toFixed(1) }}%
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="h-2.5 overflow-hidden rounded-full bg-muted"
                                    >
                                        <div
                                            class="h-full rounded-full bg-linear-to-r from-primary via-chart-2 to-chart-4"
                                            :style="{ width: `${mix.share}%` }"
                                        />
                                    </div>
                                </div>

                                <p
                                    v-if="analytics.accounts.mix.length === 0"
                                    class="rounded-2xl border border-dashed border-border px-4 py-6 text-sm text-muted-foreground"
                                >
                                    No accounts yet. Start by creating an
                                    account so the dashboard can map liquidity
                                    and account concentration.
                                </p>
                            </div>
                        </Card>

                        <Card
                            class="border-border bg-card/95 p-6 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p
                                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >
                                        Payroll Signal
                                    </p>
                                    <h2
                                        class="mt-2 text-xl font-semibold tracking-tight text-foreground"
                                    >
                                        Salary-linked income tracking
                                    </h2>
                                </div>
                                <PiggyBank
                                    class="mt-1 size-5 text-muted-foreground"
                                />
                            </div>

                            <div class="mt-5 grid gap-3 md:grid-cols-2">
                                <div class="rounded-[1.35rem] bg-muted p-4">
                                    <p
                                        class="text-xs tracking-[0.16em] text-muted-foreground uppercase"
                                    >
                                        YTD Payroll
                                    </p>
                                    <p
                                        class="mt-2 text-2xl font-semibold text-foreground"
                                    >
                                        {{
                                            formatCurrency(
                                                analytics.activity.payroll
                                                    .year_total,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="rounded-[1.35rem] bg-muted p-4">
                                    <p
                                        class="text-xs tracking-[0.16em] text-muted-foreground uppercase"
                                    >
                                        Latest Payroll
                                    </p>
                                    <p
                                        class="mt-2 text-lg font-semibold text-foreground"
                                    >
                                        {{
                                            analytics.activity.payroll
                                                .latest_label ??
                                            'Not available yet'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <p
                                class="mt-4 text-sm leading-6 text-muted-foreground"
                            >
                                {{ payrollHighlight }}
                            </p>
                        </Card>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                    <Card
                        class="order-border bg-card/95 p-6 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    Due Soon
                                </p>
                                <h2
                                    class="mt-2 text-xl font-semibold tracking-tight text-foreground"
                                >
                                    The next obligations to watch
                                </h2>
                            </div>
                            <CalendarClock
                                class="mt-1 size-5 text-muted-foreground"
                            />
                        </div>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="expense in analytics.expenses.due_soon"
                                :key="expense.id"
                                class="grid gap-4 rounded-[1.4rem] border border-border p-4 md:grid-cols-[1.1fr_0.7fr_0.7fr]"
                            >
                                <div class="space-y-1">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <p
                                            class="font-semibold text-foreground"
                                        >
                                            {{ expense.name }}
                                        </p>
                                        <Badge
                                            variant="outline"
                                            class="capitalize"
                                        >
                                            {{ expense.type }}
                                        </Badge>
                                        <Badge
                                            variant="secondary"
                                            class="capitalize"
                                        >
                                            {{ expense.pay_in }}
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        Next due
                                        {{ formatDate(expense.next_due_date) }}
                                    </p>
                                </div>

                                <div class="space-y-1 text-sm">
                                    <p class="text-muted-foreground">
                                        Remaining balance
                                    </p>
                                    <p class="font-semibold text-foreground">
                                        {{
                                            formatCurrency(
                                                expense.remaining_balance,
                                            )
                                        }}
                                    </p>
                                </div>

                                <div class="space-y-1 text-sm">
                                    <p class="text-muted-foreground">Window</p>
                                    <p class="font-semibold text-foreground">
                                        {{
                                            expense.days_until_due === 0
                                                ? 'Due today'
                                                : `${expense.days_until_due} day${expense.days_until_due === 1 ? '' : 's'} left`
                                        }}
                                    </p>
                                </div>
                            </div>

                            <p
                                v-if="analytics.expenses.due_soon.length === 0"
                                class="rounded-[1.4rem] border border-dashed border-border px-4 py-8 text-sm text-muted-foreground"
                            >
                                No upcoming due dates with remaining balances
                                were found.
                            </p>
                        </div>
                    </Card>

                    <Card
                        class="border-border bg-card/95 p-6 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    Largest Balances Due
                                </p>
                                <h2
                                    class="mt-2 text-xl font-semibold tracking-tight text-foreground"
                                >
                                    Outstanding expense stack
                                </h2>
                            </div>
                            <ReceiptText
                                class="mt-1 size-5 text-muted-foreground"
                            />
                        </div>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="expense in analytics.expenses
                                    .top_outstanding"
                                :key="expense.id"
                                class="rounded-[1.4rem] bg-muted p-4"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="font-semibold text-foreground"
                                        >
                                            {{ expense.name }}
                                        </p>
                                        <p
                                            class="mt-1 text-sm text-muted-foreground capitalize"
                                        >
                                            {{ expense.type }} •
                                            {{ expense.pay_in }}
                                        </p>
                                    </div>
                                    <p
                                        class="text-right text-base font-semibold text-foreground"
                                    >
                                        {{
                                            formatCurrency(
                                                expense.remaining_balance,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="mt-4 h-2.5 overflow-hidden rounded-full bg-background"
                                >
                                    <div
                                        class="h-full rounded-full bg-linear-to-r from-chart-3 via-chart-4 to-destructive"
                                        :style="{
                                            width: `${Math.min((expense.paid_amount / Math.max(expense.total_amount, 1)) * 100, 100)}%`,
                                        }"
                                    />
                                </div>
                                <div
                                    class="mt-2 flex items-center justify-between text-xs text-muted-foreground"
                                >
                                    <span
                                        >Paid
                                        {{
                                            formatCurrency(expense.paid_amount)
                                        }}</span
                                    >
                                    <span
                                        >Total
                                        {{
                                            formatCurrency(expense.total_amount)
                                        }}</span
                                    >
                                </div>
                            </div>

                            <p
                                v-if="
                                    analytics.expenses.top_outstanding
                                        .length === 0
                                "
                                class="rounded-[1.4rem] border border-dashed border-border px-4 py-8 text-sm text-muted-foreground"
                            >
                                No outstanding expenses at the moment.
                            </p>
                        </div>
                    </Card>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <Card
                        class="border-border bg-card/95 p-6 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    Recent Activity
                                </p>
                                <h2
                                    class="mt-2 text-xl font-semibold tracking-tight text-foreground"
                                >
                                    Latest spend and income movement
                                </h2>
                            </div>
                            <Activity
                                class="mt-1 size-5 text-muted-foreground"
                            />
                        </div>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="entry in analytics.activity
                                    .recent_entries"
                                :key="entry.id"
                                class="grid gap-4 rounded-[1.4rem] border border-border p-4 md:grid-cols-[auto_1fr_auto] md:items-center"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl"
                                    :class="
                                        entry.entry_type === 'income'
                                            ? 'bg-chart-2/15 text-chart-2'
                                            : 'bg-chart-3/15 text-chart-3'
                                    "
                                >
                                    <ArrowUpRight
                                        v-if="entry.entry_type === 'income'"
                                        class="size-5"
                                    />
                                    <ArrowDownRight v-else class="size-5" />
                                </div>

                                <div class="space-y-1">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <p
                                            class="font-semibold text-foreground"
                                        >
                                            {{ entry.source_label }}
                                        </p>
                                        <Badge
                                            v-if="entry.is_payroll"
                                            variant="secondary"
                                        >
                                            Payroll
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ entry.detail_label }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{
                                            entry.transaction_date
                                                ? formatDate(
                                                      entry.transaction_date,
                                                  )
                                                : 'No date'
                                        }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p
                                        class="text-lg font-semibold"
                                        :class="
                                            entry.entry_type === 'income'
                                                ? 'text-chart-2'
                                                : 'text-chart-3'
                                        "
                                    >
                                        {{
                                            entry.entry_type === 'income'
                                                ? '+'
                                                : '-'
                                        }}{{ formatCurrency(entry.amount) }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs tracking-[0.16em] text-muted-foreground uppercase"
                                    >
                                        {{ entry.entry_type }}
                                    </p>
                                </div>
                            </div>

                            <p
                                v-if="
                                    analytics.activity.recent_entries.length ===
                                    0
                                "
                                class="rounded-[1.4rem] border border-dashed border-border px-4 py-8 text-sm text-muted-foreground"
                            >
                                No spend or income entries yet. Once
                                transactions are logged, the dashboard will
                                surface the latest movement here.
                            </p>
                        </div>
                    </Card>

                    <Card
                        class="border-border bg-card/95 p-6 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    Top Accounts
                                </p>
                                <h2
                                    class="mt-2 text-xl font-semibold tracking-tight text-foreground"
                                >
                                    Highest-balance accounts right now
                                </h2>
                            </div>
                            <Landmark
                                class="mt-1 size-5 text-muted-foreground"
                            />
                        </div>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="account in analytics.accounts
                                    .top_balances"
                                :key="account.id"
                                class="rounded-[1.4rem] bg-accent p-4 text-accent-foreground"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <p class="font-semibold">
                                                {{ account.account_name }}
                                            </p>
                                            <Badge
                                                v-if="account.is_default"
                                                variant="secondary"
                                                class="border border-border bg-background text-foreground hover:bg-background"
                                            >
                                                Default
                                            </Badge>
                                        </div>
                                        <p
                                            class="mt-1 text-sm text-muted-foreground capitalize"
                                        >
                                            {{ account.account_type
                                            }}<span v-if="account.bank_name">
                                                • {{ account.bank_name }}</span
                                            >
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-semibold">
                                            {{
                                                formatCurrency(account.balance)
                                            }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            {{
                                                account.is_active
                                                    ? 'Active'
                                                    : 'Inactive'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 h-2.5 overflow-hidden rounded-full bg-background"
                                >
                                    <div
                                        class="h-full rounded-full bg-linear-to-r"
                                        :class="balanceBarClass(account)"
                                        :style="{
                                            width: `${Math.min((account.balance / Math.max(analytics.overview.cash_on_hand, 1)) * 100, 100)}%`,
                                        }"
                                    />
                                </div>
                            </div>

                            <p
                                v-if="
                                    analytics.accounts.top_balances.length === 0
                                "
                                class="rounded-[1.4rem] border border-dashed border-border px-4 py-8 text-sm text-muted-foreground"
                            >
                                No account balances available yet.
                            </p>
                        </div>
                    </Card>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
