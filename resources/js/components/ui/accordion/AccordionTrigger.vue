<script setup lang="ts">
import type { AccordionTriggerProps } from 'reka-ui';
import { AccordionHeader, AccordionTrigger } from 'reka-ui';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps<AccordionTriggerProps & { class?: string }>();
</script>

<template>
    <AccordionHeader class="flex">
        <AccordionTrigger
            data-slot="accordion-trigger"
            :class="
                cn(
                    'focus-visible:border-ring focus-visible:ring-ring/50 flex flex-1 items-center justify-between gap-4 rounded-md py-3 text-left text-sm font-medium transition-all outline-none hover:underline focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50 [&[data-state=open]>svg]:rotate-180',
                    props.class,
                )
            "
            v-bind="props"
        >
            <slot />
            <ChevronDown class="size-4 shrink-0 text-muted-foreground transition-transform duration-200" />
        </AccordionTrigger>
    </AccordionHeader>
</template>
