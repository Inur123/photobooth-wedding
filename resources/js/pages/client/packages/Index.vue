<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    Check,
    Clock,
    Film,
    HelpCircle,
    Layers,
    Package as PackageIcon,
    Palette,
    Sparkles,
    Zap,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Paket Layanan', href: '/my-packages' },
        ],
    },
});

type PackageItem = {
    id: string;
    name: string;
    slug: string;
    tagline: string | null;
    price: number;
    gallery_days: number;
    max_frames: number;
    allow_zip_download: boolean;
    allow_custom_theme: boolean;
    allow_video: boolean;
    max_video_seconds: number;
    benefits: string[];
    used_by: string | null;
};

defineProps<{
    packages: PackageItem[];
}>();

const formatRupiah = (val: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
</script>

<template>
    <Head title="Paket Layanan" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <!-- Header -->
        <header
            class="flex flex-col justify-between gap-4 border-b border-border pb-5 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Paket Layanan
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Daftar paket photobooth digital titip.moment yang dibuat dan
                    disediakan oleh Admin.
                </p>
            </div>
            <Button variant="outline" size="sm" as-child>
                <Link href="/my-events">
                    <CalendarDays class="mr-1.5 size-3.5" />
                    Lihat Acara Saya
                </Link>
            </Button>
        </header>

        <!-- Packages Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <article
                v-for="pkg in packages"
                :key="pkg.id"
                class="flex flex-col justify-between rounded-2xl border bg-card p-6 shadow-xs transition hover:border-primary/40"
                :class="
                    pkg.used_by
                        ? 'border-primary ring-1 ring-primary'
                        : 'border-border'
                "
            >
                <div>
                    <!-- Package Name & Used Badge -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h2 class="text-xl font-bold text-foreground">
                                Paket {{ pkg.name }}
                            </h2>
                            <p
                                v-if="pkg.tagline"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                {{ pkg.tagline }}
                            </p>
                        </div>
                        <Badge
                            v-if="pkg.used_by"
                            variant="default"
                            class="text-[11px]"
                        >
                            Acara Aktif
                        </Badge>
                    </div>

                    <!-- Price -->
                    <div class="mt-5 border-t border-border/80 pt-4">
                        <p class="text-2xl font-extrabold text-foreground">
                            {{ formatRupiah(pkg.price) }}
                            <span
                                class="text-xs font-normal text-muted-foreground"
                            >
                                / acara
                            </span>
                        </p>
                    </div>

                    <!-- Specs Breakdown -->
                    <div
                        class="mt-4 space-y-2 rounded-xl border border-border bg-muted/20 p-3.5 text-xs"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-1.5 text-muted-foreground"
                            >
                                <Clock class="size-3.5 text-primary" />
                                Masa Aktif Galeri:
                            </span>
                            <span class="font-bold text-foreground">
                                {{ pkg.gallery_days }} Hari
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-1.5 text-muted-foreground"
                            >
                                <Layers class="size-3.5 text-primary" />
                                Pilihan Layout Foto:
                            </span>
                            <span class="font-bold text-foreground">
                                {{
                                    pkg.max_frames <= 1
                                        ? '1 Pose Berbingkai'
                                        : `1–${pkg.max_frames} Pose`
                                }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-1.5 text-muted-foreground"
                            >
                                <Film class="size-3.5 text-primary" />
                                Kiriman Video Tamu:
                            </span>
                            <span
                                class="font-bold"
                                :class="
                                    pkg.allow_video
                                        ? 'text-primary'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{
                                    pkg.allow_video
                                        ? `Maks ${pkg.max_video_seconds} dtk`
                                        : 'Tidak Termasuk'
                                }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-1.5 text-muted-foreground"
                            >
                                <Zap class="size-3.5 text-primary" />
                                Unduh Semua File (ZIP):
                            </span>
                            <span
                                class="font-bold"
                                :class="
                                    pkg.allow_zip_download
                                        ? 'text-primary'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{
                                    pkg.allow_zip_download
                                        ? 'Termasuk'
                                        : 'Tidak Termasuk'
                                }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="flex items-center gap-1.5 text-muted-foreground"
                            >
                                <Palette class="size-3.5 text-primary" />
                                Tema Warna Custom:
                            </span>
                            <span
                                class="font-bold"
                                :class="
                                    pkg.allow_custom_theme
                                        ? 'text-primary'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{
                                    pkg.allow_custom_theme
                                        ? 'Termasuk'
                                        : 'Standar'
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Benefits Checklist -->
                    <div class="mt-5">
                        <p
                            class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Fasilitas Paket:
                        </p>
                        <ul class="mt-3 space-y-2.5 text-xs">
                            <li
                                v-for="(b, idx) in pkg.benefits"
                                :key="idx"
                                class="flex items-start gap-2.5 text-foreground/90"
                            >
                                <div
                                    class="mt-0.5 grid size-4 shrink-0 place-items-center rounded-full bg-primary/10 text-primary"
                                >
                                    <Check class="size-2.5 stroke-[3]" />
                                </div>
                                <span class="leading-relaxed">{{ b }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Footer Status or CTA -->
                <div class="mt-6 border-t border-border pt-4">
                    <div v-if="pkg.used_by" class="space-y-2">
                        <div
                            class="rounded-lg bg-primary/5 p-2.5 text-center text-xs text-primary"
                        >
                            <span class="font-semibold"
                                >Sedang digunakan pada:</span
                            >
                            <p class="mt-0.5 truncate font-bold">
                                {{ pkg.used_by }}
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-full text-xs"
                            as-child
                        >
                            <Link href="/my-events"> Buka di Acara Saya </Link>
                        </Button>
                    </div>

                    <div
                        v-else
                        class="text-center text-xs text-muted-foreground"
                    >
                        <p class="mb-2">Tertarik memakai paket ini?</p>
                        <p class="font-medium text-foreground">
                            Hubungi Admin untuk Pemesanan / Upgrade
                        </p>
                    </div>
                </div>
            </article>
        </div>

        <!-- Assistance Banner -->
        <div
            class="flex flex-col justify-between gap-4 rounded-2xl border border-border bg-muted/30 p-5 sm:flex-row sm:items-center sm:p-6"
        >
            <div class="flex items-start gap-3.5">
                <div
                    class="grid size-10 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary"
                >
                    <HelpCircle class="size-5" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-foreground">
                        Butuh Informasi Lebih Lanjut Mengenai Paket?
                    </h3>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Semua paket dan harga diatur secara transparan oleh
                        Admin. Hubungi admin kami jika Anda membutuhkan paket
                        kustom untuk acara pernikahan atau kegiatan kantor Anda.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
