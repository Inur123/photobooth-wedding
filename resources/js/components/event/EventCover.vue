<script setup lang="ts">
import { ArrowUpRight, BookOpen, MapPin } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import { eventDate } from '@/lib/eventTheme';
import type { GuestEvent } from '@/types/event';

const props = defineProps<{ event: GuestEvent }>();
const emit = defineEmits<{ open: [] }>();
const opening = ref(false);
const rotate = ref({ x: 0, y: 0 });
let timer: ReturnType<typeof setTimeout> | undefined;
const monogram = computed(() =>
    [props.event.party_one_name, props.event.party_two_name]
        .filter(Boolean)
        .map((name) => name![0])
        .join(''),
);
const occasion = computed(
    () =>
        ({
            wedding: 'Pernikahan',
            engagement: 'Pertunangan',
            birthday: 'Ulang tahun',
            other: 'Perayaan',
        })[props.event.category],
);

function tilt(pointer: PointerEvent) {
    if (
        pointer.pointerType !== 'mouse' ||
        opening.value ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    )
        return;
    const bounds = (
        pointer.currentTarget as HTMLElement
    ).getBoundingClientRect();
    rotate.value = {
        x: ((pointer.clientY - bounds.top) / bounds.height - 0.5) * -10,
        y: ((pointer.clientX - bounds.left) / bounds.width - 0.5) * 12,
    };
}

function open() {
    if (opening.value) return;
    opening.value = true;
    rotate.value = { x: 0, y: 0 };
    timer = setTimeout(
        () => emit('open'),
        window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 950,
    );
}

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <section
        class="event-cover"
        :class="{ 'is-opening': opening }"
        aria-label="Sampul album acara"
    >
        <header class="cover-masthead">
            <span class="event-wordmark">titip<span>.</span>moment</span>
            <span class="cover-edition">Album tamu · {{ occasion }}</span>
        </header>
        <div class="cover-layout">
            <div class="cover-copy">
                <p class="cover-prelude">Satu hari, begitu banyak cerita.</p>
                <h1 class="cover-names notranslate" translate="no">
                    <span>{{ event.party_one_name }}</span>
                    <template v-if="event.party_two_name">
                        <span class="cover-ampersand">&amp;</span>
                        <span>{{ event.party_two_name }}</span>
                    </template>
                </h1>
                <div class="cover-details">
                    <p>{{ eventDate(event.event_date) }}</p>
                    <p v-if="event.venue" class="cover-venue">
                        <MapPin aria-hidden="true" />{{ event.venue }}
                    </p>
                </div>
                <p
                    v-if="event.notes"
                    class="cover-invitation cover-invitation--custom"
                >
                    {{ event.notes }}
                </p>
                <p v-else class="cover-invitation">
                    <span class="cover-invitation-full">
                        Yang membuat hari ini berkesan, salah satunya kamu.
                        Titipkan foto, suara, dan doa untuk dikenang bersama.
                    </span>
                    <span class="cover-invitation-compact">
                        Titipkan momen dan ucapanmu untuk dikenang bersama.
                    </span>
                </p>
                <button
                    class="event-primary cover-open"
                    type="button"
                    :disabled="opening"
                    @click="open"
                >
                    <BookOpen aria-hidden="true" />{{
                        opening ? 'Membuka album…' : 'Buka album kenangan'
                    }}
                </button>
                <p class="cover-footnote">
                    Dari sudut pandangmu, untuk kenangan kita.
                </p>
            </div>
            <div
                class="cover-art"
                @pointermove="tilt"
                @pointerleave="rotate = { x: 0, y: 0 }"
            >
                <div
                    class="keepsake-scene"
                    :style="{
                        '--tilt-x': `${rotate.x}deg`,
                        '--tilt-y': `${rotate.y}deg`,
                    }"
                    aria-hidden="true"
                >
                    <div class="keepsake-shadow" />
                    <div class="keepsake-back-sheet">
                        <span>Setiap momen punya tempat.</span>
                    </div>
                    <div class="keepsake-letter">
                        <div class="letter-rule" />
                        <img
                            v-if="event.cover_url"
                            :src="event.cover_url"
                            alt=""
                            fetchpriority="high"
                            width="400"
                            height="300"
                            class="letter-photo"
                        />
                        <div
                            v-else
                            class="letter-monogram notranslate"
                            translate="no"
                        >
                            {{ monogram }}
                        </div>
                        <p class="letter-names notranslate" translate="no">
                            {{ event.party_one_name
                            }}<template v-if="event.party_two_name"
                                ><br /><i>&amp;</i>
                                {{ event.party_two_name }}</template
                            >
                        </p>
                        <span class="letter-date">{{
                            eventDate(event.event_date)
                        }}</span>
                        <div class="letter-rule" />
                    </div>
                    <div class="keepsake-envelope">
                        <div class="envelope-fold" />
                        <span class="envelope-label"
                            >Untuk kenangan yang tinggal.</span
                        >
                    </div>
                    <div class="envelope-flap" />
                    <div class="keepsake-seal">
                        <span>{{ monogram }}</span>
                    </div>
                </div>
                <span class="cover-art-caption"
                    >Sebuah album. Cerita kita semua.<ArrowUpRight
                        aria-hidden="true"
                /></span>
            </div>
        </div>
        <footer class="cover-footer">
            <span>Disimpan dengan hati.</span><span>titip.moment</span>
        </footer>
    </section>
</template>
