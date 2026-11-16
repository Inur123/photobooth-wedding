<script setup lang="ts">
import type { PaginationPrevProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { ChevronLeft } from "@lucide/vue"
import { PaginationPrev } from "reka-ui"
import { buttonVariants } from "@/components/ui/button"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<PaginationPrevProps & { class?: HTMLAttributes["class"] }>(),
  {
    asChild: true,
  },
)
</script>

<template>
  <PaginationPrev v-bind="props">
    <slot>
      <button
        type="button"
        :class="cn(buttonVariants({ variant: 'outline', size: 'icon-sm' }), 'size-8', props.class)"
        aria-label="Halaman Sebelumnya"
      >
        <ChevronLeft class="size-4" />
      </button>
    </slot>
  </PaginationPrev>
</template>
