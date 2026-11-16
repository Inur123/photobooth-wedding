<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import { CalendarDays, Images, LayoutGrid, Package } from '@lucide/vue';
import { computed } from 'vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarRail,
} from '@/components/ui/sidebar';
import type { User } from '@/types';

const props = withDefaults(defineProps<SidebarProps>(), {
    collapsible: 'icon',
});

const page = usePage();
const user = computed(
    () => page.props.auth?.user as (User & { is_admin?: boolean }) | null,
);
const isAdmin = computed(() => Boolean(user.value?.is_admin));

const clientNav = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Acara Saya',
        url: '/my-events',
        icon: CalendarDays,
    },
    {
        title: 'Paket Layanan',
        url: '/my-packages',
        icon: Package,
    },
];

const adminNav = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Daftar Acara',
        url: '/events',
        icon: CalendarDays,
    },
    {
        title: 'Paket & Harga',
        url: '/packages',
        icon: Package,
    },
    {
        title: 'Moderasi Kiriman',
        url: '/contributions',
        icon: Images,
    },
];

const navItems = computed(() => (isAdmin.value ? adminNav : clientNav));
</script>

<template>
    <Sidebar v-bind="props">
        <SidebarHeader>
            <TeamSwitcher />
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="navItems" label="Menu Utama" />
        </SidebarContent>
        <SidebarFooter>
            <NavUser :user="user ?? undefined" />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
