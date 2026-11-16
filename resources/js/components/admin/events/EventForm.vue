<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Copy, ExternalLink, LockKeyhole, QrCode } from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import ImageUpload from '@/components/ImageUpload.vue';
import EventDatePicker from '@/components/EventDatePicker.vue';
import InputError from '@/components/InputError.vue';
import QrCardModal from '@/components/QrCardModal.vue';
import SearchableSelect, {
    type SearchableSelectOption,
} from '@/components/SearchableSelect.vue';
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

type EventRecord = {
    id: string;
    slug: string;
    category: string;
    status: string;
    is_manual_status?: boolean;
    party_one_name: string;
    party_two_name: string | null;
    event_date: string | null;
    venue: string | null;
    notes: string | null;
    gallery_visibility: string;
    is_demo: boolean;
    package_id: string | null;
    theme_id: string | null;
    custom_colors?: CustomColorsValue | null;
    user_id?: string | null;
    cover_url?: string | null;
};

type PackageOption = {
    id: string;
    name: string;
    price: number;
    gallery_days: number;
    max_frames: number;
    allow_zip_download: boolean;
    allow_custom_theme: boolean;
    allow_video: boolean;
    max_video_seconds: number;
};

const props = defineProps<{
    mode: 'create' | 'edit';
    event?: EventRecord;
    ownerUrl?: string;
    guestUrl?: string;
    eventColors?: { primary: string; accent: string; neutral: string };
    packages: PackageOption[];
    themes: ThemeOption[];
    clients?: Array<{ id: string; name: string; email: string }>;
}>();

const isEditing = props.mode === 'edit' && props.event;
const fieldPrefix = props.mode === 'edit' ? 'edit' : 'create';

const initialTheme = props.themes.find(
    (t) => String(t.id) === String(props.event?.theme_id ?? ''),
);

const form = useForm({
    user_id: props.event?.user_id ? String(props.event.user_id) : 'none',
    package_id: String(props.event?.package_id ?? props.packages[0]?.id ?? ''),
    theme_id: props.event?.theme_id ? String(props.event.theme_id) : 'none',
    use_custom_colors: Boolean(props.event?.custom_colors?.primary),
    custom_colors: props.event?.custom_colors
        ? { ...props.event.custom_colors }
        : initialTheme
          ? {
                primary: initialTheme.primary_color,
                accent: initialTheme.accent_color,
                neutral: initialTheme.neutral_color,
            }
          : { primary: '#1d1c29', accent: '#ff5d6c', neutral: '#f8f6f2' },
    category: props.event?.category ?? 'wedding',
    status: props.event?.status ?? 'draft',
    is_manual_status: Boolean(props.event?.is_manual_status),
    party_one_name: props.event?.party_one_name ?? '',
    party_two_name: props.event?.party_two_name ?? '',
    event_date: props.event?.event_date
        ? props.event.event_date.slice(0, 10)
        : '',
    venue: props.event?.venue ?? '',
    notes: props.event?.notes ?? '',
    gallery_visibility: props.event?.gallery_visibility ?? 'public',
    is_demo: props.event?.is_demo ?? false,
    cover: null as File | null,
    remove_cover: false,
});

const clientOptions = computed<SearchableSelectOption[]>(() => {
    const list: SearchableSelectOption[] = [
        {
            value: 'none',
            label: 'Tanpa Akun Klien (Kelola Mandiri oleh Admin)',
        },
    ];

    for (const c of props.clients ?? []) {
        list.push({
            value: String(c.id),
            label: c.name,
            sublabel: c.email,
        });
    }

    return list;
});

const selectedPackage = computed(() =>
    props.packages.find((p) => String(p.id) === String(form.package_id)),
);
const allowCustomTheme = computed(() =>
    Boolean(selectedPackage.value?.allow_custom_theme),
);

function submit() {
    const transformedForm = form.transform((data) => ({
        ...data,
        ...(isEditing ? { _method: 'put' } : {}),
        user_id:
            data.user_id === 'none' || data.user_id === ''
                ? null
                : data.user_id,
        theme_id:
            !allowCustomTheme.value ||
            data.theme_id === 'none' ||
            data.theme_id === ''
                ? null
                : data.theme_id,
        use_custom_colors: allowCustomTheme.value && data.use_custom_colors,
        custom_colors:
            allowCustomTheme.value && data.use_custom_colors
                ? data.custom_colors
                : null,
        package_id: data.package_id || null,
    }));

    if (isEditing && props.event) {
        transformedForm.post(`/events/${props.event.id}`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => form.reset('cover', 'remove_cover'),
        });
        return;
    }

    transformedForm.post('/events');
}

async function copyOwnerUrl() {
    if (!props.ownerUrl) return;
    try {
        await navigator.clipboard.writeText(props.ownerUrl);
        toast.success('Tautan pribadi berhasil disalin.');
    } catch {
        toast.error(
            'Tautan belum bisa disalin. Pilih dan salin tautan pada kolom.',
        );
    }
}

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

const qrOpen = ref(false);
</script>

<template>
    <form class="flex flex-col gap-5 p-4 sm:p-6" @submit.prevent="submit">
        <header
            class="flex flex-col justify-between gap-4 border-b pb-4 sm:flex-row sm:items-center"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1
                        class="notranslate text-2xl font-bold tracking-tight text-foreground"
                        translate="no"
                    >
                        <template v-if="isEditing && event">
                            {{ event.party_one_name
                            }}<span v-if="event.party_two_name">
                                &amp; {{ event.party_two_name }}</span
                            >
                        </template>
                        <template v-else>Buat Acara Baru</template>
                    </h1>
                    <Badge
                        v-if="isEditing && event"
                        :variant="statusVariant[event.status] ?? 'secondary'"
                    >
                        {{ statusLabel[event.status] ?? event.status }}
                    </Badge>
                </div>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    <template v-if="isEditing && event">
                        Tautan halaman tamu: /e/{{ event.slug }}
                    </template>
                    <template v-else>
                        Isi rincian acara dan paket. Tautan tamu akan dibuat
                        otomatis saat disimpan.
                    </template>
                </p>
            </div>
            <div
                class="grid grid-cols-2 gap-2.5 sm:flex sm:flex-wrap sm:items-center"
            >
                <Button
                    v-if="isEditing && event"
                    type="button"
                    variant="outline"
                    class="col-span-2 sm:col-span-1"
                    as-child
                >
                    <a :href="`/e/${event.slug}`" target="_blank">
                        <ExternalLink class="size-4" aria-hidden="true" />
                        Buka Halaman Tamu
                    </a>
                </Button>
                <Button
                    v-if="isEditing && event && guestUrl && eventColors"
                    type="button"
                    variant="outline"
                    @click="qrOpen = true"
                >
                    <QrCode class="size-4" aria-hidden="true" />
                    QR Code
                </Button>
                <Button type="button" variant="outline" as-child>
                    <Link href="/events">
                        {{ isEditing ? 'Kembali' : 'Batal' }}
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2" />
                    {{
                        form.processing
                            ? 'Menyimpan...'
                            : isEditing
                              ? 'Simpan Perubahan'
                              : 'Buat Acara'
                    }}
                </Button>
            </div>
        </header>

        <div class="grid gap-5 xl:grid-cols-12 xl:items-start">
            <section
                class="space-y-4 rounded-xl border bg-card p-4 shadow-xs sm:p-5 xl:col-span-7"
            >
                <div>
                    <h2 class="text-base font-semibold text-foreground">
                        Detail Acara
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        Nama, jadwal, dan lokasi yang tampil di halaman tamu.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-1.5">
                        <Label :for="`${fieldPrefix}-party-one`">
                            Nama Pihak Pertama
                        </Label>
                        <Input
                            :id="`${fieldPrefix}-party-one`"
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
                        <InputError :message="form.errors.party_one_name" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label :for="`${fieldPrefix}-party-two`">
                            Nama Pihak Kedua (opsional)
                        </Label>
                        <Input
                            :id="`${fieldPrefix}-party-two`"
                            v-model="form.party_two_name"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="words"
                            spellcheck="false"
                            translate="no"
                            data-gramm="false"
                            placeholder="Contoh: Rizky"
                        />
                        <InputError :message="form.errors.party_two_name" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-1.5">
                        <Label :for="`${fieldPrefix}-category`">
                            Kategori Acara
                        </Label>
                        <Select v-model="form.category">
                            <SelectTrigger :id="`${fieldPrefix}-category`">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="wedding"
                                    >Pernikahan</SelectItem
                                >
                                <SelectItem value="birthday"
                                    >Ulang Tahun</SelectItem
                                >
                                <SelectItem value="corporate"
                                    >Acara Kantor</SelectItem
                                >
                                <SelectItem value="other"
                                    >Acara Lainnya</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.category" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label :for="`${fieldPrefix}-event-date`">
                            Tanggal Acara
                        </Label>
                        <EventDatePicker
                            :id="`${fieldPrefix}-event-date`"
                            v-model="form.event_date"
                            :invalid="Boolean(form.errors.event_date)"
                        />
                        <InputError :message="form.errors.event_date" />
                    </div>
                </div>

                <div class="grid gap-1.5">
                    <Label :for="`${fieldPrefix}-venue`">Lokasi / Gedung</Label>
                    <Input
                        :id="`${fieldPrefix}-venue`"
                        v-model="form.venue"
                        placeholder="Contoh: Gedung Serbaguna, Magetan"
                    />
                    <InputError :message="form.errors.venue" />
                </div>
                <div class="grid gap-2">
                    <Label>Foto Acara</Label>
                    <p class="text-xs text-muted-foreground">
                        Tampil di sampul dan profil acara pada halaman tamu.
                    </p>
                    <ImageUpload
                        v-model="form.cover"
                        v-model:removed="form.remove_cover"
                        :existing-url="event?.cover_url"
                        label="Foto acara"
                    />
                    <InputError :message="form.errors.cover" />
                </div>
            </section>

            <section
                class="space-y-4 rounded-xl border bg-card p-4 shadow-xs sm:p-5 xl:col-span-5"
            >
                <div>
                    <h2 class="text-base font-semibold text-foreground">
                        Paket &amp; Pengaturan Galeri
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        Atur status, paket layanan, tema, dan akses galeri.
                    </p>
                </div>

                <div v-if="isEditing" class="grid gap-1.5">
                    <Label :for="`${fieldPrefix}-status`">
                        Status &amp; Kontrol Acara
                    </Label>
                    <Select
                        :model-value="
                            form.is_manual_status ? form.status : 'auto'
                        "
                        @update:model-value="
                            (val) => {
                                if (val === 'auto') {
                                    form.is_manual_status = false;
                                } else {
                                    form.is_manual_status = true;
                                    form.status = val as string;
                                }
                            }
                        "
                    >
                        <SelectTrigger :id="`${fieldPrefix}-status`">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="auto">
                                Otomatis (Berdasarkan Tanggal Acara)
                            </SelectItem>
                            <SelectItem value="active">
                                Aktif Manual (Dibuka oleh Admin)
                            </SelectItem>
                            <SelectItem value="closed">
                                Selesai Manual (Ditutup oleh Admin)
                            </SelectItem>
                            <SelectItem value="draft">
                                Draft Manual (Belum Dibuka)
                            </SelectItem>
                            <SelectItem value="expired">
                                Kadaluarsa Manual
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        class="text-[12px] leading-relaxed text-muted-foreground"
                    >
                        {{
                            form.is_manual_status
                                ? 'Mode Manual: Status dikunci oleh admin. Acara tidak akan ditutup otomatis oleh kalender dan penutupan harus dilakukan manual oleh admin.'
                                : 'Mode Otomatis: Status buka/tutup ditentukan otomatis oleh tanggal acara (Aktif di hari-H, Selesai setelah hari-H lewat).'
                        }}
                    </p>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="grid gap-1.5">
                    <Label :for="`${fieldPrefix}-client`">
                        Akun Klien / Pemilik Acara
                    </Label>
                    <SearchableSelect
                        :id="`${fieldPrefix}-client`"
                        v-model="form.user_id"
                        :options="clientOptions"
                        placeholder="Pilih akun klien..."
                        search-placeholder="Cari nama atau email klien..."
                        empty-text="Tidak ada klien yang cocok dengan pencarian."
                        :invalid="Boolean(form.errors.user_id)"
                    />
                    <p class="text-xs text-muted-foreground">
                        Klien yang ditautkan dapat login dan mengelola detail
                        acara mereka di dashboard klien.
                    </p>
                    <InputError :message="form.errors.user_id" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="grid gap-1.5">
                        <Label :for="`${fieldPrefix}-package`"
                            >Paket Layanan</Label
                        >
                        <Select v-model="form.package_id">
                            <SelectTrigger :id="`${fieldPrefix}-package`">
                                <SelectValue placeholder="Pilih paket" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="item in packages"
                                    :key="item.id"
                                    :value="String(item.id)"
                                >
                                    {{ item.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="selectedPackage"
                            class="text-xs text-muted-foreground"
                        >
                            {{ selectedPackage.gallery_days }} hari galeri ·
                            {{
                                selectedPackage.max_frames <= 1
                                    ? '1 layout foto'
                                    : `1–${selectedPackage.max_frames} layout foto`
                            }}
                            ·
                            {{
                                selectedPackage.allow_video
                                    ? `Video maks ${selectedPackage.max_video_seconds} dtk`
                                    : 'Tanpa video'
                            }}
                            ·
                            {{
                                selectedPackage.allow_zip_download
                                    ? 'ZIP aktif'
                                    : 'Tanpa ZIP'
                            }}
                            ·
                            {{
                                selectedPackage.allow_custom_theme
                                    ? 'Tema kustom'
                                    : 'Tema standar'
                            }}
                        </p>
                        <InputError :message="form.errors.package_id" />
                    </div>
                    <ThemeColorSelector
                        :id-prefix="`${fieldPrefix}-theme`"
                        :allow-custom-theme="allowCustomTheme"
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
                            primary: (form.errors as Record<string, string>)[
                                'custom_colors.primary'
                            ],
                            accent: (form.errors as Record<string, string>)[
                                'custom_colors.accent'
                            ],
                            neutral: (form.errors as Record<string, string>)[
                                'custom_colors.neutral'
                            ],
                        }"
                        @update:theme-id="form.theme_id = $event"
                        @update:use-custom-colors="
                            form.use_custom_colors = $event
                        "
                        @update:custom-colors="form.custom_colors = $event"
                    />
                </div>

                <div class="grid gap-1.5">
                    <Label :for="`${fieldPrefix}-visibility`">
                        Visibilitas Galeri
                    </Label>
                    <Select v-model="form.gallery_visibility">
                        <SelectTrigger :id="`${fieldPrefix}-visibility`">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="public">
                                Publik (semua tamu bisa melihat)
                            </SelectItem>
                            <SelectItem value="private">
                                Privat (hanya admin dan pengantin)
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.gallery_visibility" />
                </div>

                <div class="grid gap-1.5">
                    <Label :for="`${fieldPrefix}-notes`"
                        >Catatan Internal</Label
                    >
                    <Textarea
                        :id="`${fieldPrefix}-notes`"
                        v-model="form.notes"
                        placeholder="Catatan operasional admin..."
                        rows="3"
                    />
                    <InputError :message="form.errors.notes" />
                </div>
                <div
                    v-if="isEditing && ownerUrl"
                    class="space-y-2 border-t pt-4"
                >
                    <Label for="owner-album-url"
                        ><LockKeyhole class="size-4" aria-hidden="true" />Tautan
                        pribadi pemilik acara</Label
                    >
                    <p class="text-xs leading-relaxed text-muted-foreground">
                        Untuk pengantin atau pemilik acara melihat kiriman
                        privat. Bagikan tautan ini hanya kepada mereka.
                    </p>
                    <Input
                        id="owner-album-url"
                        :model-value="ownerUrl"
                        readonly
                    />
                    <Button
                        type="button"
                        variant="outline"
                        @click="copyOwnerUrl"
                        ><Copy class="size-4" aria-hidden="true" />Salin tautan
                        pribadi</Button
                    >
                </div>
            </section>
        </div>
    </form>

    <QrCardModal
        v-if="isEditing && event && guestUrl && eventColors"
        :open="qrOpen"
        :url="guestUrl"
        :party-one-name="event.party_one_name"
        :party-two-name="event.party_two_name"
        :event-date="event.event_date"
        :colors="eventColors"
        @update:open="qrOpen = $event"
    />
</template>
