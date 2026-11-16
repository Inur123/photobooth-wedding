<script setup lang="ts">
import { Check, ChevronDown, Search, X } from '@lucide/vue';
import { computed, nextTick, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export type SearchableSelectOption = {
    value: string;
    label: string;
    sublabel?: string;
};

const props = withDefaults(
    defineProps<{
        id?: string;
        options: SearchableSelectOption[];
        placeholder?: string;
        searchPlaceholder?: string;
        emptyText?: string;
        disabled?: boolean;
        invalid?: boolean;
        class?: string;
    }>(),
    {
        id: undefined,
        placeholder: 'Pilih opsi...',
        searchPlaceholder: 'Cari...',
        emptyText: 'Tidak ditemukan hasil yang cocok.',
        disabled: false,
        invalid: false,
        class: undefined,
    },
);

const model = defineModel<string>({ default: '' });

const open = ref(false);
const searchQuery = ref('');
const searchInputRef = ref<HTMLInputElement | null>(null);

const selectedOption = computed(() => {
    return props.options.find((opt) => opt.value === model.value);
});

const filteredOptions = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) {
        return props.options;
    }
    return props.options.filter((opt) => {
        const matchLabel = opt.label.toLowerCase().includes(q);
        const matchSub = opt.sublabel
            ? opt.sublabel.toLowerCase().includes(q)
            : false;
        return matchLabel || matchSub;
    });
});

function selectOption(val: string) {
    model.value = val;
    open.value = false;
    searchQuery.value = '';
}

watch(open, (isOpen) => {
    if (isOpen) {
        searchQuery.value = '';
        nextTick(() => {
            searchInputRef.value?.focus();
        });
    }
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                :id="id"
                type="button"
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                :disabled="disabled"
                :class="
                    cn(
                        'flex h-11 w-full min-w-0 items-center justify-between gap-2 rounded-md border border-border bg-background px-3 py-2 text-left text-sm font-normal transition-colors outline-none hover:bg-background data-[placeholder]:text-muted-foreground',
                        'focus-visible:border-ring focus-visible:ring-1 focus-visible:ring-ring',
                        invalid && 'border-destructive ring-1 ring-destructive',
                        disabled && 'cursor-not-allowed opacity-50',
                        props.class,
                    )
                "
            >
                <span v-if="selectedOption" class="truncate text-foreground">
                    {{ selectedOption.label }}
                    <span
                        v-if="selectedOption.sublabel"
                        class="text-xs text-muted-foreground"
                    >
                        ({{ selectedOption.sublabel }})
                    </span>
                </span>
                <span v-else class="truncate text-muted-foreground">
                    {{ placeholder }}
                </span>

                <ChevronDown
                    class="size-4 shrink-0 opacity-50 transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"
                    aria-hidden="true"
                />
            </Button>
        </PopoverTrigger>

        <PopoverContent
            align="start"
            class="w-[--reka-popover-trigger-width] min-w-[280px] p-0 shadow-md"
        >
            <!-- Search input bar -->
            <div class="flex items-center border-b border-border px-3 py-2.5">
                <Search
                    class="mr-2 size-4 shrink-0 text-muted-foreground"
                    aria-hidden="true"
                />
                <input
                    ref="searchInputRef"
                    v-model="searchQuery"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="flex h-7 w-full rounded-md bg-transparent text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                    @keydown.esc="open = false"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    class="ml-1 rounded p-0.5 text-muted-foreground hover:bg-muted hover:text-foreground"
                    title="Hapus pencarian"
                    @click="
                        searchQuery = '';
                        searchInputRef?.focus();
                    "
                >
                    <X class="size-3.5" aria-hidden="true" />
                </button>
            </div>

            <!-- Options list -->
            <div class="max-h-60 overflow-y-auto p-1">
                <div
                    v-if="filteredOptions.length === 0"
                    class="py-6 text-center text-xs text-muted-foreground"
                >
                    {{ emptyText }}
                </div>
                <button
                    v-for="opt in filteredOptions"
                    :key="opt.value"
                    type="button"
                    class="flex w-full items-center justify-between gap-2 rounded-md px-2.5 py-2 text-left text-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                    :class="
                        opt.value === model
                            ? 'bg-accent/60 font-medium text-foreground'
                            : 'text-foreground/90'
                    "
                    @click="selectOption(opt.value)"
                >
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">
                            {{ opt.label }}
                        </p>
                        <p
                            v-if="opt.sublabel"
                            class="truncate text-xs text-muted-foreground"
                        >
                            {{ opt.sublabel }}
                        </p>
                    </div>
                    <Check
                        v-if="opt.value === model"
                        class="size-4 shrink-0 text-primary"
                        aria-hidden="true"
                    />
                </button>
            </div>
        </PopoverContent>
    </Popover>
</template>
