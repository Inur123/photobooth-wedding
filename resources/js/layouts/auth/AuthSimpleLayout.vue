<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Camera,
    Heart,
    Mail,
    QrCode,
    ShieldCheck,
    Sparkles,
    Star,
    Zap,
} from '@lucide/vue';
import { login, register } from '@/routes';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        currentTab?: 'login' | 'register' | 'forgot' | 'verify' | 'reset';
    }>(),
    {
        title: '',
        description: '',
        currentTab: 'login',
    },
);
</script>

<template>
    <div
        class="sunset-mesh relative flex min-h-screen flex-col justify-between overflow-x-hidden font-sans text-on-surface antialiased selection:bg-primary-container selection:text-white"
    >
        <!-- Top Simple Navigation Strip -->
        <header
            class="z-20 mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 sm:px-6 sm:py-5 lg:px-8"
        >
            <Link href="/" class="group flex items-center gap-2">
                <img
                    src="/images/titip-momen-logo-web.png"
                    alt="titip.moment"
                    class="h-7 w-auto object-contain transition-transform group-hover:scale-105 sm:h-9"
                />
            </Link>
            <Link
                href="/"
                class="inline-flex items-center gap-1.5 rounded-full border border-outline-variant/60 bg-white px-3.5 py-1.5 text-xs font-semibold text-on-surface-variant shadow-xs transition-all hover:bg-surface-container-low hover:text-primary sm:px-4 sm:py-2"
            >
                <ArrowLeft class="h-3.5 w-3.5 text-primary" />
                <span>Kembali ke Beranda</span>
            </Link>
        </header>

        <!-- Main Split Layout Canvas -->
        <main
            class="mx-auto flex w-full max-w-7xl flex-1 items-center justify-center px-4 py-4 sm:px-6 sm:py-8 lg:px-8"
        >
            <div
                class="grid w-full grid-cols-1 items-center gap-8 lg:grid-cols-12 lg:gap-12"
            >
                <!-- LEFT SIDE: Showcase & Visual Storytelling Panel (Visible on lg, compact on mobile) -->
                <section
                    class="hidden flex-col justify-center space-y-6 lg:col-span-6 lg:flex lg:pr-4"
                >
                    <!-- Eyebrow Pill -->
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-primary/20 bg-white/95 px-3.5 py-1.5 text-xs font-bold tracking-wide text-primary shadow-xs"
                    >
                        <span
                            class="h-2 w-2 animate-pulse rounded-full bg-primary"
                        ></span>
                        <span>Platform Photobooth QR #1 di Indonesia</span>
                    </div>

                    <!-- Headline & Tagline -->
                    <div class="space-y-3">
                        <h1
                            class="text-3xl leading-tight font-extrabold tracking-tight text-on-surface sm:text-4xl"
                        >
                            Abadikan ribuan tawa dan momen estetik tamu di
                            acaramu.
                        </h1>
                        <p
                            class="max-w-lg text-sm leading-relaxed font-normal text-on-surface-variant sm:text-base"
                        >
                            Tanpa sewa alat berat, tanpa instalasi rumit. Cukup
                            cetak kartu QR di setiap meja, tamu langsung selfie
                            dengan bingkai kustom estetik khas Korea.
                        </p>
                    </div>

                    <!-- Value Badges Cluster -->
                    <div class="flex flex-wrap gap-2.5 pt-1">
                        <span
                            class="card-rim inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-on-surface shadow-xs"
                        >
                            <Zap class="h-3.5 w-3.5 text-primary-container" />
                            Setup 5 Menit
                        </span>
                        <span
                            class="card-rim inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-on-surface shadow-xs"
                        >
                            <Camera class="h-3.5 w-3.5 text-primary" />
                            Photobox di Setiap Tamu
                        </span>
                        <span
                            class="card-rim inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-on-surface shadow-xs"
                        >
                            <Sparkles
                                class="h-3.5 w-3.5 text-primary-container"
                            />
                            Bebas Antrean
                        </span>
                    </div>

                    <!-- Aesthetic Photostrip & Polaroid Preview Showcase (Korean Vibe) -->
                    <div class="relative pt-6 pb-2">
                        <!-- Ambient Glow Backdrop -->
                        <div
                            class="pointer-events-none absolute -top-10 -left-10 -z-10 h-72 w-72 rounded-full bg-primary-container/20 blur-3xl"
                        ></div>
                        <div
                            class="pointer-events-none absolute right-4 -bottom-10 -z-10 h-72 w-72 rounded-full bg-amber-400/20 blur-3xl"
                        ></div>

                        <div
                            class="card-rim relative max-w-lg rounded-2xl border border-outline-variant/30 bg-white/95 p-5 shadow-sm backdrop-blur-md"
                        >
                            <div
                                class="flex flex-col items-center gap-4 sm:flex-row sm:items-start"
                            >
                                <!-- Polaroid Mockup -->
                                <div
                                    class="polaroid-shadow w-32 shrink-0 -rotate-3 rounded-xl border border-outline-variant/40 bg-white p-2 transition-transform duration-300 hover:rotate-0 sm:w-36"
                                >
                                    <div
                                        class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-container"
                                    >
                                        <img
                                            class="h-full w-full object-cover"
                                            src="/images/stitch/auth-preview.jpg"
                                            alt="Preview wedding candid selfie"
                                        />
                                        <div
                                            class="absolute top-1.5 right-1.5 flex items-center gap-0.5 rounded-full bg-white/90 px-1.5 py-0.5 text-[10px] font-bold text-primary backdrop-blur-xs"
                                        >
                                            <Heart
                                                class="h-2.5 w-2.5 fill-primary text-primary"
                                            />
                                            <span>Sweet Love</span>
                                        </div>
                                    </div>
                                    <div class="pt-2 text-center">
                                        <p
                                            class="text-[11px] font-medium text-on-surface-variant"
                                        >
                                            Bagas &amp; Nadia '26
                                        </p>
                                    </div>
                                </div>

                                <!-- Live Stat & Social Proof Snippet -->
                                <div
                                    class="flex flex-1 flex-col justify-between space-y-3 py-1"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-surface-container-high px-2.5 py-1 text-xs font-semibold text-primary"
                                        >
                                            <QrCode class="h-3.5 w-3.5" />
                                            Scan &amp; Langsung Pose
                                        </span>
                                        <div class="flex -space-x-1.5">
                                            <span
                                                class="flex h-5 w-5 items-center justify-center rounded-full border border-white bg-rose-500 text-[9px] font-bold text-white"
                                            >
                                                R
                                            </span>
                                            <span
                                                class="flex h-5 w-5 items-center justify-center rounded-full border border-white bg-primary text-[9px] font-bold text-white"
                                            >
                                                D
                                            </span>
                                            <span
                                                class="flex h-5 w-5 items-center justify-center rounded-full border border-white bg-amber-500 text-[9px] font-bold text-white"
                                            >
                                                K
                                            </span>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <div
                                            class="flex items-center gap-1 text-amber-500"
                                        >
                                            <Star
                                                v-for="s in 5"
                                                :key="s"
                                                class="h-3.5 w-3.5 fill-amber-500 text-amber-500"
                                            />
                                        </div>
                                        <p
                                            class="text-xs leading-snug text-on-surface italic"
                                        >
                                            "Tamu wedding kami super happy!
                                            Galeri foto live langsung penuh
                                            ribuan foto estetik tanpa antri
                                            photobooth sama sekali."
                                        </p>
                                    </div>
                                    <div
                                        class="flex items-center gap-2 border-t border-outline-variant/30 pt-1"
                                    >
                                        <span
                                            class="text-xs font-bold text-primary"
                                        >
                                            15.000+ Momen
                                        </span>
                                        <span
                                            class="text-[11px] text-on-surface-variant"
                                        >
                                            sukses tersimpan bulan ini
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- RIGHT SIDE: Multi-State Interactive Auth Card -->
                <section class="flex w-full justify-center lg:col-span-6">
                    <div
                        class="card-rim relative w-full max-w-md overflow-hidden rounded-2xl border border-outline-variant/40 bg-white p-6 shadow-xl sm:p-8"
                    >
                        <!-- Top Decorative Gradient Bar -->
                        <div
                            class="absolute top-0 right-0 left-0 h-1.5 bg-gradient-to-r from-[#FF5E62] via-[#FF7E5F] to-[#FEB47B]"
                        ></div>

                        <!-- Tab Switcher (Visible on Login & Register) -->
                        <div
                            v-if="
                                currentTab === 'login' ||
                                currentTab === 'register'
                            "
                            class="mb-6"
                        >
                            <div
                                class="grid grid-cols-2 rounded-xl border border-outline-variant/40 bg-surface-container-low p-1 text-center text-sm font-semibold"
                            >
                                <Link
                                    :href="login()"
                                    class="rounded-lg py-2.5 font-bold transition-all"
                                    :class="
                                        currentTab === 'login'
                                            ? 'coral-button-gradient text-white shadow-sm'
                                            : 'text-on-surface-variant hover:text-on-surface'
                                    "
                                >
                                    Masuk
                                </Link>
                                <Link
                                    :href="register()"
                                    class="rounded-lg py-2.5 font-bold transition-all"
                                    :class="
                                        currentTab === 'register'
                                            ? 'coral-button-gradient text-white shadow-sm'
                                            : 'text-on-surface-variant hover:text-on-surface'
                                    "
                                >
                                    Daftar Baru
                                </Link>
                            </div>
                        </div>

                        <!-- Back to login for aux states (forgot/reset/verify) -->
                        <div
                            v-else-if="
                                currentTab === 'forgot' ||
                                currentTab === 'reset'
                            "
                            class="mb-5 flex items-center gap-2"
                        >
                            <Link
                                :href="login()"
                                class="flex h-8 w-8 items-center justify-center rounded-full border border-outline-variant/60 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary"
                                aria-label="Kembali ke halaman masuk"
                            >
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                            <span
                                class="text-xs font-bold tracking-wider text-primary uppercase"
                            >
                                Atur Ulang Kata Sandi
                            </span>
                        </div>

                        <!-- Form Title & Description -->
                        <div class="mb-5 space-y-1">
                            <h2
                                class="text-2xl font-bold tracking-tight text-on-surface sm:text-3xl"
                            >
                                {{ title }}
                            </h2>
                            <p
                                v-if="description"
                                class="text-sm leading-relaxed text-on-surface-variant"
                            >
                                {{ description }}
                            </p>
                        </div>

                        <!-- Form Slot -->
                        <slot />

                        <!-- Trust & Security Seal Footer inside card -->
                        <div
                            class="mt-6 flex items-center justify-center gap-2 border-t border-outline-variant/30 pt-4 text-xs text-on-surface-variant"
                        >
                            <ShieldCheck class="h-4 w-4 text-primary" />
                            <span
                                >Data acara dan galeri foto dienkripsi aman
                                256-bit</span
                            >
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Clean Bottom Branding Bar -->
        <footer
            class="mx-auto flex w-full max-w-7xl flex-col items-center gap-2.5 px-4 pt-4 pb-8 text-center text-xs text-on-surface-variant sm:flex-row sm:justify-between sm:px-6 sm:py-5 sm:text-left lg:px-8"
        >
            <!-- 1. Email Kontak (Atas di mobile, kanan di desktop) -->
            <a
                href="mailto:kontak@titipmoment.id"
                class="order-1 inline-flex items-center gap-1.5 font-medium transition-colors hover:text-primary sm:order-3"
            >
                <Mail class="h-3.5 w-3.5 text-primary" />
                <span>kontak@titipmoment.id</span>
            </a>

            <!-- 2. Legal Links (Tengah di mobile & desktop) -->
            <div
                class="order-2 flex items-center justify-center gap-3.5 font-medium sm:order-2"
            >
                <Link
                    href="/terms"
                    class="transition-colors hover:text-primary"
                >
                    Syarat &amp; Ketentuan
                </Link>
                <span class="text-outline-variant/80">•</span>
                <Link
                    href="/privacy"
                    class="transition-colors hover:text-primary"
                >
                    Kebijakan Privasi
                </Link>
            </div>

            <!-- 3. Copyright (Bawah di mobile, kiri di desktop) -->
            <div
                class="order-3 leading-relaxed text-on-surface-variant/80 sm:order-1"
            >
                © {{ new Date().getFullYear() }} titip.moment · Seluruh hak
                cipta dilindungi.
            </div>
        </footer>
    </div>
</template>
