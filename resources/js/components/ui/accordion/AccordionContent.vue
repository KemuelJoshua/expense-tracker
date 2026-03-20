<script setup lang="ts">
import type { AccordionContentProps } from 'reka-ui';
import { AccordionContent } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<AccordionContentProps & { class?: string }>();
</script>

<template>
    <AccordionContent
        data-slot="accordion-content"
        :class="
            cn(
                'data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down overflow-hidden text-sm',
                props.class,
            )
        "
        v-bind="props"
    >
        <div class="pt-3 pb-1">
            <slot />
        </div>
    </AccordionContent>
</template>
