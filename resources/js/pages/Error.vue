<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    AlertTriangle,
    ArrowLeft,
    ClockAlert,
    Compass,
    Home,
    KeyRound,
    RefreshCw,
    ShieldAlert,
    Sparkles,
    Zap,
} from '@lucide/vue';

interface Props {
    status?: number;
    message?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
    status: 404,
    message: null,
});

interface ErrorContent {
    status: number;
    badge: string;
    title: string;
    description: string;
    icon: any;
    primaryAction: {
        label: string;
        type: 'home' | 'reload' | 'login';
        url?: string;
    };
    secondaryAction?: {
        label: string;
        type: 'back' | 'home' | 'login';
        url?: string;
    };
}

const errorMap: Record<number, Omit<ErrorContent, 'status'>> = {
    401: {
        badge: '401 · Perlu Masuk',
        title: 'Silakan masuk ke akun Anda',
        description:
            'Akses ke halaman ini membutuhkan verifikasi identitas. Silakan masuk terlebih dahulu untuk melanjutkan.',
        icon: KeyRound,
        primaryAction: {
            label: 'Masuk ke Akun',
            type: 'login',
            url: '/login',
        },
        secondaryAction: {
            label: 'Ke Halaman Beranda',
            type: 'home',
            url: '/',
        },
    },
    403: {
        badge: '403 · Akses Dibatasi',
        title: 'Ruang ini memiliki akses khusus',
        description:
            'Akun Anda tidak memiliki izin untuk melihat atau mengelola bagian ini. Hubungi pemilik acara atau administrator jika ini sebuah kesalahan.',
        icon: ShieldAlert,
        primaryAction: {
            label: 'Kembali ke Beranda',
            type: 'home',
            url: '/',
        },
        secondaryAction: {
            label: 'Ganti Akun Login',
            type: 'login',
            url: '/login',
        },
    },
    404: {
        badge: '404 · Halaman Tidak Ditemukan',
        title: 'Momen yang Anda cari belum ada di sini',
        description:
            'Tautan yang Anda tuju mungkin salah ketik, sudah kedaluwarsa, atau album acara telah dipindahkan.',
        icon: Compass,
        primaryAction: {
            label: 'Kembali ke Beranda',
            type: 'home',
            url: '/',
        },
        secondaryAction: {
            label: 'Kembali Sebelumnya',
            type: 'back',
        },
    },
    419: {
        badge: '419 · Sesi Kedaluwarsa',
        title: 'Waktu interaksi telah berakhir',
        description:
            'Halaman berada dalam keadaan diam cukup lama demi keamanan. Silakan muat ulang halaman untuk memperbarui token keamanan.',
        icon: ClockAlert,
        primaryAction: {
            label: 'Segarkan Halaman',
            type: 'reload',
        },
        secondaryAction: {
            label: 'Ke Halaman Beranda',
            type: 'home',
            url: '/',
        },
    },
    429: {
        badge: '429 · Terlalu Banyak Permintaan',
        title: 'Mohon jeda sejenak',
        description:
            'Sistem menerima terlalu banyak permintaan dari perangkat Anda dalam waktu singkat. Tunggu beberapa saat sebelum mencoba kembali.',
        icon: Zap,
        primaryAction: {
            label: 'Coba Lagi',
            type: 'reload',
        },
        secondaryAction: {
            label: 'Ke Halaman Beranda',
            type: 'home',
            url: '/',
        },
    },
    500: {
        badge: '500 · Gangguan Server',
        title: 'Ada kendala teknis di balik layar',
        description:
            'Terjadi kesalahan internal pada sistem kami. Tim pengembang telah mencatat insiden ini dan sedang memperbaikinya.',
        icon: AlertTriangle,
        primaryAction: {
            label: 'Muat Ulang Halaman',
            type: 'reload',
        },
        secondaryAction: {
            label: 'Ke Halaman Beranda',
            type: 'home',
            url: '/',
        },
    },
    503: {
        badge: '503 · Sedang Pemeliharaan',
        title: 'Kami sedang mempercantik sistem',
        description:
            'titip.moment sedang melakukan pembaruan rutin agar pengalaman mengabadikan momen acara tetap lancar dan memukau.',
        icon: Sparkles,
        primaryAction: {
            label: 'Periksa Status Lagi',
            type: 'reload',
        },
        secondaryAction: {
            label: 'Ke Halaman Beranda',
            type: 'home',
            url: '/',
        },
    },
};

const currentError = computed<ErrorContent>(() => {
    const predefined = errorMap[props.status];
    if (predefined) {
        return {
            status: props.status,
            ...predefined,
        };
    }

    return {
        status: props.status || 500,
        badge: `${props.status || 'Galat'} · Terjadi Kendala`,
        title: 'Terjadi kesalahan tak terduga',
        description:
            props.message ||
            'Permintaan Anda tidak dapat diproses saat ini. Silakan coba muat ulang atau kembali ke beranda.',
        icon: AlertTriangle,
        primaryAction: {
            label: 'Segarkan Halaman',
            type: 'reload',
        },
        secondaryAction: {
            label: 'Ke Halaman Beranda',
            type: 'home',
            url: '/',
        },
    };
});

function handleAction(
    type: 'home' | 'reload' | 'login' | 'back',
    url?: string,
) {
    if (type === 'reload') {
        window.location.reload();
        return;
    }
    if (type === 'back') {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/';
        }
    }
}
</script>

<template>
    <Head
        :title="`${currentError.status} — ${currentError.title} | titip.moment`"
    />

    <div
        class="relative flex min-h-screen flex-col justify-between overflow-hidden bg-[#faf8f5] text-[#1d1c29] selection:bg-[#ffdad8] selection:text-[#10101a]"
    >
        <!-- Subtle warm ambient background glow -->
        <div
            class="pointer-events-none absolute -top-40 -left-40 size-96 rounded-full bg-[#ffdad8]/40 blur-3xl"
        />
        <div
            class="pointer-events-none absolute top-1/2 -right-40 size-96 rounded-full bg-[#ffc6a4]/30 blur-3xl"
        />

        <!-- Top Header Navigation -->
        <header
            class="relative z-10 mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-6 sm:py-8"
        >
            <Link
                href="/"
                class="inline-flex items-center gap-2.5 rounded-lg p-1 transition-opacity hover:opacity-85 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#ff5d6c]"
                aria-label="Kembali ke Beranda titip.moment"
            >
                <img
                    src="/images/titip-momen-logo-web.png"
                    alt="titip.moment logo"
                    width="112"
                    height="32"
                    class="h-7 w-auto object-contain"
                />
            </Link>

            <Link
                href="/"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#777586] transition-colors hover:text-[#1d1c29]"
            >
                <Home class="size-3.5" />
                <span>Beranda</span>
            </Link>
        </header>

        <!-- Main Error Display Card -->
        <main
            class="relative z-10 mx-auto flex w-full max-w-xl flex-1 flex-col items-center justify-center px-5 py-6"
        >
            <div
                class="relative w-full overflow-hidden rounded-3xl border border-[#e6dad7] bg-white p-7 text-center shadow-xl shadow-stone-900/5 transition-all sm:p-10"
            >
                <!-- Top Accent Line -->
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#ffdad8] via-[#ff5d6c] to-[#fd9e78]"
                />

                <!-- Status Badge and Icon -->
                <div class="flex flex-col items-center gap-4">
                    <div
                        class="flex size-16 items-center justify-center rounded-2xl border border-[#e6dad7] bg-[#f8f6f2] text-[#ff5d6c] shadow-sm sm:size-20"
                    >
                        <component
                            :is="currentError.icon"
                            class="size-8 stroke-[1.75] sm:size-10"
                        />
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-[#ffdad8] px-3.5 py-1 text-xs font-semibold tracking-wider text-[#d92c3f] uppercase"
                    >
                        {{ currentError.badge }}
                    </span>
                </div>

                <!-- Numerical Status Display (Decorative Serif) -->
                <div
                    class="mt-4 font-display text-5xl font-extrabold tracking-tight text-[#10101a]/15 select-none sm:text-6xl"
                    aria-hidden="true"
                >
                    {{ currentError.status }}
                </div>

                <!-- Editorial Headline -->
                <h1
                    class="mt-2 font-display text-2xl leading-tight font-bold text-[#10101a] sm:text-3xl"
                >
                    {{ currentError.title }}
                </h1>

                <!-- Friendly Description -->
                <p
                    class="mx-auto mt-3 max-w-md font-sans text-sm leading-relaxed text-[#777586] sm:text-base"
                >
                    {{ currentError.description }}
                </p>

                <!-- Technical error trace hint (if provided, e.g. dev/staging) -->
                <div
                    v-if="props.message && props.status >= 500"
                    class="mt-4 max-h-24 overflow-y-auto rounded-xl border border-[#e6dad7] bg-[#faf8f5] p-3 text-left font-mono text-xs break-all text-[#584140]"
                >
                    <span class="font-bold text-[#d92c3f]">Pesan teknis:</span>
                    {{ props.message }}
                </div>

                <!-- Action Buttons -->
                <div
                    class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                >
                    <!-- Primary Action -->
                    <template v-if="currentError.primaryAction.type === 'home'">
                        <Link
                            :href="currentError.primaryAction.url || '/'"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#ff5d6c] px-6 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-[#d92c3f] hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-[#ff5d6c] active:scale-[0.98] sm:w-auto"
                        >
                            <Home class="size-4" />
                            <span>{{ currentError.primaryAction.label }}</span>
                        </Link>
                    </template>
                    <template
                        v-else-if="currentError.primaryAction.type === 'login'"
                    >
                        <Link
                            :href="currentError.primaryAction.url || '/login'"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#ff5d6c] px-6 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-[#d92c3f] hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-[#ff5d6c] active:scale-[0.98] sm:w-auto"
                        >
                            <KeyRound class="size-4" />
                            <span>{{ currentError.primaryAction.label }}</span>
                        </Link>
                    </template>
                    <template v-else>
                        <button
                            type="button"
                            @click="
                                handleAction(currentError.primaryAction.type)
                            "
                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#ff5d6c] px-6 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-[#d92c3f] hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-[#ff5d6c] active:scale-[0.98] sm:w-auto"
                        >
                            <RefreshCw class="size-4" />
                            <span>{{ currentError.primaryAction.label }}</span>
                        </button>
                    </template>

                    <!-- Secondary Action -->
                    <template v-if="currentError.secondaryAction">
                        <template
                            v-if="currentError.secondaryAction.type === 'back'"
                        >
                            <button
                                type="button"
                                @click="handleAction('back')"
                                class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-[#e6dad7] bg-white px-5 py-3 text-sm font-medium text-[#1d1c29] transition-all duration-200 hover:border-[#d4c6c2] hover:bg-[#f6f3ee] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#ff5d6c] active:scale-[0.98] sm:w-auto"
                            >
                                <ArrowLeft class="size-4" />
                                <span>{{
                                    currentError.secondaryAction.label
                                }}</span>
                            </button>
                        </template>
                        <template v-else>
                            <Link
                                :href="currentError.secondaryAction.url || '/'"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#e6dad7] bg-white px-5 py-3 text-sm font-medium text-[#1d1c29] transition-all duration-200 hover:border-[#d4c6c2] hover:bg-[#f6f3ee] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#ff5d6c] active:scale-[0.98] sm:w-auto"
                            >
                                <component
                                    :is="
                                        currentError.secondaryAction.url ===
                                        '/login'
                                            ? KeyRound
                                            : Home
                                    "
                                    class="size-4"
                                />
                                <span>{{
                                    currentError.secondaryAction.label
                                }}</span>
                            </Link>
                        </template>
                    </template>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="relative z-10 w-full py-6 text-center text-xs text-[#777586]"
        >
            <p>
                © titip.moment · Mengabadikan setiap tawa dan doa di hari
                bahagia.
            </p>
        </footer>
    </div>
</template>
