<script setup lang="ts">
import { AudioLines, Camera, Film, MessageSquare, Download } from '@lucide/vue';
import { computed, ref } from 'vue';
import { initials } from '@/lib/eventTheme';
import type { GuestContribution } from '@/types/event';

const props = defineProps<{
    contribution: GuestContribution;
    eventTitle: string;
}>();
const failed = ref(false);
const loaded = ref(false);
const author = computed(() => props.contribution.guest_name || 'Seorang tamu');
const timestamp = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(props.contribution.created_at)),
);
const typeLabel = computed(
    () =>
        ({
            photo: 'Foto',
            video: 'Reels',
            voice: 'Voice note',
            wish: 'Ucapan',
        })[props.contribution.type],
);
const typeIcon = computed(
    () =>
        ({
            photo: Camera,
            video: Film,
            voice: AudioLines,
            wish: MessageSquare,
        })[props.contribution.type],
);
const imageDimensions = computed(() => {
    const layout = props.contribution.layout;
    if (layout === 2) return { width: 900, height: 1800 };
    if (layout === 3) return { width: 900, height: 2400 };
    if (layout === 4) return { width: 1500, height: 1900 };
    return { width: 1080, height: 1440 };
});
</script>

<template>
    <article
        class="contribution-card"
        :class="`contribution-${contribution.type}`"
    >
        <header class="contribution-header">
            <span
                class="guest-avatar notranslate"
                translate="no"
                aria-hidden="true"
                >{{ initials(author) }}</span
            >
            <div>
                <h3 class="notranslate" translate="no">{{ author }}</h3>
                <time :datetime="contribution.created_at">{{ timestamp }}</time>
            </div>
            <component
                :is="typeIcon"
                class="contribution-type-icon"
                :aria-label="typeLabel"
            />
        </header>
        <div v-if="failed" class="media-error" role="status">
            <p>Media belum bisa dimuat di pemutar ini.</p>
            <div class="media-error-actions">
                <a
                    v-if="contribution.file_url"
                    :href="contribution.file_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="event-text-button"
                >
                    Buka media langsung
                </a>
                <button
                    class="event-text-button retry-button"
                    type="button"
                    @click="
                        failed = false;
                        loaded = false;
                    "
                >
                    Coba lagi
                </button>
            </div>
        </div>
        <div
            v-else-if="contribution.type === 'photo' && contribution.file_url"
            class="post-photo-wrap"
            :aria-busy="!loaded"
        >
            <span v-if="!loaded" class="media-loading">Memuat foto…</span>
            <img
                :src="contribution.file_url"
                :alt="`Foto dari ${author} untuk ${eventTitle}`"
                :width="imageDimensions.width"
                :height="imageDimensions.height"
                loading="lazy"
                class="post-photo"
                @load="loaded = true"
                @error="failed = true"
            />
        </div>
        <video
            v-else-if="contribution.type === 'video' && contribution.file_url"
            :src="contribution.file_url"
            :poster="contribution.thumbnail_url ?? undefined"
            :aria-label="`Video dari ${author}`"
            controls
            playsinline
            webkit-playsinline
            preload="metadata"
            class="post-reel"
            @error="failed = true"
        >
            <source
                :src="contribution.file_url"
                :type="contribution.mime_type ?? 'video/mp4'"
            />
        </video>
        <div
            v-else-if="contribution.type === 'voice' && contribution.file_url"
            class="voice-note"
        >
            <div class="voice-note-intro">
                <AudioLines aria-hidden="true" /><span
                    >Pesan yang bisa didengar lagi.</span
                >
            </div>
            <audio
                :src="contribution.file_url"
                :aria-label="`Voice note dari ${author}`"
                controls
                preload="metadata"
                @error="failed = true"
            >
                <source
                    :src="contribution.file_url"
                    :type="contribution.mime_type ?? 'audio/mp4'"
                />
            </audio>
            <span v-if="contribution.duration_seconds" class="voice-duration"
                >{{ contribution.duration_seconds }} detik untuk dikenang</span
            >
        </div>
        <blockquote
            v-else-if="contribution.type === 'wish'"
            class="written-wish"
        >
            <span aria-hidden="true" class="quote-mark">“</span>
            <p>{{ contribution.message }}</p>
        </blockquote>
        <footer class="contribution-footer">
            <p
                v-if="contribution.message && contribution.type !== 'wish'"
                class="post-caption"
            >
                {{ contribution.message }}
            </p>
            <div class="post-footer-meta">
                <span
                    >{{
                        contribution.visibility === 'private'
                            ? 'Privat · '
                            : ''
                    }}{{ typeLabel }} untuk {{ eventTitle }}</span
                ><a
                    v-if="contribution.file_url"
                    :href="`${contribution.file_url}?download=1`"
                    download
                    class="event-icon-button"
                    :aria-label="`Unduh ${typeLabel.toLowerCase()} dari ${author}`"
                    ><Download aria-hidden="true"
                /></a>
            </div>
        </footer>
    </article>
</template>
