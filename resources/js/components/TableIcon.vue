<script setup lang="ts">
defineProps<{
    text?: string;
    image?: string;
}>();
</script>

<template>
    <!-- If text exists -->
    <div
        v-if="text"
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-sm font-semibold text-primary border-2"
    >
        {{ text.charAt(0).toUpperCase() }}
    </div>

    <!-- Else show image -->
    <img
        v-else
        :src="image || '/default-avatar.png'"
        alt="avatar"
        class="h-10 w-10 rounded-lg object-cover"
    />
</template>