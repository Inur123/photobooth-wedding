<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    Camera,
    ExternalLink,
    Images,
    Plus,
    Sparkles,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import { computed } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const user = computed(
    () => page.props.auth.user as { name: string; email: string },
);

const rupiah = (n: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(n);

type EventItem = {
    id: string;
    slug: string;
    party_one_name: string;
    party_two_name: string | null;
    event_date: string | null;
    venue: string | null;
    status: string;
    package_name: string;
    contribution_count: number;
};

const props = defineProps<{
    events?: EventItem[];
}>();

const statusLabel: Record<string, string> = {
    draft: 'Draft',
    active: 'Aktif',
    closed: 'Selesai',
    expired: 'Kadaluarsa',
};

const statusClass: Record<string, string> = {
    draft: 'bg-mist text-brand-muted',
    active: 'bg-emerald-100 text-emerald-700',
    closed: 'bg-lav text-ink',
    expired: 'bg-ink/8 text-ink/40',
};

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 11) return 'Selamat pagi';
    if (h < 15) return 'Selamat siang';
    if (h < 18) return 'Selamat sore';
    return 'Selamat malam';
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <!-- Welcome banner -->
        <div
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-ink to-void px-6 py-7 text-cream sm:px-8 sm:py-8"
        >
            <div
                class="pointer-events-none absolute -top-16 -right-12 size-48 rounded-full bg-coral/30 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-16 -left-12 size-48 rounded-full bg-lav/20 blur-3xl"
            />
            <div class="relative">
                <p class="text-sm text-cream/60">{{ greeting }},</p>
                <h1 class="mt-1 text-2xl font-bold sm:text-3xl">
                    {{ user.name }} <span class="text-coral">👋</span>
                </h1>
                <p class="mt-2 max-w-md text-sm text-cream/70">
                    Kelola photobooth digital acaramu dari sini. Semua momen
                    tamu tersimpan rapi di satu galeri.
                </p>
            </div>
        </div>

        <!-- Quick stats -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-ink/5">
                <div
                    class="grid size-10 place-items-center rounded-xl bg-coral/10 text-coral-deep"
                >
                    <CalendarDays class="size-5" />
                </div>
                <p class="mt-4 text-2xl font-bold">
                    {{ (events ?? []).length }}
                </p>
                <p class="text-sm text-brand-muted">Total acara</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-ink/5">
                <div
                    class="grid size-10 place-items-center rounded-xl bg-lav text-ink"
                >
                    <Camera class="size-5" />
                </div>
                <p class="mt-4 text-2xl font-bold">
                    {{
                        (events ?? []).filter((e) => e.status === 'active')
                            .length
                    }}
                </p>
                <p class="text-sm text-brand-muted">Acara aktif</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-ink/5">
                <div
                    class="grid size-10 place-items-center rounded-xl bg-peach/60 text-coral-deep"
                >
                    <Images class="size-5" />
                </div>
                <p class="mt-4 text-2xl font-bold">
                    {{
                        (events ?? []).reduce(
                            (s, e) => s + e.contribution_count,
                            0,
                        )
                    }}
                </p>
                <p class="text-sm text-brand-muted">Total kiriman tamu</p>
            </div>
        </div>

        <!-- Events list -->
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold">Acaramu</h2>
                <a
                    href="#"
                    class="inline-flex items-center gap-1.5 rounded-full bg-coral px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-coral/30 transition hover:bg-coral-deep"
                >
                    <Plus class="size-4" />
                    Pesan acara
                </a>
            </div>

            <!-- Empty state -->
            <div
                v-if="!events || events.length === 0"
                class="flex flex-col items-center justify-center rounded-3xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-ink/5"
            >
                <div
                    class="grid size-16 place-items-center rounded-2xl bg-coral/10 text-coral-deep"
                >
                    <Sparkles class="size-8" />
                </div>
                <h3 class="mt-5 text-xl font-bold">Belum ada acara</h3>
                <p class="mt-2 max-w-xs text-sm text-brand-muted">
                    Mulai dengan memesan paket photobooth digital untuk acaramu.
                </p>
                <a
                    href="#"
                    class="mt-6 inline-flex items-center gap-2 rounded-full bg-ink px-6 py-3 text-sm font-semibold text-cream transition hover:bg-void"
                >
                    <Plus class="size-4" />
                    Pesan sekarang
                </a>
            </div>

            <!-- Event cards -->
            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="ev in events"
                    :key="ev.id"
                    class="group rounded-3xl bg-white p-6 shadow-sm ring-1 ring-ink/5 transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3
                                class="notranslate leading-tight font-bold"
                                translate="no"
                            >
                                {{ ev.party_one_name
                                }}<span v-if="ev.party_two_name"
                                    >&nbsp;&amp; {{ ev.party_two_name }}</span
                                >
                            </h3>
                            <p class="mt-0.5 text-xs text-brand-muted">
                                {{ ev.venue ?? '—' }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="statusClass[ev.status] ?? 'bg-mist'"
                        >
                            {{ statusLabel[ev.status] ?? ev.status }}
                        </span>
                    </div>

                    <div
                        class="mt-4 flex items-center gap-3 text-xs text-brand-muted"
                    >
                        <span class="flex items-center gap-1">
                            <CalendarDays class="size-3.5" />
                            {{
                                ev.event_date
                                    ? new Date(
                                          ev.event_date,
                                      ).toLocaleDateString('id-ID', {
                                          day: 'numeric',
                                          month: 'short',
                                          year: 'numeric',
                                      })
                                    : '—'
                            }}
                        </span>
                        <span class="flex items-center gap-1">
                            <Images class="size-3.5" />
                            {{ ev.contribution_count }} kiriman
                        </span>
                    </div>

                    <div
                        class="mt-4 flex items-center justify-between gap-2 border-t border-ink/5 pt-4"
                    >
                        <span class="text-xs font-medium text-brand-muted">{{
                            ev.package_name
                        }}</span>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="`/my-events/${ev.id}`"
                                class="inline-flex items-center gap-1.5 rounded-full bg-coral px-3 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-coral-deep"
                            >
                                Kelola Acara
                            </Link>
                            <a
                                :href="`/e/${ev.slug}`"
                                target="_blank"
                                class="inline-flex items-center gap-1 rounded-full bg-ink/5 px-2.5 py-1.5 text-xs font-semibold text-ink transition hover:bg-ink/10"
                                title="Buka Photobooth Tamu"
                            >
                                <ExternalLink class="size-3.5" />
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>
