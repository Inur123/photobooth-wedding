<script setup lang="ts">
import { Head, Link, router, usePoll, InfiniteScroll } from '@inertiajs/vue3';
import {
    AudioLines,
    BookOpen,
    CalendarDays,
    Camera,
    Film,
    Grid3X3,
    Heart,
    Info,
    LayoutList,
    LockKeyhole,
    MapPin,
    MessageSquare,
    Share2,
    X,
} from '@lucide/vue';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import ContributionCard from '@/components/event/ContributionCard.vue';
import ContributionSkeleton from '@/components/event/ContributionSkeleton.vue';
import ContributionComposer from '@/components/event/ContributionComposer.vue';
import EventCover from '@/components/event/EventCover.vue';
import { getEcho } from '@/lib/echo';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { Toaster } from '@/components/ui/sonner';
import { eventDate, eventName, eventTheme, initials } from '@/lib/eventTheme';
import type {
    ContributionType,
    GuestAlbumTab,
    GuestContribution,
    GuestExperienceProps,
    GuestSection,
    PaginatedContributions,
} from '@/types/event';
import '../../../css/event-experience.css';

const props = defineProps<GuestExperienceProps>();
const hasVideo = computed(() =>
    props.event.capabilities.types.includes('video'),
);
const entered = computed(() => props.navigation.section !== 'cover');
const activeTab = computed(() =>
    props.navigation.tab === 'reels' && !hasVideo.value
        ? 'album'
        : props.navigation.tab,
);
const albumView = computed(() => props.navigation.view);
const noteFilter = computed(() => props.navigation.filter);
const composing = computed(() => props.navigation.section === 'titip');
const isOpen = ref(props.isOpen);
watch(
    () => props.isOpen,
    (val) => {
        isOpen.value = val;
    },
);
const selectedPhoto = ref<GuestContribution | null>(null);
const refreshing = ref(false);
const refreshError = ref(false);
const titleRef = ref<HTMLElement | null>(null);
const styles = computed(() => eventTheme(props.event));
const title = computed(() => eventName(props.event));

const realtimeItems = ref<GuestContribution[]>([]);
const deletedIds = ref<Set<string>>(new Set());

// Normalisasi contributions — gabungkan data dari props dan kiriman real-time via Reverb WebSocket,
// sekaligus deduplikasi berdasarkan id agar foto/video/catatan baru muncul instan tanpa jeda.
const contributionsList = computed<GuestContribution[]>(() => {
    const c = props.contributions;
    const raw = Array.isArray(c)
        ? [...c]
        : [...((c as PaginatedContributions | null)?.data ?? [])];

    const combined = [...realtimeItems.value, ...raw];
    const seen = new Set<string>();
    const list: GuestContribution[] = [];

    for (const item of combined) {
        if (deletedIds.value.has(item.id)) continue;
        if (seen.has(item.id)) continue;
        if (item.visibility === 'private' && !props.canViewPrivate) continue;
        seen.add(item.id);
        list.push(item);
    }

    // Urutkan kiriman terbaru selalu di paling atas (LIFO / latest created_at first)
    list.sort((a, b) => {
        const timeA = a.created_at ? new Date(a.created_at).getTime() : 0;
        const timeB = b.created_at ? new Date(b.created_at).getTime() : 0;
        if (timeA !== timeB) {
            return timeB - timeA;
        }
        return (b.id || '').localeCompare(a.id || '');
    });

    return list;
});

// Bersihkan item realtime yang sudah disinkronkan ke props dari server
watch(
    () => props.contributions,
    (newVal) => {
        if (!newVal) return;
        const incoming = Array.isArray(newVal)
            ? newVal
            : ((newVal as PaginatedContributions).data ?? []);
        const incomingIds = new Set(incoming.map((i) => i.id));
        realtimeItems.value = realtimeItems.value.filter(
            (item) => !incomingIds.has(item.id),
        );
    },
    { deep: true },
);

onMounted(() => {
    const echo = getEcho();
    if (!echo) return;

    const channelName = `events.${props.event.slug}`;
    const channel = echo.channel(channelName);

    channel.listen(
        '.ContributionCreated',
        (e: { contribution: GuestContribution }) => {
            if (!e?.contribution) return;
            if (
                e.contribution.visibility === 'private' &&
                !props.canViewPrivate
            ) {
                return;
            }

            deletedIds.value.delete(e.contribution.id);
            const existingIdx = realtimeItems.value.findIndex(
                (item) => item.id === e.contribution.id,
            );
            if (existingIdx !== -1) {
                realtimeItems.value[existingIdx] = e.contribution;
            } else {
                realtimeItems.value.unshift(e.contribution);
            }
        },
    );

    channel.listen(
        '.ContributionUpdated',
        (e: { contribution: GuestContribution }) => {
            if (!e?.contribution) return;

            if (e.contribution.is_approved === false && !props.canViewPrivate) {
                deletedIds.value.add(e.contribution.id);
                realtimeItems.value = realtimeItems.value.filter(
                    (item) => item.id !== e.contribution.id,
                );
                return;
            }

            deletedIds.value.delete(e.contribution.id);
            const existingIdx = realtimeItems.value.findIndex(
                (item) => item.id === e.contribution.id,
            );
            if (existingIdx !== -1) {
                realtimeItems.value[existingIdx] = e.contribution;
            } else {
                realtimeItems.value.unshift(e.contribution);
            }
        },
    );

    channel.listen('.ContributionDeleted', (e: { id: string }) => {
        if (!e?.id) return;
        deletedIds.value.add(e.id);
        realtimeItems.value = realtimeItems.value.filter(
            (item) => item.id !== e.id,
        );
    });

    channel.listen(
        '.EventStatusUpdated',
        (e: {
            event_id: string;
            slug: string;
            status: string;
            is_open: boolean;
            is_manual_status: boolean;
        }) => {
            if (e.slug === props.event.slug) {
                if (!e.is_open && isOpen.value) {
                    toast.info(
                        'Waktu pengiriman momen untuk acara ini telah berakhir.',
                    );
                } else if (e.is_open && !isOpen.value) {
                    toast.success('Pengiriman momen telah dibuka!');
                }
                isOpen.value = e.is_open;
            }
        },
    );
});

onUnmounted(() => {
    const echo = getEcho();
    if (echo) {
        echo.leaveChannel(`events.${props.event.slug}`);
    }
});
const contributionsMeta = computed(() => {
    const c = props.contributions;
    if (!c || Array.isArray(c)) return null;
    return (c as PaginatedContributions).meta ?? null;
});

const photos = computed(() =>
    contributionsList.value.filter((item) => item.type === 'photo'),
);
const reels = computed(() =>
    contributionsList.value.filter((item) => item.type === 'video'),
);
const notes = computed(() =>
    contributionsList.value.filter((item) =>
        ['wish', 'voice'].includes(item.type),
    ),
);
const filtered = computed(() =>
    activeTab.value === 'album'
        ? photos.value
        : activeTab.value === 'reels'
          ? reels.value
          : notes.value.filter(
                (item) =>
                    noteFilter.value === 'all' ||
                    item.type === noteFilter.value,
            ),
);
const sectionTitle = computed(
    () =>
        ({
            album: 'Momen bersama.',
            reels: 'Cerita yang bergerak.',
            notes: 'Dari hati, untuk nanti.',
            info: 'Tentang hari ini.',
        })[activeTab.value],
);
const emptyText = computed(() =>
    !props.isOpen
        ? 'Belum ada kiriman yang ditampilkan untuk acara ini.'
        : {
              album: 'Album ini menunggu sudut pandangmu.',
              reels: props.event.capabilities.types.includes('video')
                  ? 'Belum ada video di sini. Titipkan cerita pertamamu.'
                  : 'Paket acara ini difokuskan pada album foto, voice note, dan ucapan.',
              notes: 'Ruang untuk doa dan suara orang-orang tersayang.',
              info: '',
          }[activeTab.value],
);
const nav = computed(() => [
    { id: 'album' as const, label: 'Album', icon: BookOpen },
    ...(hasVideo.value
        ? [{ id: 'reels' as const, label: 'Reels', icon: Film }]
        : []),
    { id: 'notes' as const, label: 'Catatan', icon: MessageSquare },
    { id: 'info' as const, label: 'Acara', icon: Info },
]);
const polling = usePoll(
    15000,
    {
        only: ['contributions', 'isOpen', 'canViewGallery', 'canViewPrivate'],
        onError: () => {
            refreshError.value = true;
        },
        onSuccess: () => {
            refreshError.value = false;
        },
    },
    { autoStart: false },
);

watch(
    [entered, composing, () => props.canViewGallery],
    ([opened, writing, allowed]) => {
        if (opened && !writing && allowed) polling.start();
        else polling.stop();
    },
    { immediate: true },
);

const tabPaths: Record<GuestAlbumTab, GuestSection> = {
    album: 'album',
    reels: 'reels',
    notes: 'catatan',
    info: 'acara',
};

function sectionUrl(
    section: GuestSection,
    view = albumView.value,
    filter = noteFilter.value,
) {
    const base = props.ownerToken
        ? `/e/${props.event.slug}/owner/${props.ownerToken}`
        : `/e/${props.event.slug}`;
    if (section === 'cover') return base;
    const query = new URLSearchParams();
    const destination =
        section === 'titip' ? tabPaths[activeTab.value] : section;
    if (section === 'titip') query.set('from', destination);
    if (destination === 'album' && view === 'grid') query.set('view', view);
    if (destination === 'catatan' && filter !== 'all')
        query.set('filter', filter);
    const queryString = query.toString();
    return `${base}/${section}${queryString ? `?${queryString}` : ''}`;
}

function navigate(url: string, replace = false, preserveScroll = false) {
    router.get(url, {}, { preserveState: true, preserveScroll, replace });
}

function openAlbum() {
    router.get(
        sectionUrl('album'),
        {},
        {
            preserveState: true,
            onSuccess: async () => {
                await nextTick();
                titleRef.value?.focus({ preventScroll: true });
            },
        },
    );
}

function openComposer() {
    navigate(sectionUrl('titip'), false, true);
}

function closeComposer() {
    navigate(sectionUrl(tabPaths[activeTab.value]), true, true);
}

function refresh() {
    refreshing.value = true;
    router.reload({
        only: ['contributions', 'isOpen', 'canViewGallery', 'canViewPrivate'],
        onSuccess: () => {
            refreshError.value = false;
        },
        onError: () => {
            refreshError.value = true;
        },
        onFinish: () => {
            refreshing.value = false;
        },
    });
}

function sent(type: ContributionType) {
    const section =
        type === 'photo' ? 'album' : type === 'video' ? 'reels' : 'catatan';
    navigate(sectionUrl(section, albumView.value, 'all'), true);
}

async function share() {
    const url = `${window.location.origin}/e/${props.event.slug}`;
    try {
        if (navigator.share) {
            await navigator.share({
                title: title.value,
                url,
            });
        } else {
            await navigator.clipboard.writeText(url);
            toast.success('Tautan acara berhasil disalin.');
        }
    } catch (cause) {
        if (!(cause instanceof Error && cause.name === 'AbortError')) {
            toast.error(
                'Tautan belum bisa disalin. Salin alamat halaman dari browser.',
            );
        }
    }
}
</script>

<template>
    <Head :title="title" />
    <Toaster position="top-center" close-button />
    <main
        class="guest-experience"
        :style="styles"
        :data-event-theme="event.theme"
    >
        <EventCover v-if="!entered" :event="event" @open="openAlbum" />
        <div v-else class="guest-album">
            <header class="album-masthead">
                <Link
                    :href="sectionUrl('cover')"
                    preserve-state
                    class="event-wordmark"
                    aria-label="Buka kembali sampul acara"
                >
                    titip<span>.</span>moment</Link
                ><span class="album-masthead-label">{{
                    canViewPrivate ? 'Album pemilik acara' : 'Album kenangan'
                }}</span
                ><button
                    type="button"
                    class="event-icon-button"
                    aria-label="Bagikan tautan acara"
                    @click="share"
                >
                    <Share2 aria-hidden="true" />
                </button>
            </header>
            <div class="album-layout">
                <aside class="album-sidebar">
                    <div class="album-emblem" aria-hidden="true">
                        <img
                            v-if="event.cover_url"
                            :src="event.cover_url"
                            alt=""
                        />
                        <template v-else
                            ><span class="notranslate" translate="no">{{
                                [event.party_one_name, event.party_two_name]
                                    .filter(Boolean)
                                    .map((name) => name![0])
                                    .join('')
                            }}</span></template
                        >
                    </div>
                    <h2 class="notranslate" translate="no">
                        {{ event.party_one_name
                        }}<template v-if="event.party_two_name"
                            ><span>&amp;</span
                            >{{ event.party_two_name }}</template
                        >
                    </h2>
                    <p class="album-date">{{ eventDate(event.event_date) }}</p>
                    <p v-if="event.venue" class="album-location">
                        <MapPin aria-hidden="true" />{{ event.venue }}
                    </p>
                    <p class="album-sidebar-note">
                        <template v-if="event.notes">{{
                            event.notes
                        }}</template>
                        <template v-else
                            >Hari ini lewat sekali.<br />Kenangannya tinggal di
                            sini.</template
                        >
                    </p>
                    <Link
                        :href="sectionUrl('cover')"
                        preserve-state
                        class="event-secondary"
                    >
                        <BookOpen aria-hidden="true" />Lihat sampul
                    </Link>
                </aside>
                <section class="album-content" aria-label="Galeri acara">
                    <div class="album-mobile-context">
                        <span class="guest-avatar" aria-hidden="true"
                            ><img
                                v-if="event.cover_url"
                                :src="event.cover_url"
                                alt=""
                            /><template v-else
                                ><span class="notranslate" translate="no">{{
                                    initials(event.party_one_name)
                                }}</span></template
                            ></span
                        >
                        <div>
                            <strong class="notranslate" translate="no">{{
                                title
                            }}</strong
                            ><span>{{ eventDate(event.event_date) }}</span>
                        </div>
                    </div>
                    <header class="album-section-heading">
                        <div>
                            <p class="album-section-label">
                                {{
                                    activeTab === 'album'
                                        ? 'Album foto'
                                        : activeTab === 'reels'
                                          ? 'Reels'
                                          : activeTab === 'notes'
                                            ? 'Catatan & voice note'
                                            : 'Acara kita'
                                }}
                            </p>
                            <h1 ref="titleRef" tabindex="-1">
                                {{ sectionTitle }}
                            </h1>
                        </div>
                    </header>
                    <p
                        v-if="!isOpen"
                        class="event-status-message"
                        role="status"
                    >
                        Pengiriman momen belum dibuka atau sudah berakhir.
                        Kenangan yang tersedia tetap bisa dilihat.
                    </p>
                    <template v-if="activeTab === 'info'">
                        <article class="event-info-panel">
                            <span class="info-monogram" aria-hidden="true"
                                ><img
                                    v-if="event.cover_url"
                                    :src="event.cover_url"
                                    alt=""
                                /><template v-else
                                    ><span class="notranslate" translate="no">{{
                                        initials(event.party_one_name)
                                    }}</span></template
                                ></span
                            >
                            <h2 class="notranslate" translate="no">
                                {{ title }}
                            </h2>
                            <dl>
                                <div>
                                    <dt>
                                        <CalendarDays
                                            aria-hidden="true"
                                        />Tanggal acara
                                    </dt>
                                    <dd>{{ eventDate(event.event_date) }}</dd>
                                </div>
                                <div v-if="event.venue">
                                    <dt><MapPin aria-hidden="true" />Tempat</dt>
                                    <dd>{{ event.venue }}</dd>
                                </div>
                                <div v-if="event.notes">
                                    <dt>
                                        <Heart aria-hidden="true" />Pesan
                                        Sambutan
                                    </dt>
                                    <dd class="info-notes-text">
                                        {{ event.notes }}
                                    </dd>
                                </div>
                                <div>
                                    <dt>
                                        <BookOpen aria-hidden="true" />Album
                                        tamu
                                    </dt>
                                    <dd>
                                        {{
                                            canViewGallery
                                                ? 'Momen bersama, dari semua tamu.'
                                                : 'Kiriman disimpan untuk pemilik acara.'
                                        }}
                                    </dd>
                                </div>
                            </dl>
                            <button
                                type="button"
                                class="event-secondary"
                                @click="share"
                            >
                                <Share2 aria-hidden="true" />Bagikan acara
                            </button>
                        </article>
                    </template>
                    <div v-else-if="!canViewGallery" class="album-empty">
                        <LockKeyhole class="empty-icon" aria-hidden="true" />
                        <h2>Kenangan untuk pemilik acara.</h2>
                        <p>
                            Galeri ini bersifat privat. Kamu tetap bisa
                            menitipkan momen dan ucapanmu.
                        </p>
                        <button
                            v-if="isOpen"
                            type="button"
                            class="event-primary"
                            @click="openComposer"
                        >
                            <Camera aria-hidden="true" />Titipkan momen
                        </button>
                    </div>
                    <template v-else>
                        <div class="album-toolbar">
                            <p aria-live="polite">
                                {{
                                    activeTab === 'album'
                                        ? `${photos.length} foto`
                                        : activeTab === 'reels'
                                          ? `${reels.length} video`
                                          : `${filtered.length} catatan`
                                }}
                            </p>
                            <div
                                v-if="activeTab === 'album'"
                                class="view-toggle"
                                aria-label="Tampilan album"
                            >
                                <button
                                    type="button"
                                    class="event-icon-button"
                                    aria-label="Tampilan postingan"
                                    :aria-pressed="albumView === 'feed'"
                                    @click="
                                        navigate(
                                            sectionUrl('album', 'feed'),
                                            true,
                                            true,
                                        )
                                    "
                                >
                                    <LayoutList aria-hidden="true" /></button
                                ><button
                                    type="button"
                                    class="event-icon-button"
                                    aria-label="Tampilan grid foto"
                                    :aria-pressed="albumView === 'grid'"
                                    @click="
                                        navigate(
                                            sectionUrl('album', 'grid'),
                                            true,
                                            true,
                                        )
                                    "
                                >
                                    <Grid3X3 aria-hidden="true" />
                                </button>
                            </div>
                            <div
                                v-if="activeTab === 'notes'"
                                class="note-filters"
                                aria-label="Filter catatan"
                            >
                                <button
                                    v-for="filter in [
                                        'all',
                                        'wish',
                                        'voice',
                                    ] as const"
                                    :key="filter"
                                    type="button"
                                    :aria-pressed="noteFilter === filter"
                                    @click="
                                        navigate(
                                            sectionUrl(
                                                'catatan',
                                                albumView,
                                                filter,
                                            ),
                                            true,
                                            true,
                                        )
                                    "
                                >
                                    {{
                                        {
                                            all: 'Semua',
                                            wish: 'Ucapan',
                                            voice: 'Suara',
                                        }[filter]
                                    }}
                                </button>
                            </div>
                        </div>
                        <div
                            v-if="refreshError"
                            class="guest-error"
                            role="status"
                        >
                            Kiriman belum berhasil diperbarui.
                            <button
                                type="button"
                                class="event-text-button"
                                @click="refresh"
                            >
                                Coba lagi
                            </button>
                        </div>
                        <p
                            v-if="refreshing"
                            class="refresh-status"
                            role="status"
                        >
                            Memperbarui kiriman…
                        </p>
                        <div v-if="!filtered.length" class="album-empty">
                            <div class="empty-keepsake" aria-hidden="true">
                                <component
                                    :is="
                                        activeTab === 'album'
                                            ? Camera
                                            : activeTab === 'reels'
                                              ? Film
                                              : noteFilter === 'voice'
                                                ? AudioLines
                                                : MessageSquare
                                    "
                                />
                            </div>
                            <h2>
                                {{
                                    !isOpen
                                        ? 'Belum ada kenangan tersimpan.'
                                        : activeTab === 'album'
                                          ? 'Cerita pertama, darimu?'
                                          : activeTab === 'reels'
                                            ? 'Putar lagi momen indahnya.'
                                            : 'Sedikit kata, banyak makna.'
                                }}
                            </h2>
                            <p>{{ emptyText }}</p>
                            <button
                                v-if="isOpen"
                                type="button"
                                class="event-primary"
                                @click="openComposer"
                            >
                                <Camera aria-hidden="true" />Titipkan momen
                            </button>
                        </div>
                        <div
                            v-else-if="
                                activeTab === 'album' && albumView === 'grid'
                            "
                            class="photo-grid"
                        >
                            <button
                                v-for="photo in photos"
                                :key="photo.id"
                                type="button"
                                class="photo-grid-item"
                                :class="{
                                    'is-photobooth':
                                        photo.layout && photo.layout > 1,
                                }"
                                :aria-label="`Lihat foto dari ${photo.guest_name || 'seorang tamu'}`"
                                @click="selectedPhoto = photo"
                            >
                                <img
                                    v-if="photo.file_url"
                                    :src="photo.file_url"
                                    :alt="`Foto dari ${photo.guest_name || 'seorang tamu'}`"
                                    width="400"
                                    height="400"
                                    loading="lazy"
                                /><span
                                    v-if="photo.layout && photo.layout > 1"
                                    class="photo-layout-tag"
                                    ><Grid3X3 aria-hidden="true" />{{
                                        photo.layout
                                    }}</span
                                >
                            </button>
                        </div>
                        <div
                            v-else
                            :key="`${activeTab}-${noteFilter}`"
                            class="contribution-list"
                            :class="{ 'reels-list': activeTab === 'reels' }"
                        >
                            <InfiniteScroll
                                class="contribution-items-flow"
                                :data="'contributions'"
                                :only-next="true"
                                @start="polling.stop()"
                                @end="polling.start()"
                            >
                                <template #default>
                                    <ContributionCard
                                        v-for="contribution in filtered"
                                        :key="contribution.id"
                                        :contribution="contribution"
                                        :event-title="title"
                                    />
                                </template>
                                <template #loading>
                                    <ContributionSkeleton
                                        v-for="n in 3"
                                        :key="`sk-${n}`"
                                    />
                                </template>
                            </InfiniteScroll>
                        </div>
                    </template>
                    <p class="album-endnote">
                        Dititipkan hari ini. Dikenang berkali-kali.
                    </p>
                </section>
                <aside class="album-right-note">
                    <span class="right-note-number">Dari kamu,</span>
                    <p>
                        Yang sederhana<br />sering jadi yang<br /><em
                            >paling berharga.</em
                        >
                    </p>
                    <span>Foto spontan. Suara tawa.<br />Doa yang tulus.</span
                    ><button
                        v-if="isOpen"
                        class="event-text-button"
                        type="button"
                        @click="openComposer"
                    >
                        Titipkan ceritamu
                    </button>
                </aside>
            </div>
            <nav
                class="guest-bottom-nav"
                :class="{ 'guest-bottom-nav--four': !hasVideo }"
                aria-label="Navigasi acara"
            >
                <template v-for="(item, index) in nav" :key="item.id">
                    <button
                        v-if="hasVideo && index === 2"
                        type="button"
                        class="nav-camera"
                        :disabled="!isOpen"
                        :aria-label="
                            isOpen
                                ? 'Titipkan momen'
                                : 'Pengiriman momen ditutup'
                        "
                        @click="openComposer"
                    >
                        <span><Camera aria-hidden="true" /></span
                        ><small>Titip</small>
                    </button>
                    <Link
                        :href="sectionUrl(tabPaths[item.id])"
                        preserve-state
                        class="nav-destination"
                        :aria-current="
                            activeTab === item.id ? 'page' : undefined
                        "
                    >
                        <component :is="item.icon" aria-hidden="true" /><span>{{
                            item.label
                        }}</span>
                    </Link>
                </template>
                <button
                    v-if="!hasVideo"
                    type="button"
                    class="nav-camera"
                    :disabled="!isOpen"
                    :aria-label="
                        isOpen ? 'Titipkan momen' : 'Pengiriman momen ditutup'
                    "
                    @click="openComposer"
                >
                    <span><Camera aria-hidden="true" /></span
                    ><small>Titip</small>
                </button>
            </nav>
        </div>
        <ContributionComposer
            v-if="composing && isOpen"
            :event="event"
            @close="closeComposer"
            @sent="sent"
        />
        <Dialog
            :open="!!selectedPhoto"
            @update:open="
                (open) => {
                    if (!open) selectedPhoto = null;
                }
            "
            ><DialogContent
                :style="styles"
                class="guest-dialog photo-detail-dialog"
                :show-close-button="false"
                @keydown.esc.stop.prevent="selectedPhoto = null"
                ><div class="photo-detail-heading">
                    <DialogTitle
                        >Foto dari
                        <span class="notranslate" translate="no">{{
                            selectedPhoto?.guest_name || 'seorang tamu'
                        }}</span></DialogTitle
                    ><DialogClose as-child
                        ><button
                            type="button"
                            class="event-icon-button"
                            aria-label="Tutup foto"
                        >
                            <X aria-hidden="true" /></button
                    ></DialogClose>
                </div>
                <DialogDescription class="sr-only"
                    >Foto yang dititipkan untuk
                    <span class="notranslate" translate="no">{{
                        title
                    }}</span></DialogDescription
                ><ContributionCard
                    v-if="selectedPhoto"
                    :contribution="selectedPhoto"
                    :event-title="title" /></DialogContent
        ></Dialog>
    </main>
</template>
