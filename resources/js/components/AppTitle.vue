<script setup lang="ts">
import { CalendarDays } from 'lucide-vue-next';

const props = defineProps<{
    title?: string
    subtitle?: string
    description?: string
}>()

</script>

<template>
    <!-- Header -->
    <div
        class="flex flex-col gap-3 border-b pb-4 sm:flex-row sm:items-end sm:justify-between"
    >
        <div class="min-w-0">
            <div class="mb-1 flex items-center gap-2 text-muted-foreground">
                <CalendarDays class="h-4 w-4" />
                <span class="text-xs tracking-wide uppercase sm:text-sm"
                    >{{ props.title }}</span
                >
            </div>

            <h1 class="text-xl font-semibold tracking-tight sm:text-2xl">
                {{ props.subtitle }}
            </h1>

            <p class="mt-1 max-w-2xl text-sm text-muted-foreground">
                {{ props.description }}
            </p>
        </div>

        <!-- button slots --> 
        <slot></slot>

    </div>
</template>
