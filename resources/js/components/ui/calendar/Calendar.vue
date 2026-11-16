<script lang="ts" setup>
import type { CalendarRootEmits, CalendarRootProps, DateValue } from "reka-ui"
import type { HTMLAttributes, Ref } from "vue"
import type { LayoutTypes } from "."
import { getLocalTimeZone, today } from "@internationalized/date"
import { createReusableTemplate, reactiveOmit, useVModel } from "@vueuse/core"
import { cn } from "cn"
import { CalendarRoot, useDateFormatter, useForwardPropsEmits } from "reka-ui"
import { createYear, createYearRange, toDate } from "reka-ui/date"
import { computed, toRaw } from "vue"
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select'
import { CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell, CalendarHeader, CalendarHeading, CalendarNextButton, CalendarPrevButton } from "."

defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(defineProps<CalendarRootProps & { class?: HTMLAttributes["class"], layout?: LayoutTypes, yearRange?: DateValue[] }>(), {
  modelValue: undefined,
  layout: undefined,
})
const emits = defineEmits<CalendarRootEmits>()

const delegatedProps = reactiveOmit(props, "class", "layout", "placeholder")

const placeholder = useVModel(props, "placeholder", emits, {
  passive: true,
  defaultValue: props.defaultPlaceholder ?? today(getLocalTimeZone()),
}) as Ref<DateValue>

const formatter = useDateFormatter(props.locale ?? "en")

const yearRange = computed(() => {
  return props.yearRange ?? createYearRange({
    start: props?.minValue ?? (toRaw(props.placeholder) ?? props.defaultPlaceholder ?? today(getLocalTimeZone()))
      .cycle("year", -100),

    end: props?.maxValue ?? (toRaw(props.placeholder) ?? props.defaultPlaceholder ?? today(getLocalTimeZone()))
      .cycle("year", 10),
  })
})

const [DefineMonthTemplate, ReuseMonthTemplate] = createReusableTemplate<{ date: DateValue }>()
const [DefineYearTemplate, ReuseYearTemplate] = createReusableTemplate<{ date: DateValue }>()

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <DefineMonthTemplate v-slot="{ date }">
    <div class="**:data-[slot=native-select-icon]:right-1">
      <NativeSelect
        class="h-11 w-16 border-0 bg-transparent pr-6 pl-2 text-sm shadow-none hover:bg-accent focus-visible:border-transparent focus-visible:ring-0"
        :model-value="date.month"
        @change="(e: Event) => {
          placeholder = placeholder.set({
            month: Number((e?.target as any)?.value),
          })
        }"
      >
        <NativeSelectOption v-for="(month) in createYear({ dateObj: date })" :key="month.toString()" :value="month.month" :selected="date.month === month.month">
          {{ formatter.custom(toDate(month), { month: 'short' }) }}
        </NativeSelectOption>
      </NativeSelect>
    </div>
  </DefineMonthTemplate>

  <DefineYearTemplate v-slot="{ date }">
    <div class="**:data-[slot=native-select-icon]:right-1">
      <NativeSelect
        class="h-11 w-20 border-0 bg-transparent pr-6 pl-2 text-sm shadow-none hover:bg-accent focus-visible:border-transparent focus-visible:ring-0"
        :model-value="date.year"
        @change="(e: Event) => {
          placeholder = placeholder.set({
            year: Number((e?.target as any)?.value),
          })
        }"
      >
        <NativeSelectOption v-for="(year) in yearRange" :key="year.toString()" :value="year.year" :selected="date.year === year.year">
          {{ formatter.custom(toDate(year), { year: 'numeric' }) }}
        </NativeSelectOption>
      </NativeSelect>
    </div>
  </DefineYearTemplate>

  <CalendarRoot
    v-slot="{ grid, weekDays, date }"
    v-bind="{ ...forwarded, ...$attrs }"
    v-model:placeholder="placeholder"
    data-slot="calendar"
    :class="cn('p-1', props.class)"
  >
    <CalendarHeader class="grid grid-cols-7 items-center gap-0 px-0 pt-0">
      <CalendarPrevButton class="justify-self-center">
        <slot name="calendar-prev-icon" />
      </CalendarPrevButton>

      <div class="col-span-5 flex min-w-0 items-center justify-center">
        <slot name="calendar-heading" :date="date" :month="ReuseMonthTemplate" :year="ReuseYearTemplate">
          <template v-if="layout === 'month-and-year'">
            <div class="flex items-center justify-center gap-1">
              <ReuseMonthTemplate :date="date" />
              <ReuseYearTemplate :date="date" />
            </div>
          </template>
          <template v-else-if="layout === 'month-only'">
            <div class="flex items-center justify-center gap-1">
              <ReuseMonthTemplate :date="date" />
              {{ formatter.custom(toDate(date), { year: 'numeric' }) }}
            </div>
          </template>
          <template v-else-if="layout === 'year-only'">
            <div class="flex items-center justify-center gap-1">
              {{ formatter.custom(toDate(date), { month: 'short' }) }}
              <ReuseYearTemplate :date="date" />
            </div>
          </template>
          <template v-else>
            <CalendarHeading />
          </template>
        </slot>
      </div>

      <CalendarNextButton class="justify-self-center">
        <slot name="calendar-next-icon" />
      </CalendarNextButton>
    </CalendarHeader>

    <div class="mt-1 flex min-w-0 flex-col gap-y-2 sm:flex-row sm:gap-x-2 sm:gap-y-0">
      <CalendarGrid v-for="month in grid" :key="month.value.toString()">
        <CalendarGridHead>
          <CalendarGridRow>
            <CalendarHeadCell
              v-for="(day, dayIndex) in weekDays" :key="`${day}-${dayIndex}`"
            >
              {{ day }}
            </CalendarHeadCell>
          </CalendarGridRow>
        </CalendarGridHead>
        <CalendarGridBody>
          <CalendarGridRow v-for="(weekDates, index) in month.rows" :key="`weekDate-${index}`">
            <CalendarCell
              v-for="weekDate in weekDates"
              :key="weekDate.toString()"
              :date="weekDate"
            >
              <CalendarCellTrigger
                :day="weekDate"
                :month="month.value"
              />
            </CalendarCell>
          </CalendarGridRow>
        </CalendarGridBody>
      </CalendarGrid>
    </div>
  </CalendarRoot>
</template>
