<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { useFilterForm } from './composables/useFilterForm';
import Filter from './partials/Filter.vue';
import Sheet from './partials/Sheet.vue';
import type { CutoffFilters, CutoffGroup, CutoffSummary } from './types/cutoff';

const props = defineProps<{
    cutoffs: Record<'first' | 'second', CutoffGroup>;
    filters: CutoffFilters;
    summary: CutoffSummary;
}>();

const { form } = useFilterForm();

form.date = props.filters.month;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cutoff',
        href: '/cutoff',
    },
];

const fetchCutoff = (): void => {
    router.get(
        '/cutoff',
        {
            month: form.date,
        },
        {
            only: ['cutoffs', 'filters', 'summary'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const formatCurrency = (value: number | string): string => {
    return Number(value).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>

<template>
    <Head title="Tracker | Cutoff" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full px-4 py-2">
            <div class="space-y-5">
                <div class="py-5">
                    <div class="mb-3">
                        <h2 class="text-sm font-medium text-foreground">
                            Filter
                        </h2>
                        <p class="text-xs text-muted-foreground sm:text-sm">
                            Choose a month to load the cutoff summary and grouped expenses.
                        </p>
                    </div>

                    <Filter v-model:date="form.date">
                        <Button class="w-full sm:w-auto" @click="fetchCutoff">
                            Load Cutoff
                        </Button>
                    </Filter>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-xl border bg-background p-4 shadow-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Selected Month
                        </p>
                        <p class="mt-2 text-lg font-semibold text-foreground">
                            {{ summary.month }}
                        </p>
                    </div>

                    <div class="rounded-xl border bg-background p-4 shadow-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Total Expenses
                        </p>
                        <p class="mt-2 text-lg font-semibold text-foreground">
                            {{ summary.count }}
                        </p>
                    </div>

                    <div class="rounded-xl border bg-background p-4 shadow-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                            Total Amount
                        </p>
                        <p class="mt-2 text-lg font-semibold text-foreground">
                            ₱{{ formatCurrency(summary.total_amount) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <Sheet :cutoff="cutoffs.first" />
                    <Sheet :cutoff="cutoffs.second" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
