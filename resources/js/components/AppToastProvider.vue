<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Toaster } from '@/components/ui/sonner';

type PageProps = {
    flash?: {
        success?: string | null;
    };
};

const page = usePage<PageProps>();

const flashSuccess = computed(() => {
    const props = page.props as PageProps | undefined;

    return props?.flash?.success ?? null;
});

watch(
    flashSuccess,
    (message, previousMessage) => {
        if (message && message !== previousMessage) {
            toast.success(message);
        }
    },
    { immediate: true },
);
</script>

<template>
    <slot />
    <Toaster rich-colors close-button position="top-right" />
</template>
