<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EventForm from '@/components/admin/events/EventForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Daftar Acara', href: '/events' },
            { title: 'Ubah Acara', href: '#' },
        ],
    },
});

defineProps<{
    event: {
        id: string;
        slug: string;
        category: string;
        status: string;
        party_one_name: string;
        party_two_name: string | null;
        event_date: string | null;
        venue: string | null;
        notes: string | null;
        gallery_visibility: string;
        is_demo: boolean;
        package_id: string | null;
        theme_id: string | null;
        custom_colors?: {
            primary: string;
            accent: string;
            neutral: string;
        } | null;
        user_id?: string | null;
        cover_url: string | null;
    };
    ownerUrl: string;
    guestUrl: string;
    eventColors: { primary: string; accent: string; neutral: string };
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
    <Head :title="`Ubah Acara ${event.party_one_name}`" />
    <EventForm
        mode="edit"
        :event="event"
        :owner-url="ownerUrl"
        :guest-url="guestUrl"
        :event-colors="eventColors"
        :packages="packages"
        :themes="themes"
        :clients="clients"
    />
</template>
