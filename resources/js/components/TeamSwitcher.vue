<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <SidebarMenuButton
                size="lg"
                as-child
                tooltip="titip.moment"
                class="group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
            >
                <Link :href="dashboard()" class="flex w-full items-center px-1">
                    <!-- Expanded State: full logo image from public/images -->
                    <img
                        src="/images/titip-momen-logo-web.png"
                        alt="titip.moment"
                        class="h-7 w-auto max-w-[170px] object-contain group-data-[collapsible=icon]:hidden"
                    />

                    <!-- Collapsed State: compact logo image (kecil gpp aman aja) -->
                    <div
                        class="hidden size-8 items-center justify-center p-1 group-data-[collapsible=icon]:flex"
                    >
                        <img
                            src="/images/titip-momen-logo-web.png"
                            alt="titip.moment"
                            class="max-h-5 w-full object-contain"
                        />
                    </div>
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
