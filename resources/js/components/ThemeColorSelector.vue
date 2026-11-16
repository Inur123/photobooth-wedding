<script setup lang="ts">
import { Lock, RotateCcw } from '@lucide/vue';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';

export type ThemeOption = {
    id: string;
    name: string;
    mood: string | null;
    primary_color: string;
    accent_color: string;
    neutral_color: string;
};

export type CustomColorsValue = {
    primary: string;
    accent: string;
    neutral: string;
};

const DEFAULT_COLORS: CustomColorsValue = {
    primary: '#1d1c29',
    accent: '#ff5d6c',
    neutral: '#f8f6f2',
};

const props = withDefaults(
    defineProps<{
        idPrefix?: string;
        allowCustomTheme: boolean;
        themes: ThemeOption[];
        themeId: string;
        useCustomColors: boolean;
        customColors: CustomColorsValue;
        themeError?: string;
        customColorsErrors?: Partial<
            Record<keyof CustomColorsValue, string | undefined>
        >;
        customColorsRootError?: string;
    }>(),
    {
        idPrefix: 'theme-selector',
    },
);

const emit = defineEmits<{
    'update:themeId': [value: string];
    'update:useCustomColors': [value: boolean];
    'update:customColors': [value: CustomColorsValue];
}>();

const selectedTheme = computed(() =>
    props.themes.find((t) => String(t.id) === String(props.themeId)),
);

const basePalette = computed<CustomColorsValue>(() => {
    if (selectedTheme.value) {
        return {
            primary: selectedTheme.value.primary_color,
            accent: selectedTheme.value.accent_color,
            neutral: selectedTheme.value.neutral_color,
        };
    }
    return { ...DEFAULT_COLORS };
});

const previewColors = computed<CustomColorsValue>(() =>
    props.allowCustomTheme && props.useCustomColors
        ? props.customColors
        : props.allowCustomTheme
          ? basePalette.value
          : DEFAULT_COLORS,
);

const colorFields: Array<{
    key: keyof CustomColorsValue;
    label: string;
    hint: string;
}> = [
    {
        key: 'primary',
        label: 'Warna Utama',
        hint: 'Teks judul & tombol primer',
    },
    {
        key: 'accent',
        label: 'Warna Aksen',
        hint: 'Sorotan, ikon & aksen',
    },
    {
        key: 'neutral',
        label: 'Warna Latar',
        hint: 'Latar halaman & kartu tamu',
    },
];

function handleThemeChange(value: unknown) {
    const nextId = String(value ?? 'none');
    emit('update:themeId', nextId);

    if (!props.useCustomColors) {
        const nextTheme = props.themes.find((t) => String(t.id) === nextId);
        emit(
            'update:customColors',
            nextTheme
                ? {
                      primary: nextTheme.primary_color,
                      accent: nextTheme.accent_color,
                      neutral: nextTheme.neutral_color,
                  }
                : { ...DEFAULT_COLORS },
        );
    }
}

function handleCustomToggle(checked: boolean) {
    emit('update:useCustomColors', checked);
    if (
        checked &&
        (!props.customColors.primary ||
            !props.customColors.accent ||
            !props.customColors.neutral)
    ) {
        emit('update:customColors', { ...basePalette.value });
    }
}

function updateColorField(key: keyof CustomColorsValue, value: string) {
    emit('update:customColors', {
        ...props.customColors,
        [key]: value,
    });
}

function resetToThemePalette() {
    emit('update:customColors', { ...basePalette.value });
}
</script>

<template>
    <div class="space-y-3">
        <div class="grid gap-1.5">
            <Label :for="`${idPrefix}-preset`">Tema Warna Acara</Label>

            <template v-if="allowCustomTheme">
                <Select
                    :model-value="themeId"
                    @update:model-value="handleThemeChange"
                >
                    <SelectTrigger :id="`${idPrefix}-preset`">
                        <SelectValue placeholder="Pilih tema preset..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">
                            Tema Bawaan (Titip Momen)
                        </SelectItem>
                        <SelectItem
                            v-for="t in themes"
                            :key="t.id"
                            :value="String(t.id)"
                        >
                            <span class="flex items-center gap-2">
                                <span class="flex items-center -space-x-1">
                                    <span
                                        class="size-3 rounded-full border border-border"
                                        :style="{
                                            backgroundColor: t.primary_color,
                                        }"
                                    />
                                    <span
                                        class="size-3 rounded-full border border-border"
                                        :style="{
                                            backgroundColor: t.accent_color,
                                        }"
                                    />
                                    <span
                                        class="size-3 rounded-full border border-border"
                                        :style="{
                                            backgroundColor: t.neutral_color,
                                        }"
                                    />
                                </span>
                                <span>{{ t.name }}</span>
                                <span
                                    v-if="t.mood"
                                    class="text-xs text-muted-foreground"
                                >
                                    ({{ t.mood }})
                                </span>
                            </span>
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="themeError" />
            </template>

            <div
                v-else
                class="flex items-center gap-2 rounded-md border border-input bg-muted/30 px-3 py-2 text-xs text-muted-foreground"
            >
                <Lock class="size-3.5 shrink-0 text-muted-foreground" />
                <span>
                    Tema Bawaan (Tema &amp; warna kustom aktif di paket Plus
                    &amp; Full)
                </span>
            </div>
        </div>

        <div
            v-if="allowCustomTheme"
            class="space-y-3 rounded-lg border border-border bg-muted/20 p-3.5"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <Label
                        :for="`${idPrefix}-custom-toggle`"
                        class="text-xs font-semibold text-foreground"
                    >
                        Kustomisasi Warna Manual
                    </Label>
                    <p class="text-[11px] text-muted-foreground">
                        Atur palet warna sendiri di luar pilihan preset.
                    </p>
                </div>
                <Switch
                    :id="`${idPrefix}-custom-toggle`"
                    :model-value="useCustomColors"
                    @update:model-value="handleCustomToggle"
                />
            </div>

            <div v-if="useCustomColors" class="space-y-3 border-t pt-3">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div
                        v-for="field in colorFields"
                        :key="field.key"
                        class="space-y-1.5"
                    >
                        <Label
                            :for="`${idPrefix}-${field.key}-picker`"
                            class="text-xs font-medium text-foreground"
                        >
                            {{ field.label }}
                        </Label>
                        <div class="flex items-center gap-2">
                            <input
                                :id="`${idPrefix}-${field.key}-picker`"
                                type="color"
                                :value="customColors[field.key]"
                                class="h-9 w-11 shrink-0 cursor-pointer rounded-md border border-input bg-background p-0.5"
                                @input="
                                    updateColorField(
                                        field.key,
                                        ($event.target as HTMLInputElement)
                                            .value,
                                    )
                                "
                            />
                            <Input
                                :id="`${idPrefix}-${field.key}-hex`"
                                :model-value="customColors[field.key]"
                                maxlength="7"
                                placeholder="#000000"
                                class="font-mono text-xs uppercase"
                                @update:model-value="
                                    updateColorField(
                                        field.key,
                                        String($event ?? ''),
                                    )
                                "
                            />
                        </div>
                        <p class="text-[11px] text-muted-foreground">
                            {{ field.hint }}
                        </p>
                        <InputError
                            :message="customColorsErrors?.[field.key]"
                        />
                    </div>
                </div>

                <InputError :message="customColorsRootError" />

                <div
                    class="flex flex-wrap items-center justify-between gap-2 rounded-md border border-border px-3 py-2"
                    :style="{ backgroundColor: previewColors.neutral }"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-block size-3.5 rounded-full border border-black/15"
                            :style="{ backgroundColor: previewColors.accent }"
                        />
                        <span
                            class="text-xs font-semibold"
                            :style="{ color: previewColors.primary }"
                        >
                            Pratinjau Warna Halaman Tamu
                        </span>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded px-2 py-1 text-[11px] font-medium transition-opacity hover:opacity-85"
                        :style="{
                            backgroundColor: previewColors.accent,
                            color: '#ffffff',
                        }"
                        @click="resetToThemePalette"
                    >
                        <RotateCcw class="size-3" />
                        Ikuti Preset
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
