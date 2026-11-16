<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    defaultValue?: string | number;
    modelValue?: string | number;
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void;
    (e: 'change', payload: Event): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});
</script>

<template>
    <textarea
        v-model="modelValue"
        data-slot="textarea"
        :class="
            cn(
                'flex field-sizing-content min-h-16 w-full rounded-md border border-border bg-background px-3 py-2 text-base transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-primary focus-visible:ring-0 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive md:text-sm',
                props.class,
            )
        "
        @change="emits('change', $event)"
    />
</template>
