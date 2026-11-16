<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    Check,
    Clock,
    ExternalLink,
    Images,
    Layers,
    MapPin,
    Package as PackageIcon,
    Palette,
    Pencil,
    QrCode,
    Zap,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import QrCardModal from '@/components/QrCardModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Acara Saya', href: '/my-events' },
        ],
    },
});

type EventPackageDetail = {
    id: string;
    slug: string;
    party_one_name: string;
    party_two_name: string | null;
    display_name: string;
    event_date: string | null;
    expires_at: string | null;
    venue: string | null;
    status: string;
    contributions_count: number;
    package: {
        id: string;
        name: string;
        slug: string;
        price: number;
        tagline: string | null;
        gallery_days: number;
        max_frames: number;
        allow_zip_download: boolean;
        allow_custom_theme: boolean;
        allow_video?: boolean;
        max_video_seconds?: number;
        benefits: string[];
    };
    colors?: { primary: string; accent: string; neutral: string };
    guest_url?: string;
};

const props = defineProps<{
    userEvents: EventPackageDetail[];
}>();

const selectedEventId = ref<string>(props.userEvents[0]?.id ?? '');
const qrOpen = ref(false);

const activeEvent = computed<EventPackageDetail | undefined>(() => {
    return (
        props.userEvents.find((e) => e.id === selectedEventId.value) ??
        props.userEvents[0]
    );
});

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

function formatDate(dateStr: string | null): string {
    if (!dateStr) return 'Belum diatur';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    });
}

function calculateExpiryStatus(
    eventDate: string | null,
    expiresAt: string | null,
    galleryDays?: number,
): {
    label: string;
    isExpired: boolean;
} {
    if (!expiresAt) {
        return { label: 'Belum ditentukan', isExpired: false };
    }
    const exp = new Date(expiresAt).getTime();
    const now = new Date().getTime();

    if (now > exp) {
        return { label: 'Masa aktif telah berakhir', isExpired: true };
    }

    if (eventDate) {
        const eventTime = new Date(eventDate).getTime();
        if (now < eventTime) {
            const days = galleryDays ? `${galleryDays} hari` : 'aktif';
            return {
                label: `Aktif ${days} mulai hari-H`,
                isExpired: false,
            };
        }
    }

    const diffDays = Math.ceil((exp - now) / (1000 * 60 * 60 * 24));
    return { label: `${diffDays} hari lagi tersisa`, isExpired: false };
}
</script>

<template>
    <Head title="Acara Saya" />

    <div class="flex flex-col gap-6 p-3.5 pb-28 sm:p-6 sm:pb-8">
        <!-- Header -->
        <header
            class="flex flex-col justify-between gap-4 border-b border-border pb-5 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Acara Saya
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Rincian acara aktif, paket yang digunakan, dan masa aktif
                    galeri acaramu.
                </p>
            </div>
            <div
                v-if="userEvents.length > 0"
                class="text-xs text-muted-foreground"
            >
                Total {{ userEvents.length }} acara terdaftar
            </div>
        </header>

        <!-- Empty state jika belum ada acara -->
        <div
            v-if="userEvents.length === 0"
            class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card p-12 text-center"
        >
            <div
                class="grid size-14 place-items-center rounded-2xl bg-muted text-muted-foreground"
            >
                <CalendarDays class="size-7" />
            </div>
            <h2 class="mt-4 text-base font-semibold text-foreground">
                Belum Ada Acara yang Ditautkan
            </h2>
            <p class="mt-1 max-w-sm text-xs text-muted-foreground">
                Akun Anda belum memiliki acara yang aktif. Hubungi admin
                titip.moment untuk mendaftarkan acara Anda.
            </p>
            <Button class="mt-5" as-child>
                <Link href="/dashboard"> Kembali ke Dashboard </Link>
            </Button>
        </div>

        <template v-else>
            <!-- Event Switcher / Tabs jika akun punya lebih dari 1 acara -->
            <div v-if="userEvents.length > 1" class="space-y-2">
                <div
                    class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p
                        class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        PILIH ACARA ANDA ({{ userEvents.length }} Acara):
                    </p>
                    <span class="text-[11px] text-muted-foreground sm:text-xs">
                        Klik untuk melihat rincian acara &amp; paket
                        masing-masing
                    </span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="ev in userEvents"
                        :key="ev.id"
                        type="button"
                        class="flex min-w-[140px] flex-1 items-center justify-between gap-2.5 rounded-xl border px-3.5 py-2.5 text-xs font-semibold transition sm:flex-initial sm:justify-start sm:text-sm"
                        :class="
                            selectedEventId === ev.id
                                ? 'border-primary bg-primary/10 text-primary shadow-xs'
                                : 'border-border bg-card text-foreground hover:bg-muted/30'
                        "
                        @click="selectedEventId = ev.id"
                    >
                        <div class="flex items-center gap-2 truncate">
                            <CalendarDays
                                class="size-4 shrink-0 text-primary"
                            />
                            <span class="truncate">{{ ev.display_name }}</span>
                        </div>
                        <Badge
                            :variant="
                                selectedEventId === ev.id
                                    ? 'default'
                                    : 'secondary'
                            "
                            class="shrink-0 text-[11px]"
                        >
                            {{ ev.package.name }}
                        </Badge>
                    </button>
                </div>
            </div>

            <!-- Active Event Detail Card -->
            <div
                v-if="activeEvent"
                class="relative overflow-hidden rounded-2xl border border-border bg-card p-4 shadow-xs sm:p-6 md:p-7"
            >
                <!-- Event Header Info -->
                <div
                    class="flex flex-col justify-between gap-4 border-b border-border pb-5 sm:flex-row sm:items-start"
                >
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Acara Terpilih:
                            </span>
                            <Badge
                                :variant="
                                    statusVariant[activeEvent.status] ??
                                    'secondary'
                                "
                            >
                                {{
                                    statusLabel[activeEvent.status] ??
                                    activeEvent.status
                                }}
                            </Badge>
                        </div>
                        <h2
                            class="notranslate mt-1 text-xl font-extrabold tracking-tight text-foreground sm:text-2xl md:text-3xl"
                            translate="no"
                        >
                            {{ activeEvent.display_name }}
                        </h2>
                        <div
                            class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-muted-foreground"
                        >
                            <span class="flex items-center gap-1.5">
                                <CalendarDays class="size-3.5 text-primary" />
                                {{ formatDate(activeEvent.event_date) }}
                            </span>
                            <span
                                v-if="activeEvent.venue"
                                class="flex items-center gap-1.5"
                            >
                                <MapPin class="size-3.5 text-primary" />
                                {{ activeEvent.venue }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <Images class="size-3.5 text-primary" />
                                {{ activeEvent.contributions_count }} foto tamu
                            </span>
                            <span class="flex items-center gap-1.5">
                                <PackageIcon class="size-3.5 text-primary" />
                                Paket {{ activeEvent.package.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons: Full width on mobile, neat 2-col for secondary -->
                    <div
                        class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center"
                    >
                        <Button
                            variant="default"
                            size="sm"
                            class="h-10 w-full justify-center sm:h-9 sm:w-auto"
                            as-child
                        >
                            <Link :href="`/my-events/${activeEvent.id}`">
                                <Pencil class="mr-1.5 size-3.5" />
                                Kelola Acara Ini
                            </Link>
                        </Button>
                        <div
                            class="grid grid-cols-2 gap-2 sm:flex sm:items-center"
                        >
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-10 w-full justify-center sm:h-9 sm:w-auto"
                                @click="qrOpen = true"
                            >
                                <QrCode class="mr-1.5 size-3.5" />
                                Unduh Kartu QR
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-10 w-full justify-center sm:h-9 sm:w-auto"
                                as-child
                            >
                                <a
                                    :href="`/e/${activeEvent.slug}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <ExternalLink class="mr-1.5 size-3.5" />
                                    Halaman Tamu
                                </a>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- 4 Key Metrics Grid -->
                <div
                    class="mt-5 grid grid-cols-2 gap-2.5 sm:grid-cols-4 sm:gap-3"
                >
                    <!-- Masa Aktif & Expired Date -->
                    <div
                        class="flex flex-col justify-between rounded-xl border border-border bg-muted/20 p-3 sm:p-4"
                    >
                        <div>
                            <div
                                class="flex items-center gap-1.5 text-[11px] font-medium text-muted-foreground sm:text-xs"
                            >
                                <Clock
                                    class="size-3.5 text-primary sm:size-4"
                                />
                                <span>Masa Aktif Galeri</span>
                            </div>
                            <p
                                class="mt-1 text-base font-bold text-foreground sm:text-lg"
                            >
                                {{ activeEvent.package.gallery_days }} Hari
                            </p>
                            <p
                                class="mt-0.5 text-[11px] text-muted-foreground sm:text-xs"
                            >
                                Exp: {{ formatDate(activeEvent.expires_at) }}
                            </p>
                        </div>
                        <p
                            class="mt-1 text-[10px] font-medium sm:text-[11px]"
                            :class="
                                calculateExpiryStatus(
                                    activeEvent.event_date,
                                    activeEvent.expires_at,
                                    activeEvent.package.gallery_days,
                                ).isExpired
                                    ? 'text-destructive'
                                    : 'text-primary'
                            "
                        >
                            {{
                                calculateExpiryStatus(
                                    activeEvent.event_date,
                                    activeEvent.expires_at,
                                    activeEvent.package.gallery_days,
                                ).label
                            }}
                        </p>
                    </div>

                    <!-- Pilihan Layout Foto -->
                    <div
                        class="flex flex-col justify-between rounded-xl border border-border bg-muted/20 p-3 sm:p-4"
                    >
                        <div>
                            <div
                                class="flex items-center gap-1.5 text-[11px] font-medium text-muted-foreground sm:text-xs"
                            >
                                <Layers
                                    class="size-3.5 text-primary sm:size-4"
                                />
                                <span>Pilihan Layout Foto</span>
                            </div>
                            <p
                                class="mt-1 text-base font-bold text-foreground sm:text-lg"
                            >
                                {{
                                    activeEvent.package.max_frames <= 1
                                        ? '1 Pose'
                                        : `1–${activeEvent.package.max_frames} Pose`
                                }}
                            </p>
                            <p
                                class="mt-0.5 text-[11px] leading-tight text-muted-foreground sm:text-xs"
                            >
                                {{ activeEvent.package.max_frames }} pilihan
                                layout berbingkai
                            </p>
                        </div>
                        <p
                            class="mt-1 text-[10px] text-muted-foreground sm:text-[11px]"
                        >
                            {{
                                activeEvent.package.allow_video
                                    ? `Video tamu maks ${activeEvent.package.max_video_seconds ?? 20} dtk`
                                    : 'Foto, voice note & ucapan'
                            }}
                        </p>
                    </div>

                    <!-- Unduh ZIP -->
                    <div
                        class="flex flex-col justify-between rounded-xl border border-border bg-muted/20 p-3 sm:p-4"
                    >
                        <div>
                            <div
                                class="flex items-center gap-1.5 text-[11px] font-medium text-muted-foreground sm:text-xs"
                            >
                                <Zap class="size-3.5 text-primary sm:size-4" />
                                <span>Unduh ZIP</span>
                            </div>
                            <p
                                class="mt-1 text-base font-bold text-foreground sm:text-lg"
                            >
                                {{
                                    activeEvent.package.allow_zip_download
                                        ? 'Termasuk'
                                        : 'Tidak Termasuk'
                                }}
                            </p>
                            <p
                                class="mt-0.5 text-[11px] text-muted-foreground sm:text-xs"
                            >
                                Resolusi asli / penuh
                            </p>
                        </div>
                        <p
                            class="mt-1 text-[10px] sm:text-[11px]"
                            :class="
                                activeEvent.package.allow_zip_download
                                    ? 'text-primary'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{
                                activeEvent.package.allow_zip_download
                                    ? 'Tersedia di kelola'
                                    : 'Di Plus & Full'
                            }}
                        </p>
                    </div>

                    <!-- Tema Kustom -->
                    <div
                        class="flex flex-col justify-between rounded-xl border border-border bg-muted/20 p-3 sm:p-4"
                    >
                        <div>
                            <div
                                class="flex items-center gap-1.5 text-[11px] font-medium text-muted-foreground sm:text-xs"
                            >
                                <Palette
                                    class="size-3.5 text-primary sm:size-4"
                                />
                                <span>Tema Warna</span>
                            </div>
                            <p
                                class="mt-1 text-base font-bold text-foreground sm:text-lg"
                            >
                                {{
                                    activeEvent.package.allow_custom_theme
                                        ? 'Tema Kustom'
                                        : 'Tema Standar'
                                }}
                            </p>
                            <p
                                class="mt-0.5 text-[11px] text-muted-foreground sm:text-xs"
                            >
                                Warna palet acara
                            </p>
                        </div>
                        <p
                            class="mt-1 text-[10px] sm:text-[11px]"
                            :class="
                                activeEvent.package.allow_custom_theme
                                    ? 'text-primary'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{
                                activeEvent.package.allow_custom_theme
                                    ? 'Dapat diubah bebas'
                                    : 'Upgrade untuk kustom'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Fasilitas Yang Didapatkan -->
                <div class="mt-6 border-t border-border pt-5">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3 class="text-sm font-bold text-foreground">
                                Fasilitas yang Didapatkan pada Paket
                                {{ activeEvent.package.name }}:
                            </h3>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Seluruh fasilitas berikut aktif untuk acara
                                {{ activeEvent.display_name }}.
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-9 w-full shrink-0 justify-center sm:w-auto"
                            as-child
                        >
                            <Link href="/my-packages">
                                Lihat Semua Pilihan Paket
                            </Link>
                        </Button>
                    </div>

                    <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                        <li
                            v-for="(benefit, i) in activeEvent.package.benefits"
                            :key="i"
                            class="flex items-start gap-2.5 text-xs leading-relaxed text-foreground/90"
                        >
                            <div
                                class="mt-0.5 grid size-4 shrink-0 place-items-center rounded-full bg-primary/10 text-primary"
                            >
                                <Check class="size-2.5 stroke-[3]" />
                            </div>
                            <span>{{ benefit }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </template>

        <QrCardModal
            v-if="activeEvent"
            :open="qrOpen"
            :url="activeEvent.guest_url || `/e/${activeEvent.slug}`"
            :party-one-name="activeEvent.party_one_name"
            :party-two-name="activeEvent.party_two_name"
            :event-date="activeEvent.event_date"
            :colors="
                activeEvent.colors || {
                    primary: '#1d1c29',
                    accent: '#ff5d6c',
                    neutral: '#f8f6f2',
                }
            "
            @update:open="qrOpen = $event"
        />
    </div>
</template>
