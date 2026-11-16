<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Check,
    ChevronRight,
    EyeOff,
    FolderOpen,
    MapPin,
    Trash2,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Moderasi Kiriman', href: '/contributions' },
        ],
    },
});

type EventCardItem = {
    id: string;
    slug: string;
    party_one_name: string;
    party_two_name: string | null;
    event_date: string | null;
    venue: string | null;
    status: string;
    cover_url: string | null;
    total_count: number;
    approved_count: number;
    hidden_count: number;
};

type ContributionRow = {
    id: string;
    type: string;
    guest_name: string | null;
    is_approved: boolean;
    file_url: string | null;
    visibility: 'public' | 'private';
    message: string | null;
    created_at: string;
    event: {
        id: string;
        party_one_name: string;
        party_two_name: string | null;
        slug: string;
    } | null;
};

const props = defineProps<{
    events?: EventCardItem[];
    selectedEventId?: string | null;
    contributions: {
        data: ContributionRow[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filter?: string;
    counts?: {
        all: number;
        approved: number;
        hidden: number;
    };
}>();

const activeFilter = ref(props.filter || 'all');

watch(
    () => props.filter,
    (val) => {
        activeFilter.value = val || 'all';
    },
);

const selectedEvent = computed(
    () =>
        props.events?.find((ev) => ev.id === props.selectedEventId) ?? null,
);

const statusVariant: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    draft: 'secondary',
    active: 'default',
    closed: 'outline',
    expired: 'destructive',
};

const statusLabel: Record<string, string> = {
    draft: 'Draft',
    active: 'Aktif',
    closed: 'Selesai',
    expired: 'Kadaluarsa',
};

const typeLabel: Record<string, string> = {
    photo: 'Foto',
    video: 'Video',
    voice: 'Suara',
    wish: 'Ucapan',
};

const filterTabs = computed(() => [
    {
        key: 'all',
        label: 'Semua Kiriman',
        count: props.counts?.all ?? props.contributions.total,
    },
    {
        key: 'approved',
        label: 'Tampil di Galeri',
        count: props.counts?.approved ?? 0,
    },
    {
        key: 'hidden',
        label: 'Disembunyikan',
        count: props.counts?.hidden ?? 0,
    },
]);

function selectEvent(eventId: string) {
    router.get(
        '/contributions',
        { event: eventId, filter: 'all' },
        { preserveState: true },
    );
}

function backToEventList() {
    router.get('/contributions', {}, { preserveState: true });
}

function setFilter(f: string) {
    activeFilter.value = f;
    router.get(
        '/contributions',
        {
            ...(props.selectedEventId ? { event: props.selectedEventId } : {}),
            filter: f,
        },
        { preserveState: true, replace: true },
    );
}

function approve(id: string) {
    router.patch(`/contributions/${id}/approve`, {}, { preserveScroll: true });
}

function reject(id: string) {
    router.patch(`/contributions/${id}/reject`, {}, { preserveScroll: true });
}

function destroy(id: string) {
    router.delete(`/contributions/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Moderasi Kiriman" />

    <div class="flex flex-col gap-5 p-3.5 pb-28 sm:p-6 sm:pb-8">
        <!-- TAMPILAN 1: DAFTAR CARD ACARA (Saat belum memilih acara) -->
        <template v-if="!selectedEventId">
            <header>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Moderasi Kiriman Tamu
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Pilih acara di bawah ini untuk meninjau dan memoderasi foto,
                    video, pesan suara, maupun ucapan tamu.
                </p>
            </header>

            <div
                v-if="events && events.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <button
                    v-for="ev in events"
                    :key="ev.id"
                    type="button"
                    class="group flex flex-col justify-between rounded-2xl border border-border bg-card p-5 text-left shadow-xs transition hover:border-primary/40 hover:shadow-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    @click="selectEvent(ev.id)"
                >
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border bg-muted font-semibold text-foreground"
                                >
                                    <img
                                        v-if="ev.cover_url"
                                        :src="ev.cover_url"
                                        :alt="ev.party_one_name"
                                        class="size-full object-cover"
                                    />
                                    <span
                                        v-else
                                        class="notranslate text-sm"
                                        translate="no"
                                    >
                                        {{ ev.party_one_name[0]
                                        }}{{ ev.party_two_name?.[0] ?? '' }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <h2
                                        class="notranslate truncate text-base font-bold text-foreground group-hover:text-primary"
                                        translate="no"
                                    >
                                        {{ ev.party_one_name }}
                                        <span v-if="ev.party_two_name">
                                            &amp; {{ ev.party_two_name }}
                                        </span>
                                    </h2>
                                    <p
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        /e/{{ ev.slug }}
                                    </p>
                                </div>
                            </div>

                            <Badge
                                :variant="
                                    statusVariant[ev.status] ?? 'secondary'
                                "
                                class="shrink-0"
                            >
                                {{ statusLabel[ev.status] ?? ev.status }}
                            </Badge>
                        </div>

                        <div
                            class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground"
                        >
                            <span
                                v-if="ev.event_date"
                                class="inline-flex items-center gap-1"
                            >
                                <Calendar class="size-3.5" aria-hidden="true" />
                                {{
                                    new Date(ev.event_date).toLocaleDateString(
                                        'id-ID',
                                        {
                                            day: 'numeric',
                                            month: 'short',
                                            year: 'numeric',
                                        },
                                    )
                                }}
                            </span>
                            <span
                                v-if="ev.venue"
                                class="inline-flex items-center gap-1 truncate"
                            >
                                <MapPin class="size-3.5" aria-hidden="true" />
                                {{ ev.venue }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="mt-5 flex items-center justify-between border-t border-border/60 pt-3.5"
                    >
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <Badge variant="secondary" class="font-semibold">
                                {{ ev.total_count }} Kiriman
                            </Badge>
                            <span class="text-muted-foreground">
                                {{ ev.approved_count }} tampil
                            </span>
                            <span
                                v-if="ev.hidden_count > 0"
                                class="font-medium text-destructive"
                            >
                                · {{ ev.hidden_count }} disembunyikan
                            </span>
                        </div>

                        <ChevronRight
                            class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:text-primary"
                            aria-hidden="true"
                        />
                    </div>
                </button>
            </div>

            <div
                v-else
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card px-6 py-14 text-center"
            >
                <FolderOpen
                    class="size-10 text-muted-foreground/60"
                    aria-hidden="true"
                />
                <p class="mt-3 text-sm font-medium text-foreground">
                    Belum ada acara yang dibuat
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Buat acara terlebih dahulu untuk mulai menerima kiriman
                    tamu.
                </p>
            </div>
        </template>

        <!-- TAMPILAN 2: DAFTAR KIRIMAN UNTUK ACARA YANG DIPILIH -->
        <template v-else>
            <header
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="-ml-2 mb-1 h-8 px-2 text-xs text-muted-foreground hover:text-foreground"
                        @click="backToEventList"
                    >
                        <ArrowLeft class="mr-1.5 size-3.5" aria-hidden="true" />
                        Kembali ke Daftar Acara
                    </Button>
                    <h1
                        class="notranslate text-2xl font-bold tracking-tight text-foreground"
                        translate="no"
                    >
                        <template v-if="selectedEvent">
                            {{ selectedEvent.party_one_name }}
                            <span v-if="selectedEvent.party_two_name">
                                &amp; {{ selectedEvent.party_two_name }}
                            </span>
                        </template>
                        <template v-else>Moderasi Kiriman Tamu</template>
                    </h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        Kelola dan moderasi seluruh kiriman tamu untuk acara
                        ini.
                    </p>
                </div>
            </header>

            <!-- Filter Tabs -->
            <div
                class="no-scrollbar -mx-3.5 flex items-center gap-1.5 overflow-x-auto border-b border-border/70 px-3.5 pb-2 sm:mx-0 sm:flex-wrap sm:px-0"
                role="tablist"
                aria-label="Filter status kiriman"
            >
                <Button
                    v-for="tab in filterTabs"
                    :key="tab.key"
                    type="button"
                    role="tab"
                    :aria-selected="activeFilter === tab.key"
                    :variant="activeFilter === tab.key ? 'default' : 'outline'"
                    size="sm"
                    class="h-8 shrink-0 rounded-full px-3 text-xs font-medium sm:text-sm"
                    @click="setFilter(tab.key)"
                >
                    {{ tab.label }} ({{ tab.count }})
                </Button>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-xs"
            >
                <div class="divide-y divide-border/60">
                    <div
                        v-for="c in contributions.data"
                        :key="c.id"
                        class="flex flex-col gap-3.5 p-3.5 sm:flex-row sm:items-center sm:justify-between sm:p-5"
                    >
                        <div
                            class="flex min-w-0 flex-1 items-start gap-3 sm:gap-4"
                        >
                            <a
                                v-if="c.file_url && c.type === 'photo'"
                                :href="c.file_url"
                                target="_blank"
                                class="shrink-0 overflow-hidden rounded-xl border border-border bg-muted"
                            >
                                <img
                                    :src="c.file_url"
                                    :alt="`Foto kiriman ${c.guest_name || 'Tamu'}`"
                                    width="56"
                                    height="56"
                                    class="size-14 object-cover sm:size-16"
                                />
                            </a>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex flex-wrap items-center gap-1.5 sm:gap-2"
                                >
                                    <Badge
                                        variant="outline"
                                        class="px-2 py-0 text-[11px]"
                                    >
                                        {{ typeLabel[c.type] ?? c.type }}
                                    </Badge>
                                    <Badge
                                        variant="outline"
                                        class="px-2 py-0 text-[11px]"
                                    >
                                        {{
                                            c.visibility === 'private'
                                                ? 'Privat'
                                                : 'Publik'
                                        }}
                                    </Badge>
                                    <Badge
                                        :variant="
                                            c.is_approved
                                                ? 'default'
                                                : 'secondary'
                                        "
                                        class="px-2 py-0 text-[11px]"
                                    >
                                        {{
                                            c.is_approved
                                                ? c.visibility === 'private'
                                                    ? 'Disetujui'
                                                    : 'Tampil di Galeri'
                                                : 'Disembunyikan'
                                        }}
                                    </Badge>
                                    <span
                                        class="notranslate text-xs font-medium text-foreground"
                                        translate="no"
                                    >
                                        Oleh:
                                        {{ c.guest_name || 'Tamu Tanpa Nama' }}
                                    </span>
                                </div>

                                <p
                                    v-if="c.message"
                                    class="mt-1 line-clamp-2 text-xs text-muted-foreground sm:text-sm"
                                >
                                    {{ c.message }}
                                </p>

                                <div
                                    class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground sm:gap-3 sm:text-xs"
                                >
                                    <span>{{
                                        new Date(c.created_at).toLocaleString(
                                            'id-ID',
                                        )
                                    }}</span>
                                    <a
                                        v-if="c.file_url"
                                        :href="c.file_url"
                                        target="_blank"
                                        class="font-medium text-foreground underline underline-offset-4"
                                    >
                                        Lihat Berkas Media
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 border-t border-border/40 pt-2 sm:shrink-0 sm:border-0 sm:pt-0"
                        >
                            <Button
                                v-if="!c.is_approved"
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 flex-1 text-xs sm:flex-initial"
                                @click="approve(c.id)"
                            >
                                <Check
                                    class="mr-1.5 size-3.5"
                                    aria-hidden="true"
                                />
                                Tampilkan
                            </Button>
                            <Button
                                v-else
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 flex-1 text-xs sm:flex-initial"
                                @click="reject(c.id)"
                            >
                                <EyeOff
                                    class="mr-1.5 size-3.5"
                                    aria-hidden="true"
                                />
                                Sembunyikan
                            </Button>
                            <ConfirmDeleteDialog
                                title="Hapus kiriman tamu?"
                                description="Berkas dan data kiriman ini akan dihapus permanen dan tidak dapat dipulihkan."
                                @confirm="destroy(c.id)"
                            >
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-8 text-xs text-destructive hover:bg-destructive/10 hover:text-destructive"
                                >
                                    <Trash2
                                        class="size-3.5 sm:size-4"
                                        aria-hidden="true"
                                    />
                                    Hapus
                                </Button>
                            </ConfirmDeleteDialog>
                        </div>
                    </div>

                    <div
                        v-if="contributions.data.length === 0"
                        class="px-5 py-12 text-center text-sm text-muted-foreground"
                    >
                        Belum ada kiriman tamu untuk acara ini pada kategori
                        filter yang dipilih.
                    </div>
                </div>

                <div
                    v-if="contributions.last_page > 1"
                    class="flex items-center justify-between border-t border-border/60 px-5 py-3 text-xs text-muted-foreground sm:text-sm"
                >
                    <span>Total {{ contributions.total }} kiriman</span>
                    <span
                        >Halaman {{ contributions.current_page }} dari
                        {{ contributions.last_page }}</span
                    >
                </div>
            </div>
        </template>
    </div>
</template>
