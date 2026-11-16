<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EventForm from '@/components/admin/events/EventForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Daftar Acara', href: '/events' },
            { title: 'Buat Acara', href: '/events/create' },
        ],
    },
});

defineProps<{
    packages: Array<{
        id: string;
        name: string;
        price: number;
        gallery_days: number;
        max_frames: number;
        allow_zip_download: boolean;
        allow_custom_theme: boolean;
        allow_video: boolean;
        max_video_seconds: number;
    }>;
    themes: Array<{
        id: string;
        name: string;
        mood: string | null;
        primary_color: string;
        accent_color: string;
        neutral_color: string;
    }>;
    clients?: Array<{ id: string; name: string; email: string }>;
}>();
</script>

<template>
    <Head title="Buat Acara" />
    <EventForm
        mode="create"
        :packages="packages"
        :themes="themes"
        :clients="clients"
    />
</template>
