<script lang="ts" setup>
import type { CalendarCellProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { cn } from "cn"
import { CalendarCell, useForwardProps } from "reka-ui"

const props = defineProps<CalendarCellProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <CalendarCell
    data-slot="calendar-cell"
    :class="cn('relative h-11 p-0 text-center align-middle text-sm focus-within:relative focus-within:z-20 [&:has([data-selected])]:rounded-md [&:has([data-selected])]:bg-accent', props.class)"
    v-bind="forwardedProps"
  >
    <slot />
  </CalendarCell>
</template>
