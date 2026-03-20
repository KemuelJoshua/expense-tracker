<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage();
const appName = page.props.name;

const highlights = [
    {
        title: 'Track every bill',
        description: 'Monitor recurring costs, due dates, and unpaid balances before they pile up.',
    },
    {
        title: 'Keep cutoff-ready records',
        description: 'Group expenses around payout cycles so first-half and second-half obligations stay visible.',
    },
    {
        title: 'Review faster',
        description: 'Open one workspace for references, attachments, schedules, and payment history.',
    },
];

const focusAreas = [
    'Recurring subscriptions and utilities',
    'Loan repayments and due dates',
    'Reference tracking for invoices',
    'Payment cutoff planning',
];
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-background text-foreground">
        <div class="mx-auto flex min-h-screen w-full max-w-7xl flex-col p-6 md:p-10">
            <header class="flex items-center justify-between gap-4">
                <Link :href="$page.props.auth.user ? dashboard() : login()" class="flex items-center gap-3 font-medium">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-sm">
                        <AppLogoIcon class="size-6 fill-current" />
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-sm font-semibold">{{ appName }}</p>
                        <p class="text-xs text-muted-foreground">Expense tracking workspace</p>
                    </div>
                </Link>

                <nav class="flex items-center gap-3">
                    <Button v-if="$page.props.auth.user" as-child>
                        <Link :href="dashboard()">Open dashboard</Link>
                    </Button>

                    <template v-else>
                        <Button variant="ghost" as-child>
                            <Link :href="login()">Log in</Link>
                        </Button>

                        <Button v-if="canRegister" variant="outline" as-child>
                            <Link :href="register()">Create account</Link>
                        </Button>
                    </template>
                </nav>
            </header>

            <main class="flex flex-1 items-center py-10 md:py-16">
                <div class="w-full space-y-14">
                    <section
                        class="relative overflow-hidden rounded-[2rem] bg-linear-to-br from-primary via-primary to-chart-3 px-8 py-10 text-primary-foreground md:px-12 md:py-14"
                    >
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.24),transparent_32%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.14),transparent_30%)]" />

                        <div class="relative grid gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
                            <div class="max-w-3xl space-y-6">
                                <Badge variant="secondary" class="bg-white/14 text-primary-foreground hover:bg-white/14">
                                    Expense control
                                </Badge>

                                <div class="space-y-4">
                                    <h1 class="max-w-3xl text-4xl font-semibold tracking-tight text-balance md:text-6xl">
                                        Stay ahead of expenses, cutoffs, and recurring payments.
                                    </h1>
                                    <p class="max-w-2xl text-base leading-7 text-primary-foreground/80 md:text-lg">
                                        Use {{ appName }} to organize monthly obligations, track outstanding balances,
                                        and keep payment timing aligned with your real payout cycle.
                                    </p>
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <Button
                                        as-child
                                        size="lg"
                                        class="bg-background text-foreground hover:bg-background/90"
                                    >
                                        <Link :href="$page.props.auth.user ? dashboard() : login()">
                                            {{ $page.props.auth.user ? 'Go to dashboard' : 'Start tracking' }}
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="!$page.props.auth.user && canRegister"
                                        as-child
                                        size="lg"
                                        variant="secondary"
                                        class="bg-white/14 text-primary-foreground hover:bg-white/20"
                                    >
                                        <Link :href="register()">Create a workspace</Link>
                                    </Button>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                                <div
                                    v-for="highlight in highlights"
                                    :key="highlight.title"
                                    class="border-l-2 border-white/30 pl-4"
                                >
                                    <p class="text-sm font-semibold text-primary-foreground">
                                        {{ highlight.title }}
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-primary-foreground/75">
                                        {{ highlight.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="grid gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-start">
                        <div class="space-y-5">
                            <div class="space-y-3">
                                <Badge variant="outline">Built for routine tracking</Badge>
                                <h2 class="text-3xl font-semibold tracking-tight">
                                    What this helps you manage
                                </h2>
                                <p class="max-w-xl text-base leading-7 text-muted-foreground">
                                    Keep the page practical: recurring utilities, loans, invoice references, and
                                    cutoff-based planning without digging through spreadsheets.
                                </p>
                            </div>

                            <div class="grid gap-3">
                                <div
                                    v-for="area in focusAreas"
                                    :key="area"
                                    class="flex items-center gap-3 border-b border-border pb-3"
                                >
                                    <div class="h-2.5 w-2.5 rounded-full bg-primary" />
                                    <p class="text-sm text-foreground/85">{{ area }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-3">
                            <div class="bg-secondary/60 p-5 md:col-span-1">
                                <p class="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                    1. Record
                                </p>
                                <p class="mt-3 text-sm leading-6 text-foreground/80">
                                    Save the amount, reference number, due date, and attachment as soon as an expense
                                    appears.
                                </p>
                            </div>

                            <div class="bg-muted p-5 md:col-span-1">
                                <p class="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                    2. Schedule
                                </p>
                                <p class="mt-3 text-sm leading-6 text-foreground/80">
                                    Assign each item to first-half or second-half payment timing so cash flow stays
                                    visible.
                                </p>
                            </div>

                            <div class="bg-accent p-5 md:col-span-1">
                                <p class="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                    3. Review
                                </p>
                                <p class="mt-3 text-sm leading-6 text-foreground/80">
                                    Compare paid and total amounts, then keep recurring items visible across months.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>
