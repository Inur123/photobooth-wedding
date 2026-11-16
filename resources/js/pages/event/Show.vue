<script setup lang="ts">
import EventExperience from '@/components/event/EventExperience.vue';
import type { GuestExperienceProps } from '@/types/event';

defineProps<GuestExperienceProps>();
</script>

<template>
    <EventExperience v-bind="$props" />
</template>
