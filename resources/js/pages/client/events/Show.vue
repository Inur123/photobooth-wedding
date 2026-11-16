<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    CalendarDays,
    Check,
    Clock,
    Copy,
    Download,
    ExternalLink,
    Eye,
    EyeOff,
    Globe,
    Images,
    Layers,
    Lock,
    MapPin,
    Package as PackageIcon,
    Palette,
    Sparkles,
    Trash2,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import { getEcho } from '@/lib/echo';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import ImageUpload from '@/components/ImageUpload.vue';
import InputError from '@/components/InputError.vue';
import ThemeColorSelector, {
    type CustomColorsValue,
    type ThemeOption,
} from '@/components/ThemeColorSelector.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Spinner } from '@/components/ui/spinner';
import { eventDate } from '@/lib/eventTheme';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Kelola Acara', href: '#' },
        ],
    },
});

type PackageRecord = {
    id: string;
    name: string;
    slug: string;
    price: number;
    gallery_days: number;
    max_frames: number;
    allow_custom_theme: boolean;
    allow_zip_download: boolean;
    allow_video: boolean;
    max_video_seconds: number;
};

type EventDetail = {
    id: string;
    slug: string;
    status: string;
    status_label: string;
    party_one_name: string;
    party_two_name: string | null;
    event_date: string | null;
    venue: string | null;
    notes: string | null;
    gallery_visibility: string;
    theme_id: string | null;
    custom_colors?: CustomColorsValue | null;
    cover_url: string | null;
    expires_at: string | null;
};

type ContributionItem = {
    id: string;
    type: string;
    guest_name: string | null;
    message: string | null;
    file_url: string | null;
    thumbnail_url: string | null;
    is_approved: boolean;
    visibility: 'public' | 'private';
    created_at: string;
};

const props = defineProps<{
    event: EventDetail;
    package: PackageRecord;
    themes: ThemeOption[];
    guestUrl: string;
    galleryUrl: string;
    ownerUrl: string;
    eventColors?: { primary: string; accent: string; neutral: string };
    allowedLayouts?: number[];
    stats: {
        total: number;
        approved: number;
        hidden: number;
    };
    contributions: {
        data: ContributionItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const initialTheme = props.themes.find(
    (t) => String(t.id) === String(props.event.theme_id ?? ''),
);

const formattedEventDate = computed(() => {
    return props.event.event_date
        ? eventDate(props.event.event_date)
        : 'Belum ditentukan';
});

const form = useForm({
    party_one_name: props.event.party_one_name ?? '',
    party_two_name: props.event.party_two_name ?? '',
    venue: props.event.venue ?? '',
    notes: props.event.notes ?? '',
    gallery_visibility: props.event.gallery_visibility ?? 'public',
    theme_id: props.event.theme_id ? String(props.event.theme_id) : 'none',
    use_custom_colors: Boolean(props.event.custom_colors?.primary),
    custom_colors: props.event.custom_colors
        ? { ...props.event.custom_colors }
        : initialTheme
          ? {
                primary: initialTheme.primary_color,
                accent: initialTheme.accent_color,
                neutral: initialTheme.neutral_color,
            }
          : { primary: '#1d1c29', accent: '#ff5d6c', neutral: '#f8f6f2' },
    cover: null as File | null,
    remove_cover: false,
});

function submitForm() {
    form.transform((data) => ({
        ...data,
        _method: 'put',
        theme_id:
            !props.package.allow_custom_theme ||
            data.theme_id === 'none' ||
            !data.theme_id
                ? null
                : data.theme_id,
        use_custom_colors:
            props.package.allow_custom_theme && data.use_custom_colors,
        custom_colors:
            props.package.allow_custom_theme && data.use_custom_colors
                ? data.custom_colors
                : null,
    })).post(`/my-events/${props.event.id}`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => form.reset('cover', 'remove_cover'),
    });
}

async function copyLink(url: string, label: string) {
    try {
        await navigator.clipboard.writeText(url);
        toast.success(`Tautan ${label} berhasil disalin ke papan klip.`);
    } catch {
        toast.error('Gagal menyalin tautan.');
    }
}

function toggleContribution(contributionId: string) {
    router.patch(
        `/my-events/${props.event.id}/contributions/${contributionId}/toggle`,
        {},
        { preserveScroll: true },
    );
}

function destroyContribution(contributionId: string) {
    router.delete(
        `/my-events/${props.event.id}/contributions/${contributionId}`,
        { preserveScroll: true },
    );
}

onMounted(() => {
    const echo = getEcho();
    if (!echo) return;

    const channel = echo.channel(`events.${props.event.id}`);

    const refreshContributions = () => {
        router.reload({
            only: ['contributions', 'stats'],
        });
    };

    channel.listen('.ContributionCreated', refreshContributions);
    channel.listen('.ContributionUpdated', refreshContributions);
    channel.listen('.ContributionDeleted', refreshContributions);
});

onUnmounted(() => {
    const echo = getEcho();
    if (echo) {
        echo.leaveChannel(`events.${props.event.id}`);
    }
});

const statusVariantMap: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    draft: 'secondary',
    active: 'default',
    closed: 'outline',
    expired: 'destructive',
};

const statusLabelMap: Record<string, string> = {
    draft: 'Draft (Belum Dibuka)',
    active: 'Aktif (Menerima Kiriman)',
    closed: 'Selesai (Kiriman Ditutup)',
    expired: 'Galeri Kadaluarsa',
};

function formatDate(dateStr: string | null): string {
    if (!dateStr) return 'Belum ditentukan';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    });
}

function formatRelativeTime(dateStr: string): string {
    const diff = Math.floor(
        (new Date().getTime() - new Date(dateStr).getTime()) / 1000,
    );
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
    return `${Math.floor(diff / 86400)} hari lalu`;
}

const activeTab = ref<'all' | 'approved' | 'hidden'>('all');

const filteredContributions = computed(() => {
    let items = props.contributions.data;
    if (activeTab.value === 'approved') {
        items = props.contributions.data.filter((c) => c.is_approved);
    } else if (activeTab.value === 'hidden') {
        items = props.contributions.data.filter((c) => !c.is_approved);
    }

    return [...items].sort((a, b) => {
        const timeA = a.created_at ? new Date(a.created_at).getTime() : 0;
        const timeB = b.created_at ? new Date(b.created_at).getTime() : 0;
        if (timeA !== timeB) {
            return timeB - timeA;
        }
        return (b.id || '').localeCompare(a.id || '');
    });
});
</script>

<template>
    <Head :title="`Kelola Acara - ${event.party_one_name}`" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <!-- Header -->
        <header
            class="flex flex-col justify-between gap-4 border-b border-border pb-5 sm:flex-row sm:items-center"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1
                        class="notranslate text-2xl font-bold tracking-tight text-foreground"
                        translate="no"
                    >
                        {{ event.party_one_name }}
                        <span v-if="event.party_two_name">
                            &amp; {{ event.party_two_name }}
                        </span>
                    </h1>
                    <Badge
                        :variant="statusVariantMap[event.status] ?? 'secondary'"
                    >
                        {{ statusLabelMap[event.status] ?? event.status_label }}
                    </Badge>
                </div>
                <div
                    class="mt-1.5 flex flex-wrap items-center gap-4 text-xs text-muted-foreground"
                >
                    <span class="flex items-center gap-1">
                        <CalendarDays class="size-3.5" />
                        {{ formatDate(event.event_date) }}
                    </span>
                    <span v-if="event.venue" class="flex items-center gap-1">
                        <MapPin class="size-3.5" />
                        {{ event.venue }}
                    </span>
                    <span class="flex items-center gap-1">
                        <PackageIcon class="size-3.5" />
                        Paket {{ package.name }}
                    </span>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div
                class="grid w-full grid-cols-1 gap-2 sm:flex sm:w-auto sm:flex-wrap sm:items-center"
            >
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-10 w-full justify-center sm:h-9 sm:w-auto"
                    @click="copyLink(guestUrl, 'photobooth tamu')"
                >
                    <Copy class="mr-1.5 size-3.5" />
                    Salin Tautan Tamu
                </Button>
                <Button
                    type="button"
                    variant="default"
                    size="sm"
                    class="h-10 w-full justify-center sm:h-9 sm:w-auto"
                    as-child
                >
                    <a
                        :href="guestUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <ExternalLink class="mr-1.5 size-3.5" />
                        Buka Photobooth Tamu
                    </a>
                </Button>
                <Button
                    v-if="ownerUrl"
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-10 w-full justify-center sm:h-9 sm:w-auto"
                    as-child
                >
                    <a
                        :href="ownerUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        title="Buka galeri pribadi dengan tautan khusus pemilik acara"
                    >
                        <Eye class="mr-1.5 size-3.5" />
                        Buka Galeri Pemilik
                    </a>
                </Button>
            </div>
        </header>

        <!-- Main Content: 2-Column Responsive Layout -->
        <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
            <!-- Left Column: Edit Form -->
            <section class="space-y-6 lg:col-span-7">
                <form
                    class="rounded-2xl border border-border bg-card p-5 shadow-xs sm:p-6"
                    @submit.prevent="submitForm"
                >
                    <div class="border-b border-border pb-4">
                        <h2 class="text-base font-semibold text-foreground">
                            Informasi Acara
                        </h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Perbarui rincian acara yang akan tampil di halaman
                            photobooth dan galeri tamu.
                        </p>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-1.5">
                                <Label for="client-party-one">
                                    Nama Mempelai / Pihak Pertama
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="client-party-one"
                                    v-model="form.party_one_name"
                                    autocomplete="off"
                                    autocorrect="off"
                                    autocapitalize="words"
                                    spellcheck="false"
                                    translate="no"
                                    data-gramm="false"
                                    placeholder="Contoh: Anisa"
                                    required
                                />
                                <InputError
                                    :message="form.errors.party_one_name"
                                />
                            </div>
                            <div class="grid gap-1.5">
                                <Label for="client-party-two">
                                    Nama Mempelai / Pihak Kedua (opsional)
                                </Label>
                                <Input
                                    id="client-party-two"
                                    v-model="form.party_two_name"
                                    autocomplete="off"
                                    autocorrect="off"
                                    autocapitalize="words"
                                    spellcheck="false"
                                    translate="no"
                                    data-gramm="false"
                                    placeholder="Contoh: Rizky"
                                />
                                <InputError
                                    :message="form.errors.party_two_name"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-1.5">
                                <div class="flex items-center justify-between">
                                    <Label for="client-event-date">
                                        Tanggal Acara
                                    </Label>
                                    <span
                                        class="flex items-center gap-1 text-[11px] text-muted-foreground"
                                    >
                                        <Lock
                                            class="size-3"
                                            aria-hidden="true"
                                        />
                                        Ditetapkan Admin
                                    </span>
                                </div>
                                <div class="relative">
                                    <Input
                                        id="client-event-date"
                                        :model-value="formattedEventDate"
                                        readonly
                                        tabindex="-1"
                                        class="cursor-default border-border bg-muted/40 pr-10 font-normal text-foreground select-none"
                                    />
                                    <CalendarDays
                                        class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-foreground/80"
                                        aria-hidden="true"
                                    />
                                </div>
                                <p class="text-[11px] text-muted-foreground">
                                    Hubungi Admin jika ingin reschedule tanggal.
                                </p>
                            </div>
                            <div class="grid gap-1.5">
                                <div class="flex items-center justify-between">
                                    <Label for="client-venue">
                                        Lokasi / Gedung Acara
                                    </Label>
                                    <span
                                        class="text-[11px] text-muted-foreground"
                                    >
                                        Opsional
                                    </span>
                                </div>
                                <Input
                                    id="client-venue"
                                    v-model="form.venue"
                                    placeholder="Contoh: Gedung Serbaguna, Magetan"
                                />
                                <p class="text-[11px] text-muted-foreground">
                                    Nama gedung atau alamat pelaksanaan acara.
                                </p>
                                <InputError :message="form.errors.venue" />
                            </div>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="client-notes">
                                Pesan Sambutan / Ucapan untuk Tamu
                            </Label>
                            <Textarea
                                id="client-notes"
                                v-model="form.notes"
                                placeholder="Tuliskan ucapan terima kasih atau instruksi untuk tamu yang hadir..."
                                rows="3"
                            />
                            <p class="text-xs text-muted-foreground">
                                Pesan ini tampil di halaman sambutan photobooth.
                            </p>
                            <InputError :message="form.errors.notes" />
                        </div>

                        <div class="grid gap-4 border-t border-border pt-5">
                            <div class="grid gap-1.5">
                                <Label for="client-visibility">
                                    Akses Galeri Tamu
                                </Label>
                                <Select v-model="form.gallery_visibility">
                                    <SelectTrigger id="client-visibility">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="public">
                                            Publik (semua tamu saling lihat)
                                        </SelectItem>
                                        <SelectItem value="private">
                                            Privat (hanya Anda yang dapat
                                            melihat)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="form.errors.gallery_visibility"
                                />
                            </div>

                            <ThemeColorSelector
                                id-prefix="client-theme"
                                :allow-custom-theme="package.allow_custom_theme"
                                :themes="themes"
                                :theme-id="form.theme_id"
                                :use-custom-colors="form.use_custom_colors"
                                :custom-colors="form.custom_colors"
                                :theme-error="form.errors.theme_id"
                                :custom-colors-root-error="
                                    (form.errors as Record<string, string>)
                                        .custom_colors
                                "
                                :custom-colors-errors="{
                                    primary: (
                                        form.errors as Record<string, string>
                                    )['custom_colors.primary'],
                                    accent: (
                                        form.errors as Record<string, string>
                                    )['custom_colors.accent'],
                                    neutral: (
                                        form.errors as Record<string, string>
                                    )['custom_colors.neutral'],
                                }"
                                @update:theme-id="form.theme_id = $event"
                                @update:use-custom-colors="
                                    form.use_custom_colors = $event
                                "
                                @update:custom-colors="
                                    form.custom_colors = $event
                                "
                            />

                            <div class="grid gap-1.5">
                                <Label>Foto Sampul Acara</Label>
                                <ImageUpload
                                    v-model="form.cover"
                                    v-model:removed="form.remove_cover"
                                    :existing-url="event.cover_url"
                                    label="Foto sampul acara"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Digunakan sebagai latar sambutan dan foto
                                    profil acara.
                                </p>
                                <InputError :message="form.errors.cover" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex flex-col items-stretch justify-between gap-3 border-t border-border pt-4 sm:flex-row sm:items-center"
                    >
                        <p
                            class="text-center text-xs text-muted-foreground sm:text-left"
                        >
                            Paket layanan dan status acara dikelola oleh Admin.
                        </p>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="h-11 w-full justify-center sm:w-auto"
                        >
                            <Spinner v-if="form.processing" class="mr-2" />
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Perubahan'
                            }}
                        </Button>
                    </div>
                </form>
            </section>

            <!-- Right Column: Package Info & Moderation -->
            <aside class="space-y-6 lg:col-span-5">
                <!-- Package Details Card -->
                <div
                    class="rounded-2xl border border-border bg-card p-5 shadow-xs sm:p-6"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span
                                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Paket Layanan Anda
                            </span>
                            <h2
                                class="mt-0.5 text-xl font-bold text-foreground"
                            >
                                Paket {{ package.name }}
                            </h2>
                        </div>
                        <Badge
                            variant="outline"
                            class="border-primary/20 bg-primary/5 text-primary"
                        >
                            {{
                                statusLabelMap[event.status] ??
                                event.status_label
                            }}
                        </Badge>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div
                            class="rounded-xl border border-border bg-muted/20 p-3"
                        >
                            <div
                                class="flex items-center gap-1.5 font-medium text-foreground"
                            >
                                <Clock class="size-3.5 text-primary" />
                                Masa Aktif
                            </div>
                            <p class="mt-1 font-semibold text-foreground">
                                {{ package.gallery_days }} Hari
                            </p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">
                                Exp: {{ formatDate(event.expires_at) }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-border bg-muted/20 p-3"
                        >
                            <div
                                class="flex items-center gap-1.5 font-medium text-foreground"
                            >
                                <Layers class="size-3.5 text-primary" />
                                Pilihan Layout
                            </div>
                            <p class="mt-1 font-semibold text-foreground">
                                {{
                                    package.max_frames <= 1
                                        ? '1 Pose'
                                        : `1–${package.max_frames} Pose`
                                }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">
                                Bingkai otomatis acara
                            </p>
                        </div>
                    </div>

                    <!-- Pilihan Layout & Media Tamu -->
                    <div class="mt-4 border-t border-border pt-3.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-foreground">
                                Layout &amp; Media Tamu
                            </span>
                            <Badge variant="outline" class="text-[10px]">
                                Otomatis Sesuai Paket
                            </Badge>
                        </div>

                        <div class="mt-2.5 flex flex-wrap gap-1.5">
                            <Badge
                                v-for="count in allowedLayouts ?? [1]"
                                :key="count"
                                variant="secondary"
                                class="text-[11px]"
                            >
                                {{ count === 1 ? '1 Pose' : `${count} Pose` }}
                            </Badge>
                            <Badge
                                :variant="
                                    package.allow_video ? 'default' : 'outline'
                                "
                                class="text-[11px]"
                            >
                                {{
                                    package.allow_video
                                        ? `Video (maks ${package.max_video_seconds} dtk)`
                                        : 'Tanpa Video'
                                }}
                            </Badge>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            Bingkai photobooth otomatis mengikuti warna tema
                            acara. Tamu dapat mengirim foto berbingkai (maks 4
                            MB), voice note (maks 60 dtk), dan ucapan.
                        </p>
                    </div>

                    <!-- ZIP Download Feature -->
                    <div class="mt-4 border-t border-border pt-4">
                        <template v-if="package.allow_zip_download">
                            <Button as-child variant="default" class="w-full">
                                <a
                                    :href="`/my-events/${event.id}/download-zip`"
                                    download
                                >
                                    <Download class="mr-2 size-4" />
                                    Unduh Semua Foto (ZIP)
                                </a>
                            </Button>
                            <p
                                class="mt-1.5 text-center text-[11px] text-muted-foreground"
                            >
                                Unduh seluruh kiriman foto beresolusi penuh yang
                                disetujui.
                            </p>
                        </template>
                        <div
                            v-else
                            class="flex items-start gap-2.5 rounded-xl border border-dashed border-border bg-muted/30 p-3.5"
                        >
                            <Lock
                                class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                            />
                            <div class="text-xs text-muted-foreground">
                                <p class="font-semibold text-foreground">
                                    Unduh Arsip ZIP Belum Tersedia
                                </p>
                                <p class="mt-0.5 leading-relaxed">
                                    Fitur unduh ZIP seluruh foto beresolusi
                                    tinggi tersedia di paket Plus dan Full.
                                    Hubungi admin kami untuk peningkatan paket.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guest Photo Moderation Card -->
                <div
                    class="rounded-2xl border border-border bg-card p-5 shadow-xs sm:p-6"
                >
                    <div
                        class="flex items-center justify-between border-b border-border pb-4"
                    >
                        <div>
                            <h2 class="text-base font-semibold text-foreground">
                                Moderasi Momen Tamu
                            </h2>
                            <p class="text-xs text-muted-foreground">
                                {{ stats.total }} kiriman ·
                                {{ stats.approved }} tampil ·
                                {{ stats.hidden }} disembunyikan
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            as-child
                        >
                            <a :href="galleryUrl" target="_blank">
                                <Eye class="mr-1.5 size-3.5" />
                                Galeri
                            </a>
                        </Button>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="mt-4 flex gap-1.5">
                        <Button
                            type="button"
                            size="sm"
                            :variant="
                                activeTab === 'all' ? 'default' : 'outline'
                            "
                            class="h-7 text-xs"
                            @click="activeTab = 'all'"
                        >
                            Semua ({{ stats.total }})
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            :variant="
                                activeTab === 'approved' ? 'default' : 'outline'
                            "
                            class="h-7 text-xs"
                            @click="activeTab = 'approved'"
                        >
                            Tampil ({{ stats.approved }})
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            :variant="
                                activeTab === 'hidden' ? 'default' : 'outline'
                            "
                            class="h-7 text-xs"
                            @click="activeTab = 'hidden'"
                        >
                            Disembunyikan ({{ stats.hidden }})
                        </Button>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="filteredContributions.length === 0"
                        class="flex flex-col items-center justify-center py-10 text-center text-xs text-muted-foreground"
                    >
                        <Images class="size-8 text-muted-foreground/40" />
                        <p class="mt-2 font-medium">Belum ada kiriman tamu</p>
                        <p class="mt-0.5 text-muted-foreground/80">
                            Bagikan tautan acara agar tamu dapat mengirimkan
                            foto dan ucapan mereka.
                        </p>
                    </div>

                    <!-- Contribution Items Grid -->
                    <div v-else class="mt-4 space-y-3">
                        <article
                            v-for="c in filteredContributions"
                            :key="c.id"
                            class="flex items-start gap-3 rounded-xl border border-border bg-card p-3 transition hover:bg-muted/10"
                        >
                            <!-- Thumbnail -->
                            <a
                                v-if="c.file_url || c.thumbnail_url"
                                :href="c.file_url ?? c.thumbnail_url ?? '#'"
                                target="_blank"
                                class="shrink-0 overflow-hidden rounded-lg border border-border bg-muted"
                            >
                                <img
                                    :src="c.thumbnail_url ?? c.file_url ?? ''"
                                    :alt="`Kiriman dari ${c.guest_name || 'Tamu'}`"
                                    width="52"
                                    height="52"
                                    class="size-13 object-cover"
                                    loading="lazy"
                                />
                            </a>
                            <div
                                v-else
                                class="grid size-13 shrink-0 place-items-center rounded-lg border border-border bg-muted text-muted-foreground"
                            >
                                <Images class="size-5" />
                            </div>

                            <!-- Meta & Message -->
                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-center justify-between gap-1.5"
                                >
                                    <p
                                        class="notranslate truncate text-xs font-semibold text-foreground"
                                        translate="no"
                                    >
                                        {{ c.guest_name || 'Tamu Tanpa Nama' }}
                                    </p>
                                    <span
                                        class="shrink-0 text-[11px] text-muted-foreground"
                                    >
                                        {{ formatRelativeTime(c.created_at) }}
                                    </span>
                                </div>
                                <p
                                    v-if="c.message"
                                    class="mt-0.5 line-clamp-2 text-xs text-muted-foreground"
                                >
                                    {{ c.message }}
                                </p>

                                <!-- Moderation Actions -->
                                <div
                                    class="mt-2 flex items-center justify-between gap-2 border-t border-border/60 pt-2"
                                >
                                    <div
                                        class="flex flex-wrap items-center gap-1.5"
                                    >
                                        <Badge
                                            :variant="
                                                c.is_approved
                                                    ? 'default'
                                                    : 'secondary'
                                            "
                                            class="px-2 py-0 text-[10px] font-normal"
                                        >
                                            {{
                                                c.is_approved
                                                    ? 'Tampil di Galeri'
                                                    : 'Disembunyikan'
                                            }}
                                        </Badge>
                                        <Badge
                                            variant="outline"
                                            class="px-1.5 py-0 text-[10px] font-normal"
                                            :class="
                                                c.visibility === 'private'
                                                    ? 'border-amber-300 bg-amber-50 text-amber-800'
                                                    : 'border-slate-200 bg-slate-50 text-slate-600'
                                            "
                                        >
                                            <component
                                                :is="
                                                    c.visibility === 'private'
                                                        ? Lock
                                                        : Globe
                                                "
                                                class="mr-1 size-2.5"
                                            />
                                            {{
                                                c.visibility === 'private'
                                                    ? 'Privat'
                                                    : 'Publik'
                                            }}
                                        </Badge>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            class="h-7 px-2 text-xs"
                                            @click="toggleContribution(c.id)"
                                        >
                                            <component
                                                :is="
                                                    c.is_approved ? EyeOff : Eye
                                                "
                                                class="mr-1 size-3"
                                            />
                                            {{
                                                c.is_approved
                                                    ? 'Sembunyikan'
                                                    : 'Tampilkan'
                                            }}
                                        </Button>

                                        <ConfirmDeleteDialog
                                            title="Hapus Momen Tamu?"
                                            description="Foto dan ucapan ini akan dihapus permanen dari galeri acaramu."
                                            @confirm="destroyContribution(c.id)"
                                        >
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                class="h-7 px-2 text-destructive hover:text-destructive"
                                            >
                                                <Trash2 class="size-3.5" />
                                            </Button>
                                        </ConfirmDeleteDialog>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="
                            contributions.links &&
                            contributions.links.length > 3
                        "
                        class="mt-4 flex flex-wrap items-center justify-center gap-1 border-t border-border pt-3"
                    >
                        <Button
                            v-for="(link, i) in contributions.links"
                            :key="i"
                            size="sm"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                            class="h-7 px-2.5 text-xs"
                            as-child
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                            >
                                <span v-html="link.label" />
                            </Link>
                            <span v-else v-html="link.label" />
                        </Button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
