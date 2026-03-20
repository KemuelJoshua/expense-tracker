<script setup lang="ts">
import type { AccordionItemProps } from 'reka-ui';
import { AccordionItem } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<AccordionItemProps & { class?: string }>();
</script>

<template>
    <AccordionItem
        data-slot="accordion-item"
        :class="cn('border-b', props.class)"
        v-bind="props"
    >
        <slot />
    </AccordionItem>
</template>
