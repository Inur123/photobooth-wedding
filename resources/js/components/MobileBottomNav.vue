<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    Images,
    LayoutGrid,
    Package,
    UserCircle2,
} from '@lucide/vue';
import { computed } from 'vue';

type NavItem = {
    title: string;
    href: string;
    icon: typeof LayoutGrid;
};

const page = usePage();
const user = computed(
    () => page.props.auth?.user as { is_admin?: boolean } | null,
);
const isAdmin = computed(() => Boolean(user.value?.is_admin));

const clientNav: NavItem[] = [
    { title: 'Beranda', href: '/dashboard', icon: LayoutGrid },
    { title: 'Acara Saya', href: '/my-events', icon: CalendarDays },
    { title: 'Paket', href: '/my-packages', icon: Package },
    { title: 'Akun', href: '/settings/profile', icon: UserCircle2 },
];

const adminNav: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'Acara', href: '/events', icon: CalendarDays },
    { title: 'Paket', href: '/packages', icon: Package },
    { title: 'Moderasi', href: '/contributions', icon: Images },
    { title: 'Akun', href: '/settings/profile', icon: UserCircle2 },
];

const items = computed(() => (isAdmin.value ? adminNav : clientNav));

function isActive(href: string): boolean {
    const url = page.url;
    if (href === '/dashboard') {
        return url === '/dashboard' || url.startsWith('/dashboard?');
    }
    if (href === '/my-events') {
        return url.startsWith('/my-events');
    }
    if (href === '/events') {
        return url.startsWith('/events');
    }
    if (href === '/my-packages') {
        return url.startsWith('/my-packages');
    }
    if (href === '/packages') {
        return url.startsWith('/packages');
    }
    if (href === '/contributions') {
        return url.startsWith('/contributions');
    }
    if (href === '/settings/profile') {
        return url.startsWith('/settings');
    }
    return url === href;
}
</script>

<template>
    <nav
        class="pointer-events-auto fixed inset-x-3 z-40 mx-auto block max-w-lg select-none md:hidden"
        style="bottom: max(10px, env(safe-area-inset-bottom))"
        aria-label="Navigasi Utama Mobile"
    >
        <div
            class="flex items-center justify-around rounded-2xl border border-border/80 bg-card/95 px-1.5 py-1.5 shadow-xl shadow-stone-900/5 backdrop-blur-md"
        >
            <Link
                v-for="item in items"
                :key="item.href"
                :href="item.href"
                preserve-scroll
                class="flex min-h-[48px] flex-1 flex-col items-center justify-center gap-0.5 rounded-xl py-1 transition-all active:scale-95"
                :class="[
                    isActive(item.href)
                        ? 'font-semibold text-primary'
                        : 'text-muted-foreground hover:text-foreground',
                ]"
                :aria-current="isActive(item.href) ? 'page' : undefined"
            >
                <div
                    class="flex items-center justify-center rounded-full px-3 py-1 transition-colors"
                    :class="[
                        isActive(item.href)
                            ? 'bg-primary/10 text-primary'
                            : 'text-muted-foreground',
                    ]"
                >
                    <component
                        :is="item.icon"
                        class="size-5 transition-transform"
                        :class="{
                            'scale-105 stroke-[2.25]': isActive(item.href),
                        }"
                        aria-hidden="true"
                    />
                </div>
                <span class="text-[10px] leading-tight tracking-tight">
                    {{ item.title }}
                </span>
            </Link>
        </div>
    </nav>
</template>
