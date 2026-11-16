<script lang="ts" setup>
import type { CalendarPrevProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { ChevronLeft } from "@lucide/vue"
import { reactiveOmit } from "@vueuse/core"
import { cn } from "cn"
import { CalendarPrev, useForwardProps } from "reka-ui"
import { buttonVariants } from '@/components/ui/button'

const props = defineProps<CalendarPrevProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <CalendarPrev
    data-slot="calendar-prev-button"
    :class="cn(
      buttonVariants({ variant: 'ghost', size: 'icon' }),
      'pointer-events-auto size-11 bg-transparent p-0 text-muted-foreground hover:text-foreground',
      props.class,
    )"
    v-bind="forwardedProps"
  >
    <slot>
      <ChevronLeft class="size-4" />
    </slot>
  </CalendarPrev>
</template>
