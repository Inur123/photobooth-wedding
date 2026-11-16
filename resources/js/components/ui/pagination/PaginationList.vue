<script setup lang="ts">
import type { PaginationListProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { PaginationList } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<PaginationListProps & { class?: HTMLAttributes["class"] }>()
</script>

<template>
  <PaginationList v-slot="slotProps" :class="cn('flex items-center gap-1', props.class)">
    <slot v-bind="slotProps" />
  </PaginationList>
</template>
