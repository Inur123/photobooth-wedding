<script setup lang="ts">
import type { PaginationLastProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { ChevronsRight } from "@lucide/vue"
import { PaginationLast } from "reka-ui"
import { buttonVariants } from "@/components/ui/button"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<PaginationLastProps & { class?: HTMLAttributes["class"] }>(),
  {
    asChild: true,
  },
)
</script>

<template>
  <PaginationLast v-bind="props">
    <slot>
      <button
        type="button"
        :class="cn(buttonVariants({ variant: 'outline', size: 'icon-sm' }), 'size-8', props.class)"
        aria-label="Halaman Terakhir"
      >
        <ChevronsRight class="size-4" />
      </button>
    </slot>
  </PaginationLast>
</template>
