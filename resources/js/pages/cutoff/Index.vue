<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import Filter from './partials/Filter.vue';
import Sheet from './partials/Sheet.vue';
import { useFilterForm } from './composables/useFilterForm';

import { Button } from '@/components/ui/button';

const { form } = useFilterForm();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cutoff',
        href: '/cutoff',
    },
];

const fetchCutoff = () => {
    console.log(form.date);
    console.log(form);
};
</script>

<template>
    <Head title="Tracker | Cutoff" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full px-4 py-2">
            
            <div class="space-y-5">
                <!-- Filter -->
                <div class="py-5">
                    <div class="mb-3">
                        <h2 class="text-sm font-medium text-foreground">
                            Filter
                        </h2>
                        <p class="text-xs text-muted-foreground sm:text-sm">
                            Choose a date to load the cutoff details.
                        </p>
                    </div>

                    <Filter v-model:date="form.date" >
                        <Button class="w-full sm:w-auto" @click="fetchCutoff">
                            Enter Actual Salary
                        </Button>
                    </Filter>
                </div>

                <!-- Content -->
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <Sheet/>

                    <div class="flex h-32 items-center justify-center rounded-xl border border-dashed bg-muted/20 p-4">
                        <div class="text-center">
                            <p class="text-sm font-medium text-foreground">
                                Content 2
                            </p>
                            <p class="text-xs text-muted-foreground sm:text-sm">
                                Your second cutoff panel goes here.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
