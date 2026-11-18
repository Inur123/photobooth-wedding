<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { drawPhotobooth, photoboothLayout } from '@/lib/photobooth';
import type { GuestEvent } from '@/types/event';

const props = defineProps<{
    event: GuestEvent;
    layout: number;
    shots: HTMLCanvasElement[];
    video: HTMLVideoElement | null;
    live: boolean;
    mirror?: boolean;
}>();
const canvas = ref<HTMLCanvasElement | null>(null);
const geometry = computed(() => photoboothLayout(props.layout));
let animation = 0;
let lastDraw = 0;
let mounted = false;

function draw() {
    if (!canvas.value) return;
    drawPhotobooth(canvas.value, props.event, props.layout, props.shots, {
        live: props.live ? props.video : null,
        mirrorLive: props.mirror ?? true,
        scale: 0.3,
    });
}
function tick(time: number) {
    if (time - lastDraw >= 100) {
        draw();
        lastDraw = time;
    }
    if (props.live) animation = requestAnimationFrame(tick);
}
function update() {
    if (!mounted) return;
    cancelAnimationFrame(animation);
    draw();
    if (props.live) animation = requestAnimationFrame(tick);
}
watch(
    [
        () => props.layout,
        () => props.shots,
        () => props.video,
        () => props.live,
    ],
    update,
    { flush: 'post' },
);
onMounted(() => {
    mounted = true;
    update();
    void document.fonts.ready.then(() => {
        if (mounted) update();
    });
});
onBeforeUnmount(() => {
    mounted = false;
    cancelAnimationFrame(animation);
});
</script>

<template>
    <aside class="photobooth-preview" aria-label="Pratinjau photobox">
        <div class="photobooth-preview-heading">
            <strong>Hasil photobox</strong
            ><span>{{ shots.length }} / {{ layout }} pose</span>
        </div>
        <canvas
            ref="canvas"
            :width="geometry.width * 0.3"
            :height="geometry.height * 0.3"
            :style="{ aspectRatio: `${geometry.width} / ${geometry.height}` }"
            role="img"
            :aria-label="`Layout photobox ${layout} pose, ${shots.length} sudah diambil`"
        />
        <p>Pose yang diambil langsung mengisi bingkai ini.</p>
    </aside>
</template>
