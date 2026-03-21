<script setup lang="ts">
import { Calendar } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    date: string;
}>();

const emit = defineEmits<{
    (e: 'update:date', value: string | number): void;
}>();
</script>

<template>
    <div class="flex items-end gap-3">
        <div class="flex-1 space-y-1">
            <label class="text-xs font-medium text-muted-foreground">
                Month
            </label>

            <div class="relative">
                <Calendar
                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <div class="flex gap-3">
                    <Input
                        type="month"
                        :model-value="props.date"
                        @update:model-value="emit('update:date', $event)"
                        class="h-9 max-w-xl pl-10 text-sm"
                    />
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
