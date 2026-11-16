<script setup lang="ts">
import type { PaginationFirstProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { ChevronsLeft } from "@lucide/vue"
import { PaginationFirst } from "reka-ui"
import { buttonVariants } from "@/components/ui/button"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<PaginationFirstProps & { class?: HTMLAttributes["class"] }>(),
  {
    asChild: true,
  },
)
</script>

<template>
  <PaginationFirst v-bind="props">
    <slot>
      <button
        type="button"
        :class="cn(buttonVariants({ variant: 'outline', size: 'icon-sm' }), 'size-8', props.class)"
        aria-label="Halaman Pertama"
      >
        <ChevronsLeft class="size-4" />
      </button>
    </slot>
  </PaginationFirst>
</template>
