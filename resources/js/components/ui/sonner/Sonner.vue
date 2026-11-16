<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import { CircleCheckIcon, InfoIcon, Loader2Icon, OctagonXIcon, TriangleAlertIcon, XIcon } from "@lucide/vue"
import { Toaster as Sonner } from "vue-sonner"
import { cn } from "@/lib/utils"

import 'vue-sonner/style.css';

const props = defineProps<ToasterProps>()
</script>

<template>
  <Sonner
    :class="cn('toaster group', props.class)"
    :style="{
      '--normal-bg': '#ffffff',
      '--normal-text': '#1d1c29',
      '--normal-border': 'var(--border)',
      '--success-bg': '#ffffff',
      '--success-text': '#1d1c29',
      '--success-border': 'var(--border)',
      '--info-bg': '#ffffff',
      '--info-text': '#1d1c29',
      '--info-border': 'var(--border)',
      '--warning-bg': '#ffffff',
      '--warning-text': '#1d1c29',
      '--warning-border': 'var(--border)',
      '--error-bg': '#ffffff',
      '--error-text': '#1d1c29',
      '--error-border': 'var(--border)',
      '--border-radius': 'var(--radius)',
    }"
    v-bind="props"
  >
    <template #success-icon>
      <CircleCheckIcon class="size-4" />
    </template>
    <template #info-icon>
      <InfoIcon class="size-4" />
    </template>
    <template #warning-icon>
      <TriangleAlertIcon class="size-4" />
    </template>
    <template #error-icon>
      <OctagonXIcon class="size-4" />
    </template>
    <template #loading-icon>
      <div>
        <Loader2Icon class="size-4 animate-spin" />
      </div>
    </template>
    <template #close-icon>
      <XIcon class="size-4" />
    </template>
  </Sonner>
</template>
