<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-14 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-4 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:h-16 md:justify-start md:px-6"
    >
        <!-- Desktop Header: SidebarTrigger + Separator + Breadcrumbs -->
        <div class="hidden items-center gap-2 md:flex">
            <SidebarTrigger class="-ml-1" />
            <Separator
                v-if="breadcrumbs && breadcrumbs.length > 0"
                orientation="vertical"
                class="mr-2 data-[orientation=vertical]:h-4"
            />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Mobile Header: Clean Brand Logo only (no sidebar trigger, no breadcrumbs) -->
        <div class="flex w-full items-center md:hidden">
            <Link :href="dashboard()" class="flex items-center">
                <AppLogo />
            </Link>
        </div>
    </header>
</template>
