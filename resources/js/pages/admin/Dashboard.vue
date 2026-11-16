<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    Camera,
    Clock,
    Package,
    Plus,
    TrendingUp,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }],
    },
});

const props = defineProps<{
    stats: {
        total_events: number;
        active_events: number;
        total_contributions: number;
        pending_approvals: number;
        total_packages: number;
    };
    recent_events: Array<{
        id: string;
        slug: string;
        party_one_name: string;
        party_two_name: string | null;
        status: string;
        event_date: string | null;
        package: { id: string; name: string } | null;
    }>;
}>();

const statusLabel: Record<string, string> = {
    draft: 'Draft',
    active: 'Aktif',
    closed: 'Selesai',
    expired: 'Kadaluarsa',
};

const statusVariant: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    draft: 'secondary',
    active: 'default',
    closed: 'outline',
    expired: 'destructive',
};

const statItems = [
    {
        label: 'Total Acara',
        value: props.stats.total_events,
        icon: CalendarDays,
        tone: 'bg-peach/25',
        iconTone: 'bg-peach/60 text-coral-deep',
    },
    {
        label: 'Acara Aktif',
        value: props.stats.active_events,
        icon: TrendingUp,
        tone: 'bg-coral/8',
        iconTone: 'bg-coral/15 text-coral-deep',
    },
    {
        label: 'Total Kiriman Tamu',
        value: props.stats.total_contributions,
        icon: Camera,
        tone: 'bg-lav/35',
        iconTone: 'bg-lav text-ink',
    },
    {
        label: 'Disembunyikan',
        value: props.stats.pending_approvals,
        icon: Clock,
        tone: 'bg-card',
        iconTone: 'bg-mist text-ink',
    },
    {
        label: 'Paket Aktif',
        value: props.stats.total_packages,
        icon: Package,
        tone: 'bg-card',
        iconTone: 'bg-peach/45 text-coral-deep',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <header
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Pantau acara yang sedang berlangsung, kuota paket, dan
                    kiriman tamu.
                </p>
            </div>
            <Button class="w-full sm:w-auto" as-child>
                <Link href="/events/create">
                    <Plus class="mr-2 size-4" aria-hidden="true" />
                    Buat Acara Baru
                </Link>
            </Button>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div
                v-for="s in statItems"
                :key="s.label"
                class="rounded-xl border p-5 shadow-xs"
                :class="s.tone"
            >
                <div
                    class="flex items-center justify-between text-muted-foreground"
                >
                    <span class="text-xs font-medium">{{ s.label }}</span>
                    <span
                        class="grid size-9 place-items-center rounded-lg"
                        :class="s.iconTone"
                    >
                        <component
                            :is="s.icon"
                            class="size-4"
                            aria-hidden="true"
                        />
                    </span>
                </div>
                <p
                    class="mt-3 text-2xl font-bold tracking-tight text-foreground"
                >
                    {{ s.value }}
                </p>
            </div>
        </div>

        <section class="rounded-xl border bg-card shadow-xs">
            <div
                class="flex flex-col gap-3 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-base font-semibold text-foreground">
                        Acara Terbaru
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        5 acara terakhir yang dibuat di sistem.
                    </p>
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    class="w-full sm:w-auto"
                    as-child
                >
                    <Link href="/events">Lihat Semua Acara</Link>
                </Button>
            </div>

            <div class="divide-y">
                <div
                    v-for="ev in recent_events"
                    :key="ev.id"
                    class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="min-w-0">
                        <p
                            class="notranslate truncate font-medium text-foreground"
                            translate="no"
                        >
                            {{ ev.party_one_name }}
                            <span v-if="ev.party_two_name">
                                &amp; {{ ev.party_two_name }}</span
                            >
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Paket {{ ev.package?.name ?? 'Belum dipilih' }} ·
                            {{
                                ev.event_date
                                    ? new Date(
                                          ev.event_date,
                                      ).toLocaleDateString('id-ID', {
                                          day: 'numeric',
                                          month: 'short',
                                          year: 'numeric',
                                      })
                                    : 'Tanggal belum diatur'
                            }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <Badge
                            :variant="statusVariant[ev.status] ?? 'secondary'"
                        >
                            {{ statusLabel[ev.status] ?? ev.status }}
                        </Badge>
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="`/events/${ev.id}/edit`"> Ubah </Link>
                        </Button>
                    </div>
                </div>

                <div
                    v-if="recent_events.length === 0"
                    class="px-5 py-10 text-center text-sm text-muted-foreground"
                >
                    Belum ada acara terdaftar. Klik tombol "Buat Acara Baru"
                    untuk memulai.
                </div>
            </div>
        </section>
    </div>
</template>
