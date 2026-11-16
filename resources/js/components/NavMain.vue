<script setup lang="ts">
import type { Component } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

defineProps<{
    items: {
        title: string;
        url: string;
        icon?: Component;
    }[];
    label?: string;
}>();

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup>
        <SidebarGroupLabel
            v-if="label"
            class="group-data-[collapsible=icon]:hidden"
        >
            {{ label }}
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :tooltip="item.title"
                    :is-active="isCurrentOrParentUrl(item.url)"
                >
                    <Link :href="item.url">
                        <component :is="item.icon" v-if="item.icon" />
                        <span class="group-data-[collapsible=icon]:hidden">{{
                            item.title
                        }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
