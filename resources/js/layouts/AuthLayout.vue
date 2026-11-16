<script setup lang="ts">
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        currentTab?: 'login' | 'register' | 'forgot' | 'verify' | 'reset';
    }>(),
    {
        title: '',
        description: '',
        currentTab: 'login',
    },
);
</script>

<template>
    <AuthSimpleLayout
        :title="title"
        :description="description"
        :current-tab="currentTab"
    >
        <slot />
    </AuthSimpleLayout>
</template>
