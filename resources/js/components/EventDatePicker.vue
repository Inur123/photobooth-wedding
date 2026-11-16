<script setup lang="ts">
import {
    DateFormatter,
    getLocalTimeZone,
    parseDate,
    today,
    type DateValue,
} from '@internationalized/date';
import { CalendarDays } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

const props = defineProps<{
    id: string;
    invalid?: boolean;
    class?: string;
}>();

const model = defineModel<string>({ default: '' });
const open = ref(false);

const formatter = new DateFormatter('id-ID', {
    dateStyle: 'long',
});

const selectedDate = computed<DateValue | undefined>(() => {
    if (!model.value) return undefined;

    try {
        return parseDate(model.value);
    } catch {
        return undefined;
    }
});

const formattedDate = computed(() => {
    if (!selectedDate.value) return 'Pilih tanggal';

    return formatter.format(selectedDate.value.toDate(getLocalTimeZone()));
});

function selectDate(value: DateValue | DateValue[] | undefined) {
    if (!value || Array.isArray(value)) return;

    model.value = value.toString();
    open.value = false;
}

function clearDate() {
    model.value = '';
    open.value = false;
}
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                :id="id"
                type="button"
                variant="outline"
                :aria-invalid="invalid || undefined"
                :class="
                    cn(
                        'h-11 w-full justify-between bg-background px-3 text-left font-normal',
                        'data-[state=open]:border-ring data-[state=open]:ring-1 data-[state=open]:ring-ring',
                        !selectedDate && 'text-muted-foreground',
                        props.class,
                    )
                "
            >
                <span class="truncate">{{ formattedDate }}</span>
                <CalendarDays
                    class="size-4 shrink-0 text-muted-foreground"
                    aria-hidden="true"
                />
            </Button>
        </PopoverTrigger>
        <PopoverContent
            align="start"
            class="w-(--reka-popover-trigger-width) p-2"
        >
            <Calendar
                :model-value="selectedDate"
                :placeholder="selectedDate ?? today(getLocalTimeZone())"
                locale="id-ID"
                layout="month-and-year"
                class="w-full p-0"
                aria-label="Tanggal acara"
                initial-focus
                @update:model-value="selectDate"
            />
            <div v-if="selectedDate" class="border-t p-1">
                <Button
                    type="button"
                    variant="ghost"
                    class="h-11 w-full"
                    @click="clearDate"
                >
                    Hapus tanggal
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>
