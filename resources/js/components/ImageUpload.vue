<script setup lang="ts">
import { ImagePlus, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{ existingUrl?: string | null; label: string }>();
const file = defineModel<File | null>({ default: null });
const removed = defineModel<boolean>('removed', { default: false });
const input = ref<HTMLInputElement | null>(null);
const localUrl = ref<string | null>(null);
const error = ref('');
const preview = computed(
    () => localUrl.value ?? (removed.value ? null : props.existingUrl),
);

watch(file, (value) => {
    if (localUrl.value) URL.revokeObjectURL(localUrl.value);
    localUrl.value = value ? URL.createObjectURL(value) : null;
    if (!value && input.value) input.value.value = '';
});
onBeforeUnmount(() => {
    if (localUrl.value) URL.revokeObjectURL(localUrl.value);
});

function select(event: Event) {
    const selected = (event.target as HTMLInputElement).files?.[0];
    if (!selected) return;
    error.value = '';
    if (
        !['image/jpeg', 'image/png', 'image/webp'].includes(selected.type) ||
        selected.size > 8 * 1024 * 1024
    ) {
        error.value = 'Gunakan JPG, PNG, atau WebP, maksimal 8 MB.';
        if (input.value) input.value.value = '';
        return;
    }
    file.value = selected;
    removed.value = false;
}
function remove() {
    file.value = null;
    removed.value = true;
    error.value = '';
}
</script>

<template>
    <div class="space-y-3">
        <input
            ref="input"
            type="file"
            class="hidden"
            accept="image/jpeg,image/png,image/webp"
            :aria-label="label"
            @change="select"
        />
        <div
            class="flex flex-wrap items-center gap-4 rounded-lg border border-border bg-muted/20 p-3.5"
        >
            <img
                v-if="preview"
                :src="preview"
                :alt="`Pratinjau ${label.toLowerCase()}`"
                width="80"
                height="80"
                class="size-20 shrink-0 rounded-md border border-border bg-background object-cover"
            />
            <span
                v-else
                class="flex size-20 shrink-0 items-center justify-center rounded-md border border-dashed border-border bg-background text-muted-foreground"
                ><ImagePlus class="size-6" aria-hidden="true"
            /></span>
            <div class="flex min-w-0 flex-1 flex-col items-start gap-1.5">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="input?.click()"
                    >{{ preview ? 'Ganti foto' : 'Pilih foto' }}</Button
                >
                <p class="text-xs text-muted-foreground">
                    JPG, PNG, atau WebP. Maksimal 8 MB.
                </p>
            </div>
            <Button
                v-if="preview"
                type="button"
                variant="ghost"
                size="icon"
                class="shrink-0"
                :aria-label="`Hapus ${label.toLowerCase()}`"
                @click="remove"
                ><X class="size-4"
            /></Button>
        </div>
        <p v-if="error" role="alert" class="text-sm text-destructive">
            {{ error }}
        </p>
    </div>
</template>
