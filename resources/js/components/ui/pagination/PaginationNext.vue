<script setup lang="ts">
import type { PaginationNextProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { ChevronRight } from "@lucide/vue"
import { PaginationNext } from "reka-ui"
import { buttonVariants } from "@/components/ui/button"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<PaginationNextProps & { class?: HTMLAttributes["class"] }>(),
  {
    asChild: true,
  },
)
</script>

<template>
  <PaginationNext v-bind="props">
    <slot>
      <button
        type="button"
        :class="cn(buttonVariants({ variant: 'outline', size: 'icon-sm' }), 'size-8', props.class)"
        aria-label="Halaman Selanjutnya"
      >
        <ChevronRight class="size-4" />
      </button>
    </slot>
  </PaginationNext>
</template>
