<script setup lang="ts">
import type { PaginationEllipsisProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { MoreHorizontal } from "@lucide/vue"
import { PaginationEllipsis } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<PaginationEllipsisProps & { class?: HTMLAttributes["class"] }>()
</script>

<template>
  <PaginationEllipsis
    v-bind="props"
    :class="cn('flex size-8 items-center justify-center text-muted-foreground', props.class)"
  >
    <slot>
      <MoreHorizontal class="size-4" />
    </slot>
  </PaginationEllipsis>
</template>
