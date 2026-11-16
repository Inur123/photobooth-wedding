<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profil',
        href: editProfile(),
    },
    {
        title: 'Keamanan',
        href: editSecurity(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="px-3.5 py-5 pb-28 sm:px-6 sm:py-6 sm:pb-8">
        <Heading
            title="Pengaturan"
            description="Kelola profil dan keamanan akun"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <!-- Mobile / Tablet: sleek segmented control bar; Desktop: vertical sidebar -->
                <nav
                    class="flex space-x-1 rounded-xl bg-muted/60 p-1 lg:flex-col lg:space-y-1 lg:space-x-0 lg:bg-transparent lg:p-0"
                    aria-label="Pengaturan"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'flex-1 justify-center rounded-lg text-xs font-semibold sm:text-sm lg:w-full lg:justify-start',
                            isCurrentOrParentUrl(item.href)
                                ? 'bg-card text-foreground shadow-xs lg:bg-muted'
                                : 'text-muted-foreground hover:text-foreground',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <div class="h-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
