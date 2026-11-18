import { computed, nextTick, onBeforeUnmount, ref, shallowRef } from 'vue';
import fixWebmDuration from 'fix-webm-duration';
import type { ContributionType, GuestEvent } from '@/types/event';
import { drawPhotobooth } from '@/lib/photobooth';

type FacingMode = 'user' | 'environment';

export function useGuestCapture(event: GuestEvent) {
    const mode = ref<ContributionType>('photo');
    const videoEl = ref<HTMLVideoElement | null>(null);
    const cameraReady = ref(false);
    const requesting = ref(false);
    const recording = ref(false);
    const capturing = ref(false);
    const switching = ref(false);
    const facingMode = ref<FacingMode>('user');
    const canSwitchCamera = ref(false);
    // Only the selfie camera is mirrored, so text and scenery from the rear
    // camera keep their real orientation.
    const mirrored = computed(() => facingMode.value === 'user');
    const elapsed = ref(0);
    const shotCount = ref(0);
    const layout = ref(event.capabilities.photo_layouts[0] ?? 1);
    const preview = ref<string | null>(null);
    const file = shallowRef<File | null>(null);
    const error = ref('');
    const notice = ref('');
    let stream: MediaStream | null = null;
    let recorder: MediaRecorder | null = null;
    let ticker: ReturnType<typeof setInterval> | null = null;
    let hardStopTimer: ReturnType<typeof setTimeout> | null = null;
    let generation = 0;
    let started = 0;
    let autoStopped = false;
    const shots = shallowRef<HTMLCanvasElement[]>([]);

    function clearRecordingTimers() {
        if (ticker) clearInterval(ticker);
        ticker = null;
        if (hardStopTimer) clearTimeout(hardStopTimer);
        hardStopTimer = null;
    }

    function stopStream() {
        stream?.getTracks().forEach((track) => track.stop());
        stream = null;
        cameraReady.value = false;
    }

    function clearPreview() {
        if (preview.value) URL.revokeObjectURL(preview.value);
        preview.value = null;
        file.value = null;
    }

    function reset() {
        generation++;
        clearRecordingTimers();
        autoStopped = false;
        if (recorder && recorder.state !== 'inactive') {
            recorder.onstop = null;
            recorder.stop();
        }
        recorder = null;
        stopStream();
        clearPreview();
        requesting.value = false;
        capturing.value = false;
        recording.value = false;
        switching.value = false;
        shots.value = [];
        elapsed.value = 0;
        shotCount.value = 0;
        error.value = '';
        notice.value = '';
    }

    function setMode(next: ContributionType) {
        reset();
        mode.value = next;
    }

    function setLayout(value: number) {
        reset();
        layout.value = value;
    }

    function requestStream(facing: FacingMode, exact = false) {
        return navigator.mediaDevices.getUserMedia({
            video:
                mode.value === 'voice'
                    ? false
                    : {
                          facingMode: exact ? { exact: facing } : facing,
                          width: { ideal: 1280 },
                          height: { ideal: 960 },
                      },
            audio: mode.value !== 'photo',
        });
    }

    async function attachStream(acquired: MediaStream) {
        stream = acquired;
        cameraReady.value = true;
        await nextTick();
        if (videoEl.value && mode.value !== 'voice') {
            videoEl.value.srcObject = stream;
            await videoEl.value.play();
        }
    }

    async function detectCameras() {
        if (mode.value === 'voice') {
            canSwitchCamera.value = false;
            return;
        }
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            canSwitchCamera.value =
                devices.filter((device) => device.kind === 'videoinput')
                    .length > 1;
        } catch {
            canSwitchCamera.value = false;
        }
    }

    async function startCamera() {
        if (requesting.value) return;
        error.value = '';
        if (!navigator.mediaDevices?.getUserMedia) {
            error.value =
                'Browser ini belum bisa membuka kamera. Gunakan HTTPS atau pilih file dari perangkat.';
            return;
        }
        const ticket = ++generation;
        stopStream();
        requesting.value = true;
        try {
            const acquired = await requestStream(facingMode.value);
            if (ticket !== generation) {
                acquired.getTracks().forEach((track) => track.stop());
                return;
            }
            await attachStream(acquired);
            // Device labels and the full camera list are only exposed after
            // permission is granted, so detect cameras after the first stream.
            await detectCameras();
        } catch {
            if (ticket !== generation) return;
            stopStream();
            error.value =
                'Kamera atau mikrofon belum bisa diakses. Periksa izin browser, lalu coba lagi atau unggah file.';
        } finally {
            if (ticket === generation) requesting.value = false;
        }
    }

    async function switchCamera() {
        if (
            mode.value === 'voice' ||
            !cameraReady.value ||
            recording.value ||
            capturing.value ||
            switching.value
        )
            return;
        const previous = facingMode.value;
        const next: FacingMode = previous === 'user' ? 'environment' : 'user';
        const ticket = ++generation;
        error.value = '';
        switching.value = true;
        // Phones cannot open two cameras at once, so release the current one
        // first. cameraReady stays true to keep the layout from jumping.
        stream?.getTracks().forEach((track) => track.stop());
        stream = null;
        try {
            let acquired: MediaStream;
            try {
                acquired = await requestStream(next, true);
            } catch {
                acquired = await requestStream(next);
            }
            if (ticket !== generation) {
                acquired.getTracks().forEach((track) => track.stop());
                return;
            }
            const reported = acquired.getVideoTracks()[0]?.getSettings()
                .facingMode as string | undefined;
            facingMode.value =
                reported === 'user' || reported === 'environment'
                    ? reported
                    : next;
            await attachStream(acquired);
        } catch {
            if (ticket !== generation) return;
            try {
                const fallback = await requestStream(previous);
                if (ticket !== generation) {
                    fallback.getTracks().forEach((track) => track.stop());
                    return;
                }
                await attachStream(fallback);
                error.value =
                    'Kamera lain tidak dapat dibuka di perangkat ini.';
            } catch {
                stopStream();
                error.value =
                    'Kamera tidak dapat dibuka. Aktifkan kembali kamera atau unggah file.';
            }
        } finally {
            if (ticket === generation) switching.value = false;
        }
    }

    function acceptFile(next: File) {
        clearPreview();
        file.value = next;
        preview.value = URL.createObjectURL(next);
    }

    async function compose(
        shots: (HTMLCanvasElement | ImageBitmap)[],
        ticket: number,
    ) {
        const canvas = document.createElement('canvas');
        const count = layout.value;
        await document.fonts.ready;
        if (ticket !== generation) return;
        drawPhotobooth(canvas, event, count, shots);
        // Export sebagai WebP untuk ukuran lebih kecil, fallback ke JPEG jika tidak didukung
        const supportsWebP = canvas
            .toDataURL('image/webp')
            .startsWith('data:image/webp');
        const mimeOut = supportsWebP ? 'image/webp' : 'image/jpeg';
        const extOut = supportsWebP ? 'webp' : 'jpg';
        const qualityOut = supportsWebP ? 0.85 : 0.9;
        const blob = await new Promise<Blob | null>((resolve) =>
            canvas.toBlob(resolve, mimeOut, qualityOut),
        );
        if (ticket !== generation) return;
        if (!blob) throw new Error('Foto gagal diproses. Silakan coba lagi.');
        acceptFile(
            new File([blob], `titip-moment.${extOut}`, { type: mimeOut }),
        );
        stopStream();
    }

    async function capture() {
        if (!cameraReady.value || capturing.value || switching.value) return;
        const ticket = generation;
        capturing.value = true;
        error.value = '';
        try {
            if (shots.value.length < layout.value) {
                const video = videoEl.value;
                if (!video?.videoWidth)
                    throw new Error(
                        'Kamera belum siap. Aktifkan kembali kamera.',
                    );
                const shot = document.createElement('canvas');
                shot.width = video.videoWidth;
                shot.height = video.videoHeight;
                const context = shot.getContext('2d');
                if (!context)
                    throw new Error('Kamera tidak dapat mengambil foto.');
                if (mirrored.value) {
                    context.translate(shot.width, 0);
                    context.scale(-1, 1);
                }
                context.drawImage(video, 0, 0);
                shots.value = [...shots.value, shot];
                shotCount.value = shots.value.length;
            }
            if (shots.value.length === layout.value) {
                await compose(shots.value, ticket);
                if (ticket === generation) shots.value = [];
            }
        } catch (cause) {
            if (ticket === generation)
                error.value =
                    cause instanceof Error
                        ? cause.message
                        : 'Foto gagal diambil.';
        } finally {
            if (ticket === generation) capturing.value = false;
        }
    }

    function stopRecording() {
        clearRecordingTimers();
        if (recorder?.state === 'recording') recorder.stop();
    }

    function startRecording() {
        if (!stream || recording.value || switching.value) return;
        const isVideo = mode.value === 'video';
        const maxSeconds = isVideo
            ? event.capabilities.max_video_seconds
            : event.capabilities.max_voice_seconds;
        if (!event.capabilities.types.includes(mode.value) || maxSeconds <= 0) {
            error.value = 'Jenis kiriman ini tidak tersedia pada paket acara.';
            stopStream();
            return;
        }
        if (typeof MediaRecorder === 'undefined') {
            error.value =
                'Browser ini belum mendukung perekaman. Pilih file dari perangkat.';
            return;
        }
        const ticket = generation;
        // Prioritaskan format MP4 (H.264/AAC) agar video & suara dapat diputar di semua perangkat
        // termasuk iPhone/iPad (Safari iOS 15+) serta Android & desktop. WebM dipakai sebagai fallback.
        const candidates = isVideo
            ? [
                  'video/mp4;codecs=avc1.42E01E,mp4a.40.2',
                  'video/mp4;codecs=avc1',
                  'video/mp4;codecs=h264',
                  'video/mp4',
                  'video/webm;codecs=vp9,opus',
                  'video/webm;codecs=vp8,opus',
                  'video/webm',
              ]
            : [
                  'audio/mp4;codecs=mp4a.40.2',
                  'audio/mp4',
                  'audio/aac',
                  'audio/m4a',
                  'audio/webm;codecs=opus',
                  'audio/webm',
                  'audio/ogg;codecs=opus',
              ];
        const mimeType = candidates.find((mime) =>
            MediaRecorder.isTypeSupported(mime),
        );
        const chunks: Blob[] = [];
        try {
            const recorderOptions: MediaRecorderOptions = mimeType
                ? { mimeType }
                : {};
            if (isVideo) {
                recorderOptions.videoBitsPerSecond = 1_800_000; // 1.8 Mbps (kualitas tajam, ukuran optimal untuk mobile)
                recorderOptions.audioBitsPerSecond = 96_000; // 96 kbps audio jernih
            } else {
                recorderOptions.audioBitsPerSecond = 64_000; // 64 kbps cukup untuk suara
            }
            const activeRecorder = new MediaRecorder(stream, recorderOptions);
            recorder = activeRecorder;
            activeRecorder.ondataavailable = (chunk) => {
                if (chunk.data.size) chunks.push(chunk.data);
            };
            activeRecorder.onerror = () => {
                reset();
                error.value =
                    'Perekaman terhenti. Silakan rekam ulang atau unggah file.';
            };
            activeRecorder.onstop = async () => {
                clearRecordingTimers();
                if (ticket !== generation) return;
                recording.value = false;
                const durationMs = Math.max(1000, Date.now() - started);
                elapsed.value = Math.min(
                    maxSeconds,
                    Math.max(1, Math.round(durationMs / 1000)),
                );
                const defaultMime = isVideo ? 'video/mp4' : 'audio/mp4';
                const mime = activeRecorder.mimeType || mimeType || defaultMime;
                const extension = mime.includes('mp4')
                    ? 'mp4'
                    : mime.includes('m4a')
                      ? 'm4a'
                      : mime.includes('aac')
                        ? 'aac'
                        : mime.includes('ogg')
                          ? 'ogg'
                          : 'webm';
                let blob: Blob = new Blob(chunks, { type: mime });
                if (mime.includes('webm')) {
                    try {
                        blob = await fixWebmDuration(blob, durationMs, {
                            logger: false,
                        });
                    } catch {
                        /* Fallback ke blob asli jika patch metadata gagal */
                    }
                }
                const result = new File([blob], `titip-moment.${extension}`, {
                    type: mime,
                });
                const maxMb = Math.round(
                    (isVideo
                        ? event.capabilities.max_video_kb
                        : event.capabilities.max_voice_kb) / 1024,
                );
                if (
                    result.size &&
                    result.size <=
                        (isVideo
                            ? event.capabilities.max_video_kb
                            : event.capabilities.max_voice_kb) *
                            1024
                ) {
                    acceptFile(result);
                    if (autoStopped) {
                        notice.value = `Perekaman berhenti otomatis karena mencapai batas ${maxSeconds} detik.`;
                    }
                } else {
                    error.value = `Rekaman kosong atau terlalu besar (maksimal ${maxMb} MB). Coba rekam lebih singkat.`;
                }
                stopStream();
            };
            autoStopped = false;
            notice.value = '';
            activeRecorder.start(250);
            started = Date.now();
            elapsed.value = 0;
            recording.value = true;
            ticker = setInterval(() => {
                const elapsedMs = Date.now() - started;
                elapsed.value = Math.min(
                    maxSeconds,
                    Math.floor(elapsedMs / 1000),
                );
                if (elapsedMs >= maxSeconds * 1000) {
                    elapsed.value = maxSeconds;
                    autoStopped = true;
                    stopRecording();
                }
            }, 100);
            hardStopTimer = setTimeout(() => {
                elapsed.value = maxSeconds;
                autoStopped = true;
                stopRecording();
            }, maxSeconds * 1000);
        } catch {
            error.value =
                'Perekaman tidak didukung perangkat ini. Gunakan unggah file.';
        }
    }

    async function upload(files: FileList | null) {
        if (!files?.length) return;
        reset();
        if (!event.capabilities.types.includes(mode.value)) {
            error.value = 'Jenis kiriman ini tidak tersedia pada paket acara.';
            return;
        }
        const ticket = generation;
        const selected = Array.from(files);
        if (mode.value === 'photo') {
            if (selected.length !== layout.value) {
                error.value = `Pilih ${layout.value} foto sesuai layout yang dipilih.`;
                return;
            }
            const maxPhotoBytes = event.capabilities.max_photo_kb * 1024;
            const maxPhotoMb = Math.round(
                event.capabilities.max_photo_kb / 1024,
            );
            // Terima HEIC/HEIF (iPhone) — browser/canvas handle konversi otomatis
            const allowedPhotoTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/heic',
                'image/heif',
            ];
            if (
                selected.some(
                    (image) =>
                        !allowedPhotoTypes.includes(image.type) ||
                        image.size > maxPhotoBytes,
                )
            ) {
                error.value = `Gunakan foto JPG, PNG, WebP, atau HEIC, maksimal ${maxPhotoMb} MB per foto.`;
                return;
            }
            capturing.value = true;
            const images: ImageBitmap[] = [];
            try {
                for (const image of selected)
                    images.push(await createImageBitmap(image));
                await compose(images, ticket);
            } catch (cause) {
                if (ticket === generation)
                    error.value =
                        cause instanceof Error
                            ? cause.message
                            : 'Foto tidak dapat dibaca.';
            } finally {
                images.forEach((image) => image.close());
                if (ticket === generation) capturing.value = false;
            }
            return;
        }
        const selectedFile = selected[0];
        const isVideo = mode.value === 'video';
        const allowed = isVideo
            ? ['video/mp4', 'video/webm', 'video/quicktime']
            : [
                  'audio/webm',
                  'audio/mpeg',
                  'audio/mp4',
                  'audio/aac',
                  'audio/m4a',
                  'audio/x-m4a',
                  'audio/ogg',
                  'audio/wav',
                  'audio/x-wav',
                  'video/webm',
              ];
        const maxMediaKb = isVideo
            ? event.capabilities.max_video_kb
            : event.capabilities.max_voice_kb;
        const maxMediaMb = Math.round(maxMediaKb / 1024);
        if (
            !allowed.includes(selectedFile.type) ||
            selectedFile.size > maxMediaKb * 1024
        ) {
            error.value = isVideo
                ? `Gunakan video MP4, WebM, atau MOV, maksimal ${maxMediaMb} MB.`
                : `Gunakan audio MP3, MP4, WebM, OGG, atau WAV, maksimal ${maxMediaMb} MB.`;
            return;
        }
        const objectUrl = URL.createObjectURL(selectedFile);
        const media = document.createElement(isVideo ? 'video' : 'audio');
        requesting.value = true;
        try {
            const duration = await new Promise<number>((resolve, reject) => {
                const timeout = setTimeout(
                    () =>
                        reject(
                            new Error(
                                'File tidak dapat dibaca. Coba file lain.',
                            ),
                        ),
                    10000,
                );
                media.onloadedmetadata = () => {
                    clearTimeout(timeout);
                    if (Number.isFinite(media.duration))
                        resolve(media.duration);
                    else
                        reject(
                            new Error(
                                'Durasi file tidak terbaca. Rekam langsung atau gunakan file lain.',
                            ),
                        );
                };
                media.onerror = () => {
                    clearTimeout(timeout);
                    reject(
                        new Error('File tidak dapat diputar oleh browser ini.'),
                    );
                };
                media.src = objectUrl;
            });
            if (ticket !== generation) return;
            const maxSeconds = isVideo
                ? event.capabilities.max_video_seconds
                : event.capabilities.max_voice_seconds;
            if (duration > maxSeconds)
                throw new Error(`Durasi maksimal ${maxSeconds} detik.`);
            elapsed.value = Math.max(1, Math.ceil(duration));
            acceptFile(selectedFile);
        } catch (cause) {
            if (ticket === generation)
                error.value =
                    cause instanceof Error
                        ? cause.message
                        : 'File tidak dapat dibaca.';
        } finally {
            URL.revokeObjectURL(objectUrl);
            media.removeAttribute('src');
            media.load();
            if (ticket === generation) requesting.value = false;
        }
    }

    onBeforeUnmount(reset);
    return {
        mode,
        videoEl,
        cameraReady,
        requesting,
        recording,
        capturing,
        switching,
        facingMode,
        canSwitchCamera,
        mirrored,
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
        switchCamera,
        capture,
        startRecording,
        stopRecording,
        upload,
    };
}
