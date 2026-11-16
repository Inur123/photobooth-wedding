<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Check, Lock } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Paket & Harga', href: '/packages' },
            { title: 'Ubah Paket', href: '#' },
        ],
    },
});

type PackageData = {
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
    benefits: string[] | null;
    is_active: boolean;
};

const props = defineProps<{
    package: PackageData;
    coreBenefits: string[];
}>();

const form = useForm({
    name: props.package.name,
    tagline: props.package.tagline ?? '',
    price: props.package.price,
    allow_zip_download: Boolean(props.package.allow_zip_download),
    allow_custom_theme: Boolean(props.package.allow_custom_theme),
    allow_video: Boolean(props.package.allow_video),
    is_active: Boolean(props.package.is_active),
});

const resolvedVideoSeconds = computed(() =>
    form.allow_video
        ? Math.max(5, Number(props.package.max_video_seconds) || 20)
        : 0,
);

const generatedBenefits = computed<string[]>(() => {
    const days = Math.max(1, Number(props.package.gallery_days) || 30);
    const layouts = Math.min(
        4,
        Math.max(1, Number(props.package.max_frames) || 1),
    );

    const items = [...props.coreBenefits];

    items.push(
        layouts === 1
            ? '1 pilihan layout foto (1 pose berbingkai)'
            : `${layouts} pilihan layout foto (1–${layouts} pose)`,
    );

    items.push(
        form.allow_video
            ? resolvedVideoSeconds.value > 0
                ? `Kiriman video (maks ${resolvedVideoSeconds.value} dtk), voice note & ucapan`
                : 'Kiriman video, voice note & ucapan'
            : 'Kiriman voice note & ucapan tertulis',
    );

    if (form.allow_custom_theme) {
        items.push('Tema warna custom (halaman & bingkai)');
    }

    if (form.allow_zip_download) {
        items.push('Unduh semua file (ZIP)');
    }

    items.push(`Galeri aktif ${days} hari`);

    return items;
});

function submit() {
    form.put(`/packages/${props.package.id}`, {
        preserveScroll: true,
    });
}

const rupiah = (n: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(n);
</script>

<template>
    <Head :title="`Ubah Paket ${package.name}`" />

    <form class="flex flex-col gap-5 p-4 sm:p-6" @submit.prevent="submit">
        <header
            class="flex flex-col justify-between gap-4 border-b pb-4 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    Ubah Paket {{ package.name }}
                </h1>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Perubahan harga, fitur tambahan, dan status paket langsung
                    berlaku di katalog depan.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-2.5 sm:flex sm:items-center">
                <Button type="button" variant="outline" as-child>
                    <Link href="/packages">Batal</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </Button>
            </div>
        </header>

        <div class="grid gap-5 xl:grid-cols-12 xl:items-start">
            <div class="flex flex-col gap-5 xl:col-span-5">
                <section class="rounded-xl border bg-card p-5 shadow-xs">
                    <h2 class="text-base font-semibold text-foreground">
                        Informasi &amp; Pengaturan Paket
                    </h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Identitas paket, harga dasar, serta opsi fitur yang
                        dapat diaktifkan.
                    </p>

                    <div class="mt-4 grid gap-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="flex flex-col gap-1.5">
                                <Label for="pkg-name">Nama Paket</Label>
                                <Input
                                    id="pkg-name"
                                    v-model="form.name"
                                    placeholder="Contoh: Basic"
                                    required
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label for="pkg-price"
                                    >Harga ({{
                                        rupiah(form.price || 0)
                                    }})</Label
                                >
                                <Input
                                    id="pkg-price"
                                    v-model.number="form.price"
                                    type="number"
                                    min="0"
                                    required
                                />
                                <p
                                    v-if="form.errors.price"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.price }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <Label for="pkg-tagline"
                                >Kalimat Penjelas (opsional)</Label
                            >
                            <Input
                                id="pkg-tagline"
                                v-model="form.tagline"
                                placeholder="Contoh: Semua fitur inti, siap dipakai di hari-H"
                            />
                            <p
                                v-if="form.errors.tagline"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.tagline }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3 border-t pt-4">
                        <div
                            class="flex items-center justify-between gap-4 rounded-lg bg-muted/50 px-3.5 py-2.5"
                        >
                            <div>
                                <Label
                                    for="pkg-zip"
                                    class="text-sm font-medium text-foreground"
                                >
                                    Unduh Arsip ZIP
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    Izinkan unduh seluruh foto dan video dalam
                                    satu berkas ZIP.
                                </p>
                            </div>
                            <Switch
                                id="pkg-zip"
                                v-model="form.allow_zip_download"
                            />
                        </div>

                        <div
                            class="flex items-center justify-between gap-4 rounded-lg bg-muted/50 px-3.5 py-2.5"
                        >
                            <div>
                                <Label
                                    for="pkg-video"
                                    class="text-sm font-medium text-foreground"
                                >
                                    Kiriman Video (Reels)
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    Izinkan tamu mengunggah atau merekam video
                                    singkat di acara.
                                </p>
                            </div>
                            <Switch id="pkg-video" v-model="form.allow_video" />
                        </div>

                        <div
                            class="flex items-center justify-between gap-4 rounded-lg bg-muted/50 px-3.5 py-2.5"
                        >
                            <div>
                                <Label
                                    for="pkg-custom-theme"
                                    class="text-sm font-medium text-foreground"
                                >
                                    Tema Warna Custom
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    Izinkan pengubah tema preset dan warna
                                    kustom acara.
                                </p>
                            </div>
                            <Switch
                                id="pkg-custom-theme"
                                v-model="form.allow_custom_theme"
                            />
                        </div>

                        <div
                            class="flex items-center justify-between gap-4 rounded-lg bg-muted/50 px-3.5 py-2.5"
                        >
                            <div>
                                <Label
                                    for="pkg-active"
                                    class="text-sm font-medium text-foreground"
                                >
                                    Status Paket Aktif
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    Tampilkan paket ini pada halaman depan
                                    publik.
                                </p>
                            </div>
                            <Switch id="pkg-active" v-model="form.is_active" />
                        </div>
                    </div>
                </section>
            </div>

            <section
                class="rounded-xl border bg-card p-5 shadow-xs xl:col-span-7"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <h2 class="text-base font-semibold text-foreground">
                            Rincian Fasilitas Paket ({{
                                generatedBenefits.length
                            }})
                        </h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Disusun otomatis oleh sistem dari fitur inti, kuota,
                            dan toggle di samping agar selalu sinkron dengan
                            aplikasi.
                        </p>
                    </div>
                    <Badge variant="secondary" class="w-fit shrink-0">
                        <Lock class="mr-1.5 size-3" aria-hidden="true" />
                        Otomatis dari Sistem
                    </Badge>
                </div>

                <dl
                    class="mt-4 grid grid-cols-1 gap-3 rounded-lg border border-border bg-muted/40 p-3.5 sm:grid-cols-3"
                >
                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Masa Aktif Galeri (Regulasi)
                        </dt>
                        <dd
                            class="mt-0.5 text-sm font-semibold text-foreground"
                        >
                            {{ package.gallery_days }} hari
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Pilihan Layout (Regulasi)
                        </dt>
                        <dd
                            class="mt-0.5 text-sm font-semibold text-foreground"
                        >
                            {{
                                package.max_frames <= 1
                                    ? '1 pose berbingkai'
                                    : `1–${package.max_frames} pose`
                            }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Batas Video Tamu
                        </dt>
                        <dd
                            class="mt-0.5 text-sm font-semibold text-foreground"
                        >
                            {{
                                form.allow_video
                                    ? `Maks ${resolvedVideoSeconds} detik`
                                    : 'Tidak aktif'
                            }}
                        </dd>
                    </div>
                </dl>

                <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                    <li
                        v-for="benefit in generatedBenefits"
                        :key="benefit"
                        class="flex items-start gap-2.5 rounded-lg border border-border bg-muted/30 px-3.5 py-2.5 text-sm text-foreground"
                    >
                        <span
                            class="mt-0.5 grid size-4 shrink-0 place-items-center rounded-full bg-primary/10 text-primary"
                        >
                            <Check
                                class="size-2.5 stroke-[3]"
                                aria-hidden="true"
                            />
                        </span>
                        <span class="leading-snug">{{ benefit }}</span>
                    </li>
                </ul>

                <p
                    class="mt-4 rounded-lg border border-dashed border-border bg-muted/20 px-3.5 py-2.5 text-xs text-muted-foreground"
                >
                    Masa aktif galeri dan pilihan layout foto mengikuti regulasi
                    paket. Aktifkan toggle
                    <strong>Kiriman Video</strong>,
                    <strong>Unduh Arsip ZIP</strong>, atau
                    <strong>Tema Warna Custom</strong> di sebelah kiri untuk
                    memperbarui fasilitas paket ini secara otomatis.
                </p>
            </section>
        </div>
    </form>
</template>
