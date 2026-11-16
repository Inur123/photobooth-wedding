<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    AudioLines,
    Camera,
    Film,
    ImagePlus,
    LoaderCircle,
    MessageSquare,
    Globe,
    LockKeyhole,
    Send,
    Square,
    Upload,
    X,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import PhotoboothPreview from '@/components/event/PhotoboothPreview.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useGuestCapture } from '@/composables/useGuestCapture';
import { eventName, eventTheme } from '@/lib/eventTheme';
import { photoboothLayout } from '@/lib/photobooth';
import type { ContributionType, GuestEvent } from '@/types/event';

const props = defineProps<{ event: GuestEvent }>();
const emit = defineEmits<{ close: []; sent: [type: ContributionType] }>();
const chosen = ref(false);
const guestName = ref('');
const message = ref('');
const visibility = ref<'public' | 'private'>(
    props.event.gallery_visibility === 'private' ? 'private' : 'public',
);
const submitting = ref(false);
const uploadProgress = ref(0);
const fileInput = ref<HTMLInputElement | null>(null);
const formMountTime = ref(0);
const {
    mode,
    videoEl,
    cameraReady,
    requesting,
    recording,
    capturing,
    elapsed,
    shotCount,
    shots,
    layout,
    preview,
    file,
    error,
    notice,
    reset,
    setMode,
    setLayout,
    startCamera,
    capture,
    startRecording,
    stopRecording,
    upload,
} = useGuestCapture(props.event);
const styles = computed(() => eventTheme(props.event));
const photoGeometry = computed(() => photoboothLayout(layout.value));
const showPhotobooth = computed(
    () => chosen.value && mode.value === 'photo' && !preview.value,
);
const modes = computed(() => {
    const maxLayout = Math.max(...props.event.capabilities.photo_layouts, 1);

    return [
        {
            type: 'photo' as const,
            title: 'Foto',
            description:
                maxLayout === 1
                    ? 'Satu foto dengan bingkai acara.'
                    : `Satu foto atau kolase hingga ${maxLayout} pose.`,
            icon: Camera,
            destination: 'Masuk ke Album',
        },
        {
            type: 'video' as const,
            title: 'Video',
            description: `Cerita bergerak, sampai ${props.event.capabilities.max_video_seconds} detik.`,
            icon: Film,
            destination: 'Masuk ke Reels',
        },
        {
            type: 'wish' as const,
            title: 'Ucapan',
            description: 'Doa, harapan, atau cerita kecil.',
            icon: MessageSquare,
            destination: 'Masuk ke Catatan',
        },
        {
            type: 'voice' as const,
            title: 'Voice note',
            description: `Titipkan pesan suara, sampai ${props.event.capabilities.max_voice_seconds} detik.`,
            icon: AudioLines,
            destination: 'Masuk ke Catatan',
        },
    ];
});
const visibleModes = computed(() =>
    modes.value.filter((item) =>
        props.event.capabilities.types.includes(item.type),
    ),
);
const current = computed(() =>
    modes.value.find((item) => item.type === mode.value)!,
);
const maxRecordingSeconds = computed(() =>
    mode.value === 'video'
        ? props.event.capabilities.max_video_seconds
        : props.event.capabilities.max_voice_seconds,
);
const maxRecordingFormatted = computed(() => {
    const secs = maxRecordingSeconds.value;
    return `${String(Math.floor(secs / 60)).padStart(2, '0')}:${String(secs % 60).padStart(2, '0')}`;
});
const maxUploadMb = computed(() =>
    mode.value === 'photo'
        ? Math.round(props.event.capabilities.max_photo_kb / 1024)
        : mode.value === 'video'
          ? Math.round(props.event.capabilities.max_video_kb / 1024)
          : Math.round(props.event.capabilities.max_voice_kb / 1024),
);
const working = computed(
    () =>
        submitting.value ||
        recording.value ||
        capturing.value ||
        requesting.value,
);
const accept = computed(() =>
    mode.value === 'photo'
        ? 'image/jpeg,image/png,image/webp,image/heic,image/heif'
        : mode.value === 'video'
          ? 'video/mp4,video/webm,video/quicktime'
          : 'audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/webm,audio/ogg,audio/wav,audio/x-wav',
);
const timer = computed(
    () =>
        `${String(Math.floor(elapsed.value / 60)).padStart(2, '0')}:${String(elapsed.value % 60).padStart(2, '0')}`,
);
const submitLabel = computed(() => {
    if (!submitting.value) return 'Titipkan momen';
    if (uploadProgress.value >= 100) return 'Menyimpan ke galeri…';
    if (uploadProgress.value > 0) return `Mengunggah ${uploadProgress.value}%`;
    return 'Mengirim…';
});

onMounted(() => {
    formMountTime.value = Math.floor(Date.now() / 1000);
    try {
        guestName.value =
            sessionStorage.getItem(`guest-name:${props.event.slug}`) ?? '';
    } catch {
        /* Storage is optional in private browsing. */
    }
});

function choose(type: ContributionType) {
    setMode(type);
    chosen.value = true;
    message.value = '';
}

function close() {
    if (submitting.value) return;
    reset();
    emit('close');
}

function back() {
    reset();
    chosen.value = false;
}

async function selectFiles(event: Event) {
    const input = event.target as HTMLInputElement;
    await upload(input.files);
    input.value = '';
}

function send() {
    if (working.value) return;
    if (!guestName.value.trim()) {
        error.value = 'Isi namamu sebelum menitipkan momen.';
        return;
    }
    if (mode.value === 'wish' && !message.value.trim()) {
        error.value = 'Tulis ucapanmu terlebih dahulu.';
        return;
    }
    if (mode.value !== 'wish' && !file.value) {
        error.value = 'Ambil atau pilih file terlebih dahulu.';
        return;
    }
    if (mode.value !== 'wish' && file.value) {
        const maxKb =
            mode.value === 'video'
                ? props.event.capabilities.max_video_kb || 25600
                : mode.value === 'voice'
                  ? props.event.capabilities.max_voice_kb || 5120
                  : props.event.capabilities.max_photo_kb || 4096;
        const maxBytes = maxKb * 1024;
        if (file.value.size > maxBytes) {
            const maxMb = Math.round(maxKb / 1024);
            const sizeMb = (file.value.size / (1024 * 1024)).toFixed(1);
            error.value = `Ukuran file (${sizeMb} MB) melebihi batas maksimal (${maxMb} MB). Silakan pilih atau rekam file yang lebih ringkas.`;
            return;
        }
    }
    const data = new FormData();
    const type = mode.value;
    data.set('type', type);
    data.set('guest_name', guestName.value.trim());
    data.set('visibility', visibility.value);
    if (message.value.trim()) data.set('message', message.value.trim());
    if (file.value) data.set('file', file.value);
    if (type === 'photo') {
        data.set('layout', String(layout.value));
    }
    if (type === 'video' || type === 'voice')
        data.set('duration_seconds', String(elapsed.value));
    // Anti-spam: timestamp form dibuka (server cek apakah terlalu cepat)
    data.set('_ts', String(formMountTime.value));
    // client_upload_id untuk idempotency (cegah double submit)
    data.set('client_upload_id', crypto.randomUUID());
    submitting.value = true;
    error.value = '';
    router.post(`/e/${props.event.slug}/contributions`, data, {
        preserveScroll: true,
        onProgress: (progress) => {
            uploadProgress.value = progress?.percentage ?? 0;
        },
        onError: (errors) => {
            error.value =
                Object.values(errors)[0] ??
                'Kiriman belum tersimpan. Coba lagi.';
        },
        onSuccess: () => {
            try {
                sessionStorage.setItem(
                    `guest-name:${props.event.slug}`,
                    guestName.value.trim(),
                );
            } catch {
                /* The submission also works without storage. */
            }
            reset();
            emit('sent', type);
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
}
</script>

<template>
    <Dialog
        :open="true"
        @update:open="
            (open) => {
                if (!open) close();
            }
        "
    >
        <DialogContent
            :style="styles"
            class="guest-dialog composer-dialog"
            :class="{ 'composer-dialog--photobooth': showPhotobooth }"
            :show-close-button="false"
            @keydown.esc.stop.prevent="close"
            @escape-key-down="
                (event) => {
                    if (submitting) event.preventDefault();
                }
            "
            @interact-outside="(event) => event.preventDefault()"
        >
            <header class="composer-heading">
                <button
                    v-if="chosen"
                    type="button"
                    class="event-icon-button"
                    aria-label="Kembali ke pilihan kiriman"
                    :disabled="submitting"
                    @click="back"
                >
                    <ArrowLeft aria-hidden="true" />
                </button>
                <div>
                    <DialogTitle class="composer-title">{{
                        chosen
                            ? `Titip ${current.title.toLowerCase()}`
                            : 'Mau titip apa hari ini?'
                    }}</DialogTitle
                    ><DialogDescription
                        class="composer-description notranslate"
                        translate="no"
                        >{{
                            chosen
                                ? current.destination
                                : `Untuk ${eventName(event)}`
                        }}</DialogDescription
                    >
                </div>
                <DialogClose as-child
                    ><button
                        type="button"
                        class="event-icon-button composer-close"
                        aria-label="Tutup kiriman"
                        :disabled="submitting"
                    >
                        <X aria-hidden="true" /></button
                ></DialogClose>
            </header>

            <div
                v-if="!chosen"
                class="mode-choices"
                :class="{ 'mode-choices--three': visibleModes.length === 3 }"
            >
                <button
                    v-for="item in visibleModes"
                    :key="item.type"
                    class="mode-choice"
                    type="button"
                    @click="choose(item.type)"
                >
                    <span class="mode-choice-icon"
                        ><component :is="item.icon" aria-hidden="true"
                    /></span>
                    <span class="mode-choice-copy"
                        ><strong>{{ item.title }}</strong
                        ><span>{{ item.description }}</span
                        ><small>{{ item.destination }}</small></span
                    >
                </button>
                <p class="composer-privacy">
                    Kamera dan mikrofon hanya aktif setelah kamu mengizinkannya.
                </p>
            </div>

            <form v-else class="composer-form" @submit.prevent="send">
                <!-- Honeypot anti-bot: field ini tidak boleh diisi manusia -->
                <input
                    name="_hp"
                    type="text"
                    tabindex="-1"
                    autocomplete="off"
                    aria-hidden="true"
                    style="
                        position: absolute;
                        left: -9999px;
                        width: 1px;
                        height: 1px;
                        opacity: 0;
                        pointer-events: none;
                    "
                />
                <div class="composer-field">
                    <Label for="guest-name">Namamu</Label
                    ><Input
                        id="guest-name"
                        v-model="guestName"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="words"
                        spellcheck="false"
                        translate="no"
                        data-gramm="false"
                        placeholder="Nama yang tampil di kiriman"
                        maxlength="60"
                        required
                        :disabled="submitting"
                    />
                </div>
                <p v-if="error" class="guest-error" role="alert">{{ error }}</p>

                <template
                    v-if="
                        mode === 'photo' &&
                        event.capabilities.photo_layouts.length > 1
                    "
                >
                    <fieldset
                        class="layout-picker"
                        :disabled="working || !!preview"
                    >
                        <legend>Layout foto</legend>
                        <div
                            class="layout-options"
                            :class="{
                                'layout-options--2':
                                    event.capabilities.photo_layouts.length ===
                                    2,
                                'layout-options--3':
                                    event.capabilities.photo_layouts.length ===
                                    3,
                            }"
                        >
                            <button
                                v-for="count in event.capabilities
                                    .photo_layouts"
                                :key="count"
                                type="button"
                                :aria-pressed="layout === count"
                                :disabled="working || !!preview"
                                @click="setLayout(count)"
                            >
                                <span
                                    class="layout-preview"
                                    :class="`layout-${count}`"
                                    aria-hidden="true"
                                    ><i v-for="n in count" :key="n" /></span
                                ><span>{{
                                    count === 1 ? 'Satu foto' : `${count} pose`
                                }}</span>
                            </button>
                        </div>
                    </fieldset>
                </template>

                <div
                    v-if="mode !== 'wish'"
                    class="capture-workspace"
                    :class="{ 'with-photobooth': showPhotobooth }"
                >
                    <div
                        class="capture-stage"
                        :class="{
                            'is-voice': mode === 'voice',
                            'has-preview': !!preview,
                        }"
                    >
                        <img
                            v-if="preview && mode === 'photo'"
                            :src="preview"
                            alt="Pratinjau foto sebelum dikirim"
                            :width="photoGeometry.width"
                            :height="photoGeometry.height"
                            class="capture-preview"
                        />
                        <video
                            v-else-if="preview && mode === 'video'"
                            :src="preview"
                            controls
                            playsinline
                            class="capture-preview"
                            aria-label="Pratinjau video sebelum dikirim"
                        />
                        <div v-else-if="mode === 'voice'" class="voice-stage">
                            <span
                                class="voice-stage-icon"
                                :class="{ 'is-recording': recording }"
                                ><AudioLines aria-hidden="true" /></span
                            ><strong>{{
                                recording
                                    ? timer
                                    : preview
                                      ? 'Dengarkan dulu ceritamu.'
                                      : 'Ada cerita dalam suaramu.'
                            }}</strong>
                            <p v-if="!preview">
                                {{
                                    recording
                                        ? 'Sedang merekam. Berhenti saat sudah selesai.'
                                        : `Rekam pesan singkat, maksimal ${props.event.capabilities.max_voice_seconds} detik.`
                                }}
                            </p>
                            <audio
                                v-if="preview"
                                :src="preview"
                                controls
                                aria-label="Pratinjau voice note"
                            />
                        </div>
                        <template v-else>
                            <video
                                v-show="cameraReady"
                                ref="videoEl"
                                autoplay
                                playsinline
                                muted
                                class="capture-live"
                                :class="{ 'is-mirrored': mode === 'photo' }"
                                aria-label="Pratinjau kamera langsung"
                            />
                            <div v-if="!cameraReady" class="camera-placeholder">
                                <component
                                    :is="mode === 'photo' ? Camera : Film"
                                    aria-hidden="true"
                                /><strong>{{
                                    mode === 'photo'
                                        ? 'Senyummu, sudut pandangmu.'
                                        : 'Rekam cerita hari ini.'
                                }}</strong>
                                <p>
                                    Aktifkan kamera atau pilih file dari
                                    perangkat.
                                </p>
                            </div>
                            <span
                                v-if="cameraReady && mode === 'photo'"
                                class="capture-status"
                                role="status"
                                >{{ shotCount }} / {{ layout }} pose</span
                            >
                            <span
                                v-if="recording"
                                class="recording-status"
                                role="status"
                                ><i />{{ timer }} /
                                {{ maxRecordingFormatted }}</span
                            >
                        </template>
                    </div>
                    <PhotoboothPreview
                        v-if="showPhotobooth"
                        :event="event"
                        :layout="layout"
                        :shots="shots"
                        :video="videoEl"
                        :live="cameraReady"
                    />
                </div>

                <p v-if="notice" class="event-status-message" role="status">
                    {{ notice }}
                </p>

                <div v-if="mode !== 'wish' && !preview" class="capture-actions">
                    <button
                        v-if="!cameraReady"
                        type="button"
                        class="event-primary"
                        :disabled="working"
                        @click="startCamera"
                    >
                        <LoaderCircle
                            v-if="requesting"
                            class="animate-spin"
                            aria-hidden="true"
                        /><component
                            :is="mode === 'voice' ? AudioLines : Camera"
                            v-else
                            aria-hidden="true"
                        />{{
                            requesting
                                ? 'Meminta izin…'
                                : mode === 'voice'
                                  ? 'Aktifkan mikrofon'
                                  : 'Aktifkan kamera'
                        }}
                    </button>
                    <button
                        v-else-if="mode === 'photo'"
                        type="button"
                        class="event-primary"
                        :disabled="capturing"
                        @click="capture"
                    >
                        <Camera aria-hidden="true" />{{
                            capturing
                                ? `Mengambil pose ${Math.min(shotCount + 1, layout)}…`
                                : shotCount >= layout
                                  ? 'Proses foto'
                                  : layout === 1
                                    ? 'Ambil foto'
                                    : `Ambil foto ${shotCount + 1} dari ${layout}`
                        }}
                    </button>
                    <button
                        v-else
                        type="button"
                        class="event-primary"
                        @click="recording ? stopRecording() : startRecording()"
                    >
                        <Square v-if="recording" aria-hidden="true" /><component
                            :is="mode === 'voice' ? AudioLines : Film"
                            v-else
                            aria-hidden="true"
                        />{{ recording ? 'Selesai merekam' : 'Mulai merekam' }}
                    </button>
                    <button
                        v-if="!recording"
                        type="button"
                        class="event-secondary"
                        :disabled="working"
                        @click="fileInput?.click()"
                    >
                        <Upload aria-hidden="true" />Pilih file
                    </button>
                    <input
                        ref="fileInput"
                        type="file"
                        hidden
                        :accept="accept"
                        :multiple="mode === 'photo' && layout > 1"
                        aria-label="Pilih file kiriman"
                        @change="selectFiles"
                    />
                    <span class="capture-help">{{
                        mode === 'photo'
                            ? `Pilih ${layout} foto · JPG, PNG, WebP · maks ${maxUploadMb} MB/foto`
                            : `Maksimal ${maxRecordingSeconds} detik · ${maxUploadMb} MB`
                    }}</span>
                </div>

                <div v-if="mode === 'wish' || preview" class="composer-field">
                    <Label for="guest-message"
                        >{{
                            mode === 'wish'
                                ? 'Ucapan untuk yang punya hari'
                                : 'Cerita di balik momen ini'
                        }}<span v-if="mode !== 'wish'" class="optional-label">
                            (opsional)</span
                        ></Label
                    ><Textarea
                        id="guest-message"
                        v-model="message"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        translate="no"
                        data-gramm="false"
                        :placeholder="
                            mode === 'wish'
                                ? 'Semoga hari-hari kalian selalu penuh…'
                                : 'Tulis sedikit cerita…'
                        "
                        :rows="mode === 'wish' ? 6 : 3"
                        maxlength="500"
                        :disabled="submitting"
                    /><span class="message-count"
                        >{{ message.length }}/500</span
                    >
                </div>
                <fieldset
                    v-if="mode === 'wish' || preview"
                    class="contribution-visibility"
                    :disabled="submitting"
                >
                    <legend>Siapa yang boleh melihat?</legend>
                    <div class="visibility-options">
                        <label
                            v-if="event.gallery_visibility !== 'private'"
                            :class="{ 'is-selected': visibility === 'public' }"
                        >
                            <input
                                v-model="visibility"
                                type="radio"
                                name="visibility"
                                value="public"
                            />
                            <Globe aria-hidden="true" />
                            <span
                                ><strong>Publik</strong
                                ><small
                                    >Bisa dilihat semua tamu di galeri.</small
                                ></span
                            >
                        </label>
                        <label
                            :class="{ 'is-selected': visibility === 'private' }"
                        >
                            <input
                                v-model="visibility"
                                type="radio"
                                name="visibility"
                                value="private"
                            />
                            <LockKeyhole aria-hidden="true" />
                            <span
                                ><strong>Privat</strong
                                ><small
                                    >Hanya pemilik acara dan admin.</small
                                ></span
                            >
                        </label>
                    </div>
                    <p
                        v-if="event.gallery_visibility === 'private'"
                        class="composer-privacy"
                    >
                        Galeri acara ini privat. Kirimanmu hanya terlihat oleh
                        pemilik acara dan admin.
                    </p>
                </fieldset>
                <div v-if="mode === 'wish' || preview" class="composer-submit">
                    <button
                        v-if="preview"
                        type="button"
                        class="event-secondary"
                        :disabled="submitting"
                        @click="reset"
                    >
                        <ImagePlus aria-hidden="true" />Ulangi</button
                    ><button
                        type="submit"
                        class="event-primary"
                        :disabled="submitting"
                    >
                        <LoaderCircle
                            v-if="submitting"
                            class="animate-spin"
                            aria-hidden="true"
                        /><Send v-else aria-hidden="true" />{{ submitLabel }}
                    </button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
