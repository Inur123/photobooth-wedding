<script lang="ts" setup>
import type { CalendarNextProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { ChevronRight } from "@lucide/vue"
import { reactiveOmit } from "@vueuse/core"
import { cn } from "cn"
import { CalendarNext, useForwardProps } from "reka-ui"
import { buttonVariants } from '@/components/ui/button'

const props = defineProps<CalendarNextProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <CalendarNext
    data-slot="calendar-next-button"
    :class="cn(
      buttonVariants({ variant: 'ghost', size: 'icon' }),
      'pointer-events-auto size-11 bg-transparent p-0 text-muted-foreground hover:text-foreground',
      props.class,
    )"
    v-bind="forwardedProps"
  >
    <slot>
      <ChevronRight class="size-4" />
    </slot>
  </CalendarNext>
</template>
