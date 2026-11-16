<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check, Pencil, X } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Paket & Harga', href: '/packages' },
        ],
    },
});

type PackageRow = {
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
    is_active: boolean;
    benefits: string[] | null;
    events_count?: number;
};

defineProps<{
    packages: PackageRow[];
}>();

const rupiah = (n: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(n);
</script>

<template>
    <Head title="Paket & Harga · Admin" />

    <div class="flex flex-col gap-6 p-4 sm:p-6">
        <header
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Paket &amp; Harga
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Atur harga per acara, pilihan layout foto, batas kiriman
                    video, serta rincian fasilitas tiap paket.
                </p>
            </div>
            <Badge variant="secondary" class="w-fit">
                {{ packages.filter((p) => p.is_active).length }} aktif dari
                {{ packages.length }} paket
            </Badge>
        </header>

        <div
            v-if="packages.length === 0"
            class="rounded-xl border bg-card p-8 text-center shadow-xs"
        >
            <p class="text-base font-semibold text-foreground">
                Belum ada paket terdaftar
            </p>
            <p class="mt-1 text-sm text-muted-foreground">
                Jalankan seeder database untuk memuat daftar paket standar.
            </p>
        </div>

        <div v-else class="flex flex-col gap-4">
            <article
                v-for="pkg in packages"
                :key="pkg.id"
                class="rounded-xl border bg-card p-5 shadow-xs transition-colors hover:border-foreground/20 sm:p-6"
                :class="{ 'opacity-65': !pkg.is_active }"
            >
                <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
                    <div
                        class="flex flex-col justify-between gap-3 lg:col-span-4"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2
                                    class="text-lg font-semibold text-foreground"
                                >
                                    {{ pkg.name }}
                                </h2>
                                <Badge
                                    :variant="
                                        pkg.is_active ? 'default' : 'secondary'
                                    "
                                >
                                    {{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                                <span class="text-xs text-muted-foreground">
                                    /{{ pkg.slug }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{
                                    pkg.tagline ||
                                    'Belum ada deskripsi singkat.'
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Harga per acara
                            </p>
                            <p
                                class="text-2xl font-bold tracking-tight text-foreground"
                            >
                                {{ rupiah(pkg.price) }}
                            </p>
                        </div>
                    </div>

                    <dl
                        class="grid grid-cols-2 gap-3 rounded-lg bg-muted/50 p-4 sm:grid-cols-4 lg:col-span-5 lg:grid-cols-2"
                    >
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Masa Aktif Galeri
                            </dt>
                            <dd
                                class="mt-0.5 text-sm font-semibold text-foreground"
                            >
                                {{ pkg.gallery_days }} hari
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Pilihan Layout Foto
                            </dt>
                            <dd
                                class="mt-0.5 text-sm font-semibold text-foreground"
                            >
                                {{
                                    pkg.max_frames <= 1
                                        ? '1 pose berbingkai'
                                        : `1–${pkg.max_frames} pose`
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Kiriman Video
                            </dt>
                            <dd
                                class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold text-foreground"
                            >
                                <Check
                                    v-if="pkg.allow_video"
                                    class="size-4 text-foreground"
                                    aria-hidden="true"
                                />
                                <X
                                    v-else
                                    class="size-4 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <span>{{
                                    pkg.allow_video
                                        ? `Maks ${pkg.max_video_seconds} dtk`
                                        : 'Tidak termasuk'
                                }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Unduh Arsip ZIP
                            </dt>
                            <dd
                                class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold text-foreground"
                            >
                                <Check
                                    v-if="pkg.allow_zip_download"
                                    class="size-4 text-foreground"
                                    aria-hidden="true"
                                />
                                <X
                                    v-else
                                    class="size-4 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <span>{{
                                    pkg.allow_zip_download
                                        ? 'Termasuk'
                                        : 'Tidak termasuk'
                                }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Tema Warna Custom
                            </dt>
                            <dd
                                class="mt-0.5 flex items-center gap-1.5 text-sm font-semibold text-foreground"
                            >
                                <Check
                                    v-if="pkg.allow_custom_theme"
                                    class="size-4 text-foreground"
                                    aria-hidden="true"
                                />
                                <X
                                    v-else
                                    class="size-4 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <span>{{
                                    pkg.allow_custom_theme
                                        ? 'Termasuk'
                                        : 'Standar'
                                }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Digunakan
                            </dt>
                            <dd
                                class="mt-0.5 text-sm font-semibold text-foreground"
                            >
                                {{ pkg.events_count ?? 0 }} acara
                            </dd>
                        </div>
                    </dl>

                    <div
                        class="flex flex-col justify-between gap-4 lg:col-span-3 lg:items-end"
                    >
                        <div class="w-full">
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Fasilitas ({{ pkg.benefits?.length ?? 0 }}
                                butir)
                            </p>
                            <ul
                                v-if="pkg.benefits && pkg.benefits.length > 0"
                                class="mt-1.5 space-y-1 text-xs text-foreground"
                            >
                                <li
                                    v-for="(benefit, idx) in pkg.benefits"
                                    :key="idx"
                                    class="flex items-start gap-1.5"
                                >
                                    <Check
                                        class="mt-0.5 size-3.5 shrink-0 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    <span>{{ benefit }}</span>
                                </li>
                            </ul>
                            <p
                                v-else
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                Belum ada butir fasilitas.
                            </p>
                        </div>

                        <Button
                            variant="outline"
                            size="sm"
                            class="min-h-10 w-full lg:w-auto"
                            as-child
                        >
                            <Link :href="`/packages/${pkg.id}/edit`">
                                <Pencil
                                    class="mr-1.5 size-3.5"
                                    aria-hidden="true"
                                />
                                Ubah Paket
                            </Link>
                        </Button>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>
