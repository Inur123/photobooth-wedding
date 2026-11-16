<script setup lang="ts">
import { Download, Link as LinkIcon, Printer, X } from '@lucide/vue';
import { toPng } from 'html-to-image';
import QRCode from 'qrcode';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { eventDate } from '@/lib/eventTheme';

const props = defineProps<{
    open: boolean;
    url: string;
    partyOneName: string;
    partyTwoName: string | null;
    eventDate: string | null;
    colors: { primary: string; accent: string; neutral: string };
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const qrDataUrl = ref('');
const cardRef = ref<HTMLElement | null>(null);
const downloading = ref(false);

const displayName = computed(() =>
    [props.partyOneName, props.partyTwoName].filter(Boolean).join(' & '),
);

const formattedDate = computed(() => eventDate(props.eventDate));

function luminance(hex: string): number {
    const validHex = /^#[\da-f]{6}$/i.test(hex) ? hex : '#f8f6f2';
    const r = parseInt(validHex.slice(1, 3), 16) / 255;
    const g = parseInt(validHex.slice(3, 5), 16) / 255;
    const b = parseInt(validHex.slice(5, 7), 16) / 255;
    const toLinear = (c: number) =>
        c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    return 0.2126 * toLinear(r) + 0.7152 * toLinear(g) + 0.0722 * toLinear(b);
}

const bgColor = computed(() => props.colors.neutral || '#f8f6f2');
const isDark = computed(() => luminance(bgColor.value) < 0.35);

const inkColor = computed(() => {
    if (isDark.value) {
        return '#f8f6f2';
    }
    return props.colors.primary || '#1d1c29';
});

const accentColor = computed(() => props.colors.accent || '#ff5d6c');

async function generateQr() {
    if (!props.url) return;
    try {
        qrDataUrl.value = await QRCode.toDataURL(props.url, {
            width: 720,
            margin: 1,
            color: {
                dark: inkColor.value,
                light: isDark.value ? '#1d1c2900' : '#ffffff00',
            },
            errorCorrectionLevel: 'H',
        });
    } catch (e) {
        console.error('Failed generating QR code', e);
    }
}

onMounted(generateQr);

watch(
    () => [props.url, inkColor.value, props.open],
    ([newUrl, , isOpen]) => {
        if (isOpen && newUrl) {
            generateQr();
        }
    },
);

async function downloadCard() {
    if (!cardRef.value || downloading.value) return;
    downloading.value = true;
    try {
        const dataUrl = await toPng(cardRef.value, {
            pixelRatio: 3,
            cacheBust: true,
        });
        const link = document.createElement('a');
        const filename = `kartu-qr-${props.partyOneName.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.png`;
        link.download = filename;
        link.href = dataUrl;
        link.click();
        toast.success('Kartu QR berhasil diunduh siap cetak.');
    } catch {
        toast.error('Gagal mengunduh kartu QR. Silakan coba lagi.');
    } finally {
        downloading.value = false;
    }
}

async function copyUrl() {
    try {
        await navigator.clipboard.writeText(props.url);
        toast.success('Tautan tamu berhasil disalin.');
    } catch {
        toast.error('Gagal menyalin tautan.');
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent :show-close-button="false" class="max-h-[92vh] max-w-sm gap-0 overflow-y-auto p-0 sm:max-w-md">
            <DialogTitle class="sr-only">Kartu QR Photobooth</DialogTitle>
            <DialogDescription class="sr-only">
                Kartu QR bergaya photobooth strip siap cetak untuk tamu
            </DialogDescription>

            <div class="flex flex-col items-center gap-5 p-5 sm:p-6">
                <!-- Preview Header Info -->
                <div class="flex w-full items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-foreground">
                            Kartu QR
                        </h3>
                        <p class="text-xs text-muted-foreground">
                            Desain serasi tema acara, siap cetak untuk undangan.
                        </p>
                    </div>
                    <DialogClose as-child>
                        <Button type="button" variant="ghost" size="icon" class="size-8 shrink-0" aria-label="Tutup">
                            <X class="size-4" aria-hidden="true" />
                        </Button>
                    </DialogClose>
                </div>

                <!-- Printable Photobooth Strip Card -->
                <div ref="cardRef" class="qr-strip-card" :style="{
                    '--qr-bg': bgColor,
                    '--qr-ink': inkColor,
                    '--qr-accent': accentColor,
                }">
                    <!-- Inner Border Frame (matching photobooth strip design) -->
                    <div class="qr-strip-inner">
                        <!-- Strip Top Header -->
                        <div class="qr-strip-header">
                            <span class="qr-strip-brand">titip.moment</span>
                            <span class="qr-strip-subtitle">scan photobooth</span>
                        </div>

                        <!-- 1 QR Code Frame Slot (replaces 3 photo slots) -->
                        <div class="qr-slot-container">
                            <div class="qr-slot-frame">
                                <img v-if="qrDataUrl" :src="qrDataUrl" alt="QR Code Photobooth" class="qr-slot-image" />
                                <div v-else class="qr-slot-loading">
                                    Membuat QR Code…
                                </div>
                            </div>
                        </div>

                        <!-- Divider line -->
                        <div class="qr-strip-divider" />

                        <!-- Strip Footer: Names & Date in Serif Typography -->
                        <div class="qr-strip-footer">
                            <h2 class="qr-strip-names notranslate" translate="no">
                                {{ displayName }}
                            </h2>
                            <p class="qr-strip-date">{{ formattedDate }}</p>
                            <p class="qr-strip-instruction">
                                Arahkan kamera HP ke QR Code untuk titip foto &
                                ucapan
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex w-full flex-col gap-2 sm:flex-row">
                    <Button type="button" variant="default" class="flex-1" :disabled="downloading || !qrDataUrl"
                        @click="downloadCard">
                        <Download class="mr-1.5 size-4" />
                        {{
                            downloading
                                ? 'Memproses Unduhan…'
                                : 'Unduh Gambar (PNG HD)'
                        }}
                    </Button>
                    <Button type="button" variant="outline" @click="copyUrl">
                        <LinkIcon class="mr-1.5 size-4" />
                        Salin Tautan
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.qr-strip-card {
    --bg: var(--qr-bg, #f8f6f2);
    --ink: var(--qr-ink, #1d1c29);
    --accent: var(--qr-accent, #ff5d6c);

    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 320px;
    border-radius: 12px;
    background: var(--bg);
    color: var(--ink);
    padding: 16px;
    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.08),
        0 12px 28px rgba(0, 0, 0, 0.06);
    box-sizing: border-box;
}

/* Inner fine border matching the photobooth print strip style */
.qr-strip-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    border: 1.5px solid var(--accent);
    opacity: 0.95;
    border-radius: 8px;
    padding: 20px 16px 24px;
    box-sizing: border-box;
}

.qr-strip-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 20px;
    padding: 0 4px;
}

.qr-strip-brand {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--ink);
    opacity: 0.85;
}

.qr-strip-subtitle {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: var(--ink);
    opacity: 0.55;
}

/* Center QR Code Slot */
.qr-slot-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.qr-slot-frame {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 220px;
    height: 220px;
    border: 1.5px solid var(--ink);
    border-color: color-mix(in srgb, var(--ink) 25%, transparent);
    background: color-mix(in srgb, var(--bg) 60%, #ffffff 40%);
    border-radius: 6px;
    padding: 14px;
    box-sizing: border-box;
}

.qr-slot-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.qr-slot-loading {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 12px;
    color: var(--ink);
    opacity: 0.5;
}

/* Thin accent divider line */
.qr-strip-divider {
    width: 48px;
    height: 1.5px;
    background: var(--accent);
    margin: 22px 0 16px;
    opacity: 0.75;
}

/* Footer: Serif Names & Date */
.qr-strip-footer {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    width: 100%;
}

.qr-strip-names {
    font-family: var(--font-display, 'Playfair Display', Georgia, serif);
    font-size: 23px;
    font-weight: 600;
    line-height: 1.25;
    letter-spacing: -0.01em;
    color: var(--ink);
    margin: 0;
}

.qr-strip-date {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.01em;
    color: var(--ink);
    opacity: 0.65;
    margin: 5px 0 0;
}

.qr-strip-instruction {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 10.5px;
    color: var(--ink);
    opacity: 0.5;
    margin-top: 10px;
    line-height: 1.35;
    max-width: 220px;
}
</style>
