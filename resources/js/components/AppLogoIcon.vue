<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 128 128"
        :class="className"
        v-bind="$attrs"
    >
        <!-- rounded app box -->
        <rect
            x="6"
            y="6"
            width="116"
            height="116"
            rx="24"
            fill="currentColor"
            opacity="0.12"
        />

        <!-- pig -->
        <g>
            <!-- body -->
            <path
                fill="currentColor"
                d="M92 54c8 5 13 14 13 26 0 20-16 34-38 34-7 0-13-1-18-3-2 3-5 5-9 5-4 0-7-1-10-3 2-5 4-10 6-15-8-5-13-13-13-24 0-19 16-33 37-33 4 0 9 1 13 2l8-8c1-1 2-2 4-2h13c2 0 4 2 4 4v12Z"
            />

            <!-- ear cut -->
            <path
                fill="white"
                d="M74 42l9-7h-7l-8 8 6-1Z"
                opacity="0.95"
            />

            <!-- snout -->
            <ellipse
                cx="43"
                cy="68"
                rx="20"
                ry="16"
                fill="white"
            />

            <!-- nostrils -->
            <ellipse cx="36" cy="68" rx="4" ry="6" fill="currentColor" />
            <ellipse cx="49" cy="68" rx="4" ry="6" fill="currentColor" />

            <!-- eye -->
            <circle cx="60" cy="54" r="5" fill="white" />

            <!-- belly separation -->
            <path
                d="M73 85c0 9-6 17-15 20"
                stroke="white"
                stroke-width="6"
                stroke-linecap="round"
                fill="none"
            />

            <!-- feet -->
            <path
                fill="currentColor"
                d="M39 98c-2 7-8 12-16 11 1-8 4-13 10-16l6 5Zm27 5c-1 8-8 13-17 11 1-8 5-14 11-18l6 7Zm31-3c-1 7-7 11-15 10 1-7 4-12 9-15l6 5Z"
            />

            <!-- tail -->
            <path
                d="M101 59c9-2 15 4 15 11 0 8-6 13-13 13-7 0-11-5-11-10 0-5 4-8 8-8 5 0 7 4 7 7"
                stroke="currentColor"
                stroke-width="6"
                stroke-linecap="round"
                stroke-linejoin="round"
                fill="none"
            />

            <!-- coin -->
            <g transform="translate(30 90) rotate(-16)">
                <circle cx="0" cy="0" r="13" fill="#FACC15" />
                <circle cx="0" cy="0" r="10" fill="#FDE047" />
                <path
                    d="M0-6v12M4-4c-1-1-2-2-4-2-2 0-4 1-4 3s2 3 4 3 4 1 4 3-2 3-4 3c-2 0-4-1-5-2"
                    stroke="#A16207"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    fill="none"
                />
            </g>
        </g>
    </svg>
</template>