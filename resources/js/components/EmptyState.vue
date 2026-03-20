<script setup lang="ts">
defineProps<{
    label?: string;
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center gap-2 py-10 text-center bg-accent"
    >
        <div
            v-if="label"
            class="rounded-full border border-dashed border-border/70 px-4 py-1 text-xs font-semibold tracking-[0.2em] text-muted-foreground uppercase"
        >
            {{ label }}
        </div>

        <p class="text-base font-semibold text-foreground">
            {{ title }}
        </p>

        <p v-if="description" class="max-w-sm text-sm text-muted-foreground">
            {{ description }}
        </p>

        <div v-if="$slots.default" class="mt-3">
            <slot />
        </div>
    </div>
</template>
