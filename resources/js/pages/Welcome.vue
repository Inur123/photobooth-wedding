<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Camera,
    Check,
    CheckCircle2,
    ChevronDown,
    CloudDownload,
    Flame,
    Heart,
    Images,
    Mail,
    Menu,
    Mic,
    Palette,
    Play,
    Rocket,
    ShieldCheck,
    Smartphone,
    Sparkles,
    Star,
    SwitchCamera,
    Timer,
    Video,
    X,
    Zap,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { dashboard, login, register } from '@/routes';

interface PackageItem {
    id: number;
    name: string;
    tagline: string | null;
    price: number;
    benefits: string[] | null;
    gallery_days?: number;
    max_frames?: number;
    allow_zip_download?: boolean;
    allow_custom_theme?: boolean;
    allow_video?: boolean;
    max_video_seconds?: number | null;
}

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        packages: PackageItem[];
    }>(),
    {
        canRegister: true,
        packages: () => [],
    },
);

const mobileMenuOpen = ref(false);
const activePhoneMode = ref<'photostrip' | 'video' | 'voice' | 'wish'>('photostrip');
const openFaqIndex = ref<number | null>(0);

interface NavItem {
    label: string;
    href: string;
    id: string;
}

const navItems: NavItem[] = [
    { label: 'Bandingkan', href: '#perbandingan', id: 'perbandingan' },
    { label: 'Cara Kerja', href: '#cara-kerja', id: 'cara-kerja' },
    { label: 'Contoh Photostrip', href: '#gaya-foto', id: 'gaya-foto' },
    { label: 'Fitur Tamu', href: '#fitur', id: 'fitur' },
    { label: 'Harga Paket', href: '#paket', id: 'paket' },
    { label: 'FAQ', href: '#faq', id: 'faq' },
];

const activeSection = ref<string>('');
let isManualScroll = false;
let scrollTimeout: ReturnType<typeof setTimeout> | null = null;

function updateActiveSection(): void {
    if (isManualScroll) return;

    // Area Hero di bagian paling atas
    if (window.scrollY < 200) {
        if (!window.location.hash || window.scrollY < 80) {
            activeSection.value = '';
        }
        return;
    }

    // Jika sudah mencapai bagian bawah halaman
    const scrollBottom = window.innerHeight + window.scrollY;
    const documentHeight = document.documentElement.scrollHeight;
    if (scrollBottom >= documentHeight - 70) {
        activeSection.value = 'faq';
        return;
    }

    // Ambang batas persis di bawah fixed navbar (tinggi navbar 80px)
    const threshold = 140;

    for (let i = navItems.length - 1; i >= 0; i--) {
        const item = navItems[i];
        const el = document.getElementById(item.id);
        if (el) {
            const rect = el.getBoundingClientRect();
            if (rect.top <= threshold) {
                activeSection.value = item.id;
                return;
            }
        }
    }
}

function handleScroll(): void {
    updateActiveSection();
}

function handleHashChange(): void {
    const hash = window.location.hash.replace('#', '');
    if (navItems.some((item) => item.id === hash)) {
        activeSection.value = hash;
    }
}

function scrollToSection(id: string, e?: Event, isMobile = false): void {
    if (e) {
        e.preventDefault();
    }
    if (isMobile) {
        mobileMenuOpen.value = false;
    }

    activeSection.value = id;
    isManualScroll = true;

    if (scrollTimeout) {
        clearTimeout(scrollTimeout);
    }

    if (window.history.pushState) {
        window.history.pushState(null, '', `#${id}`);
    } else {
        window.location.hash = `#${id}`;
    }

    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }

    scrollTimeout = setTimeout(() => {
        isManualScroll = false;
        updateActiveSection();
    }, 850);
}

onMounted(() => {
    if (window.location.hash) {
        const hash = window.location.hash.replace('#', '');
        if (navItems.some((item) => item.id === hash)) {
            activeSection.value = hash;
        }
    } else {
        updateActiveSection();
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('hashchange', handleHashChange);
});

onUnmounted(() => {
    if (scrollTimeout) {
        clearTimeout(scrollTimeout);
    }
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('hashchange', handleHashChange);
});

function toggleFaq(index: number): void {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
}

function formatRupiah(amount: number): string {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

const packagePrices = computed(() => {
    const basicPkg = props.packages.find((p) => p.name.toLowerCase().includes('basic'));
    const plusPkg = props.packages.find((p) => p.name.toLowerCase().includes('plus'));
    const fullPkg = props.packages.find((p) => p.name.toLowerCase().includes('full'));

    return {
        basic: basicPkg ? formatRupiah(basicPkg.price) : 'Rp 150.000',
        plus: plusPkg ? formatRupiah(plusPkg.price) : 'Rp 250.000',
        full: fullPkg ? formatRupiah(fullPkg.price) : 'Rp 350.000',
        basicDays: basicPkg?.gallery_days ?? 30,
        plusDays: plusPkg?.gallery_days ?? 60,
        fullDays: fullPkg?.gallery_days ?? 90,
    };
});

const faqs = [
    {
        question: 'Apakah tamu perlu download aplikasi dari Play Store / App Store?',
        answer: 'Sama sekali TIDAK! Tamu tidak perlu menginstal aplikasi atau membuat akun. Cukup buka aplikasi kamera bawaan HP (Android maupun iPhone), arahkan ke barcode QR, dan halaman web photobooth acaramu langsung terbuka dalam hitungan detik.',
    },
    {
        question: 'Apakah ada mesin fisik atau kru operator yang datang ke lokasi acara?',
        answer: 'Tidak. titip.moment adalah layanan photobooth 100% digital berbasis web mandiri (self-service). Semua proses dilakukan langsung dari browser ponsel tamu, sehingga kamu hemat biaya sewa mesin jutaan rupiah dan bebas biaya transport kru.',
    },
    {
        question: 'Bagaimana jika sinyal internet di lokasi acara agak lemah?',
        answer: 'Sistem kami sangat ringan dan seluruh foto tamu otomatis dikonversi ke format WebP teroptimasi di latar belakang, sehingga tetap lancar meski menggunakan sinyal 4G standar atau Wi-Fi gedung resepsi.',
    },
    {
        question: 'Berapa banyak foto atau video yang bisa dikirim oleh tamu?',
        answer: 'Tidak ada batasan jumlah foto! Tamu bebas berfoto dan mengirim momen sebanyak-banyaknya selama acara berlangsung tanpa khawatir kehabisan kuota jepretan.',
    },
    {
        question: 'Apakah hasil foto bisa disetel privasi atau dimoderasi oleh pengantin?',
        answer: 'Bisa! Di dashboard pengelola acara, kamu bebas memilih visibilitas galeri: Publik (semua tamu bisa melihat album) atau Privat (hanya pengantin/penyelenggara yang bisa melihat). Kamu juga bisa menyembunyikan atau menghapus kiriman tertentu kapan saja.',
    },
    {
        question: 'Bagaimana cara mengunduh semua foto, video, dan suara setelah acara selesai?',
        answer: 'Pada paket Plus dan Full, kamu cukup login ke dashboard client dan klik tombol "Download ZIP". Seluruh foto photostrip berbingkai, video ucapan, rekaman pesan suara, serta daftar ucapan tertulis (wishes.txt) akan terunduh rapi dalam satu file arsip.',
    },
];
</script>

<template>

    <Head title="titip.moment — Photobooth Digital Pernikahan & Event | Scan QR dari HP Tamu" />

    <div
        class="sunset-mesh relative min-h-screen overflow-x-hidden font-sans text-on-surface antialiased selection:bg-primary-container selection:text-white">
        <!-- TOP NAVBAR (Fixed) -->
        <header
            class="fixed top-0 right-0 left-0 z-50 border-b border-outline-variant/50 bg-white/95 backdrop-blur-md transition-all shadow-xs">
            <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Brand Logo from public images -->
                <Link href="/" class="group flex items-center gap-2">
                    <img src="/images/titip-momen-logo-web.png" alt="titip.moment"
                        class="h-8 w-auto object-contain transition-transform group-hover:scale-105 sm:h-9" />
                </Link>

                <!-- Nav Links -->
                <nav class="hidden items-center gap-7 text-sm font-semibold md:flex">
                    <a
                        v-for="item in navItems"
                        :key="item.id"
                        :href="item.href"
                        class="group relative py-1 transition-all duration-200"
                        :class="activeSection === item.id
                            ? 'font-bold text-primary'
                            : 'text-on-surface-variant hover:text-primary'"
                        @click="scrollToSection(item.id, $event)"
                    >
                        <span>{{ item.label }}</span>
                        <span
                            v-if="activeSection === item.id"
                            class="coral-gradient absolute -bottom-1.5 left-0 right-0 h-0.5 rounded-full transition-all duration-200"
                        />
                    </a>
                </nav>

                <!-- Header Actions -->
                <div class="hidden items-center gap-3 sm:flex">
                    <template v-if="$page.props.auth.user">
                        <a class="inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary-fixed/60 px-4 py-2 text-xs font-bold text-primary transition-all hover:bg-primary-fixed"
                            href="#gaya-foto"
                            @click="scrollToSection('gaya-foto', $event)">
                            <Play class="h-3.5 w-3.5 fill-current" />
                            <span>Contoh Frame</span>
                        </a>
                        <Link :href="dashboard()"
                            class="coral-gradient inline-flex items-center gap-1.5 rounded-full px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg">
                            <span>Buka Dashboard</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()"
                            class="inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary-fixed/60 px-4 py-2 text-xs font-bold text-primary transition-all hover:bg-primary-fixed">
                            Masuk Akun
                        </Link>
                        <Link v-if="props.canRegister" :href="register()"
                            class="coral-gradient inline-flex items-center gap-1.5 rounded-full px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg">
                            <span>Bikin Photobooth</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                </div>

                <!-- Mobile Menu Toggle -->
                <button type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-outline-variant bg-white text-on-surface sm:hidden"
                    :aria-expanded="mobileMenuOpen" aria-label="Buka menu navigasi"
                    @click="mobileMenuOpen = !mobileMenuOpen">
                    <X v-if="mobileMenuOpen" class="h-5 w-5" />
                    <Menu v-else class="h-5 w-5" />
                </button>
            </div>

            <!-- Mobile Dropdown Menu -->
            <div v-if="mobileMenuOpen"
                class="border-t border-outline-variant/60 bg-white/98 px-4 py-5 backdrop-blur-md md:hidden max-h-[calc(100vh-5rem)] overflow-y-auto shadow-lg">
                <div class="flex flex-col gap-1.5 text-sm font-semibold">
                    <a
                        v-for="item in navItems"
                        :key="item.id"
                        :href="item.href"
                        class="flex items-center justify-between rounded-xl px-3.5 py-2.5 transition-all duration-200"
                        :class="activeSection === item.id
                            ? 'bg-primary-fixed/60 font-bold text-primary shadow-xs'
                            : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary'"
                        @click="scrollToSection(item.id, $event, true)"
                    >
                        <span>{{ item.label }}</span>
                        <span
                            v-if="activeSection === item.id"
                            class="h-2 w-2 rounded-full bg-primary"
                        />
                    </a>
                </div>
                <div class="mt-4 flex flex-col gap-2.5 border-t border-outline-variant/50 pt-4">
                    <template v-if="$page.props.auth.user">
                        <Link :href="dashboard()"
                            class="coral-gradient inline-flex items-center justify-center gap-2 rounded-full px-5 py-3 text-xs font-bold text-white shadow-md">
                            <span>Buka Dashboard</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()"
                            class="inline-flex items-center justify-center rounded-full border border-primary/25 bg-primary-fixed/60 px-5 py-2.5 text-xs font-bold text-primary">
                            Masuk Akun Client
                        </Link>
                        <Link v-if="props.canRegister" :href="register()"
                            class="coral-gradient inline-flex items-center justify-center gap-1.5 rounded-full px-5 py-3 text-xs font-bold text-white shadow-md">
                            <span>Bikin Photobooth</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- 1. HERO SECTION -->
        <section class="relative overflow-hidden pt-28 pb-16 sm:pt-32 lg:pt-36 lg:pb-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-8">
                    <!-- Left Hero Text & Immediate Value Pitch -->
                    <div class="z-10 flex flex-col items-start gap-6 lg:col-span-7">
                        <!-- Value Badge with Icon -->
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-primary-container/40 bg-white/90 px-4 py-2 text-xs font-bold text-primary shadow-sm">
                            <span
                                class="coral-gradient flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white">
                                <Camera class="h-3.5 w-3.5" />
                            </span>
                            <span>
                                Photobooth Digital untuk Pernikahan &amp; Event • Tanpa Sewa Mesin
                                Mahal!
                            </span>
                        </div>

                        <!-- Main Headline -->
                        <h1
                            class="text-4xl leading-[1.12] font-black tracking-tight text-on-surface sm:text-5xl lg:text-6xl">
                            Bikin Tamu Punya <br class="hidden sm:inline" />
                            <span
                                class="bg-gradient-to-r from-[#FF5E62] via-[#FF7E5F] to-[#FEB47B] bg-clip-text text-transparent">
                                Photobox Sendiri di HP.
                            </span>
                            <br />
                            Tinggal Scan QR!
                        </h1>

                        <!-- Subheadline -->
                        <p class="max-w-2xl text-base leading-relaxed font-normal text-on-surface-variant sm:text-lg">
                            Gak perlu sewa mesin fotobooth jutaan rupiah atau antre panjang bikin
                            capek. Tamu tinggal <strong>scan barcode</strong>, langsung
                            foto dengan bingkai estetik acaramu, dan foto/video otomatis terkumpul
                            ke <strong>galeri live real-time</strong>.
                        </p>

                        <!-- Social Proof & Trust Chips -->
                        <div class="flex w-full flex-wrap items-center gap-2.5 py-1 sm:gap-3">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-outline-variant/70 bg-white px-3 py-1.5 text-xs font-semibold text-on-surface shadow-xs">
                                <Zap class="h-3.5 w-3.5 text-primary" />
                                <span>5 Menit setup instan</span>
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-outline-variant/70 bg-white px-3 py-1.5 text-xs font-semibold text-on-surface shadow-xs">
                                <Smartphone class="h-3.5 w-3.5 text-primary" />
                                <span>Tanpa download aplikasi</span>
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-outline-variant/70 bg-white px-3 py-1.5 text-xs font-semibold text-on-surface shadow-xs">
                                <Images class="h-3.5 w-3.5 text-primary" />
                                <span>Photostrip estetik Korea</span>
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-outline-variant/70 bg-white px-3 py-1.5 text-xs font-semibold text-on-surface shadow-xs">
                                <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-rose-500"></span>
                                <span>Galeri live real-time</span>
                            </span>
                        </div>

                        <!-- Primary CTA Buttons -->
                        <div
                            class="flex w-full flex-col items-stretch gap-3.5 pt-2 sm:w-auto sm:flex-row sm:items-center">
                            <Link :href="$page.props.auth.user ? dashboard() : register()"
                                class="coral-gradient inline-flex items-center justify-center gap-2.5 rounded-full px-8 py-4 text-sm font-bold text-white shadow-xl shadow-primary-container/30 transition-all hover:scale-[1.02] hover:shadow-2xl active:scale-98 sm:text-base">
                                <Smartphone class="h-5 w-5" />
                                <span>Buat Photobooth Acaramu</span>
                            </Link>
                            <a href="#gaya-foto"
                                class="inline-flex items-center justify-center gap-2 rounded-full border border-outline-variant bg-white px-6 py-4 text-sm font-bold text-on-surface shadow-sm transition-all hover:bg-surface-container-low sm:text-base"
                                @click="scrollToSection('gaya-foto', $event)">
                                <span>Lihat Contoh Photostrip</span>
                                <ArrowRight class="h-[18px] w-[18px] text-primary" />
                            </a>
                        </div>

                        <!-- Social Proof Micro-Rating with Star icons -->
                        <div class="flex items-center gap-3 pt-2 text-xs text-on-surface-variant">
                            <div class="flex -space-x-2">
                                <img alt="Avatar pengantin"
                                    class="h-8 w-8 rounded-full border-2 border-white object-cover shadow-xs"
                                    src="/images/stitch/img-01.jpg" />
                                <img alt="Avatar pengantin"
                                    class="h-8 w-8 rounded-full border-2 border-white object-cover shadow-xs"
                                    src="/images/stitch/img-02.jpg" />
                                <img alt="Avatar pengantin"
                                    class="h-8 w-8 rounded-full border-2 border-white object-cover shadow-xs"
                                    src="/images/stitch/img-03.jpg" />
                                <img alt="Avatar pengantin"
                                    class="h-8 w-8 rounded-full border-2 border-white object-cover shadow-xs"
                                    src="/images/stitch/img-04.jpg" />
                            </div>
                            <div>
                                <div class="flex items-center gap-0.5 text-amber-500">
                                    <Star v-for="s in 5" :key="s" class="h-3.5 w-3.5 fill-amber-500 text-amber-500" />
                                    <span class="ml-1 font-extrabold text-on-surface">4.9/5</span>
                                </div>
                                <span>
                                    Digunakan di Pernikahan, Ulang Tahun &amp; Event Kantor
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Visual: Smartphone Screen + Floating Photostrip Korea + Scan QR Mockup -->
                    <div class="relative flex items-center justify-center py-6 lg:col-span-5">
                        <!-- Glow Aura -->
                        <div
                            class="pointer-events-none absolute -z-10 h-80 w-80 rounded-full bg-primary-container/25 blur-3xl">
                        </div>
                        <div
                            class="pointer-events-none absolute right-2 -bottom-6 -z-10 h-64 w-64 rounded-full bg-secondary-container/25 blur-2xl">
                        </div>

                        <!-- FLOATING ITEM 1: Real-life Photostrip 3-Cut Korea (Aesthetic) -->
                        <div class="animate-float absolute top-6 -left-2 z-30 sm:-left-10">
                            <div
                                class="photostrip-shadow flex w-32 rotate-[-5deg] flex-col items-center gap-2 rounded-2xl border border-white bg-white p-3 sm:w-36">
                                <!-- Top sticker with Sparkles icon -->
                                <div
                                    class="absolute -top-3 left-4 flex items-center gap-1 rounded-full bg-amber-300 px-2.5 py-0.5 text-[9px] font-black tracking-wide text-amber-950 uppercase shadow-sm">
                                    <Sparkles class="h-2.5 w-2.5" />
                                    <span>Just Married</span>
                                </div>
                                <!-- Strip Photos -->
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-lg bg-rose-100">
                                    <img alt="Pose 1 Wedding" class="h-full w-full object-cover"
                                        src="/images/stitch/img-05.jpg" />
                                </div>
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-lg bg-rose-100">
                                    <img alt="Pose 2 Wedding" class="h-full w-full object-cover"
                                        src="/images/stitch/img-06.jpg" />
                                </div>
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-lg bg-rose-100">
                                    <img alt="Pose 3 Wedding" class="h-full w-full object-cover"
                                        src="/images/stitch/img-07.jpg" />
                                </div>
                                <!-- Strip Footer -->
                                <div class="pt-1 text-center">
                                    <p class="text-[10px] font-extrabold tracking-wider text-primary uppercase">
                                        DIMAS &amp; DITA
                                    </p>
                                    <p class="text-[8px] font-medium text-tertiary">
                                        24.10.2026 • Magetan
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- FLOATING ITEM 2: QR Table Acrylic Card -->
                        <div class="animate-float-rev absolute -right-2 bottom-8 z-30 sm:-right-8">
                            <div
                                class="photostrip-shadow flex max-w-[170px] rotate-[4deg] flex-col items-center gap-2 rounded-2xl border border-white bg-white/95 p-3.5 text-center backdrop-blur-md">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-primary px-2 py-0.5 text-[9px] font-extrabold tracking-wider text-white uppercase">
                                    Scan QR
                                </span>
                                <!-- QR Graphic -->
                                <div
                                    class="relative flex h-20 w-20 items-center justify-center rounded-xl bg-slate-900 p-2 shadow-inner">
                                    <div class="flex h-full w-full flex-col justify-between rounded-lg bg-white p-1.5">
                                        <div class="flex justify-between">
                                            <div class="h-4 w-4 rounded-sm bg-slate-900"></div>
                                            <div class="h-4 w-4 rounded-sm bg-slate-900"></div>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <Camera class="h-3.5 w-3.5 text-primary" />
                                        </div>
                                        <div class="flex justify-between">
                                            <div class="h-4 w-4 rounded-sm bg-slate-900"></div>
                                            <div class="h-2 w-2 bg-slate-900"></div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[10px] leading-tight font-bold text-on-surface">
                                    Arahkan kamera HP &amp; langsung foto!
                                </p>
                            </div>
                        </div>

                        <!-- Floating Live Guest Counter Toast with Camera icon -->
                        <div
                            class="absolute -top-3 right-4 z-40 flex items-center gap-2 rounded-full border border-primary/20 bg-white/95 px-3.5 py-2 shadow-lg backdrop-blur-md">
                            <Camera class="h-3.5 w-3.5 text-primary" />
                            <span class="text-xs font-bold text-on-surface">
                                142 Foto masuk dari tamu!
                            </span>
                        </div>

                        <!-- MAIN SMARTPHONE MOCKUP -->
                        <div
                            class="relative z-20 w-full max-w-[285px] rounded-[46px] border-4 border-slate-800 bg-slate-950 p-3.5 shadow-2xl sm:max-w-[310px]">
                            <!-- Top Dynamic Island / Notch -->
                            <div
                                class="absolute top-5 left-1/2 z-40 flex h-5 w-24 -translate-x-1/2 items-center justify-center rounded-full bg-slate-950">
                                <div class="mr-2 h-3 w-3 rounded-full bg-slate-800/80"></div>
                                <div class="h-2 w-2 rounded-full bg-emerald-500/80"></div>
                            </div>

                            <!-- Phone Inner Screen -->
                            <div
                                class="relative flex min-h-[570px] w-full flex-col items-center justify-between overflow-hidden rounded-[38px] bg-[#111622] px-3 pt-8 pb-4 text-white">
                                <!-- Live Web Bar / Event Header -->
                                <div class="flex w-full items-center justify-between px-2 pt-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                        <span class="text-[11px] font-bold tracking-tight text-white/90">
                                            titip.moment/e/dimasdita
                                        </span>
                                    </div>
                                    <span
                                        class="flex items-center gap-1 rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-bold text-white/80">
                                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-rose-500"></span>
                                        <span>LIVE</span>
                                    </span>
                                </div>

                                <!-- Photobooth Viewfinder Container with Custom Frame -->
                                <div class="relative mt-2 flex w-full flex-col items-center justify-between overflow-hidden rounded-2xl bg-white p-2.5 shadow-xl"
                                    style="
                                        background: linear-gradient(
                                            180deg,
                                            #ffffff 0%,
                                            #fff5f2 100%
                                        );
                                    ">
                                    <!-- Frame Header Event Customization -->
                                    <div class="flex w-full items-center justify-between px-1 pb-1 text-[#ae2f34]">
                                        <span class="text-[11px] font-extrabold tracking-wider uppercase">
                                            DIMAS &amp; DITA
                                        </span>
                                        <span class="text-[9px] font-semibold text-slate-500">
                                            POSE 2/3
                                        </span>
                                    </div>

                                    <!-- Camera Viewport Preview -->
                                    <div
                                        class="relative flex h-64 w-full items-center justify-center overflow-hidden rounded-xl bg-slate-900">
                                        <img alt="Live Camera Preview Photobooth"
                                            class="h-full w-full object-cover brightness-95"
                                            src="/images/stitch/img-08.jpg" />
                                        <!-- Viewfinder Grid Marks -->
                                        <div
                                            class="pointer-events-none absolute inset-0 m-3 rounded-xl border-2 border-white/30">
                                        </div>
                                        <!-- Timer Count Overlay -->
                                        <div
                                            class="absolute top-3 left-3 flex items-center gap-1 rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-md">
                                            <Timer class="h-3.5 w-3.5 text-amber-400" />
                                            <span>Pose 2 detik lagi!</span>
                                        </div>
                                        <!-- Sticker overlay with Heart icon -->
                                        <div
                                            class="absolute right-3 bottom-3 flex items-center gap-1 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-extrabold text-rose-600 shadow-md">
                                            <Heart class="h-2.5 w-2.5 fill-rose-600" />
                                            <span>Happy Wedding!</span>
                                        </div>
                                    </div>

                                    <!-- Frame Footer -->
                                    <div
                                        class="flex w-full items-center justify-between pt-1.5 text-[8px] font-semibold text-slate-500">
                                        <span>WEDDING CELEBRATION</span>
                                        <span>MAGETAN • 2026</span>
                                    </div>
                                </div>

                                <!-- Mode Switcher: Photostrip / Video / Voice / Wishes -->
                                <div
                                    class="mt-2 flex items-center justify-center gap-1 rounded-full bg-white/10 p-1 text-[11px]">
                                    <button type="button" class="rounded-full px-3 py-1 transition-all" :class="activePhoneMode === 'photostrip'
                                        ? 'bg-primary font-bold text-white shadow-xs'
                                        : 'text-white/70 hover:text-white'
                                        " @click="activePhoneMode = 'photostrip'">
                                        Photostrip
                                    </button>
                                    <button type="button" class="rounded-full px-2.5 py-1 transition-all" :class="activePhoneMode === 'video'
                                        ? 'bg-primary font-bold text-white shadow-xs'
                                        : 'text-white/70 hover:text-white'
                                        " @click="activePhoneMode = 'video'">
                                        Video 20s
                                    </button>
                                    <button type="button" class="rounded-full px-2.5 py-1 transition-all" :class="activePhoneMode === 'voice'
                                        ? 'bg-primary font-bold text-white shadow-xs'
                                        : 'text-white/70 hover:text-white'
                                        " @click="activePhoneMode = 'voice'">
                                        Voice
                                    </button>
                                    <button type="button" class="rounded-full px-2 py-1 transition-all" :class="activePhoneMode === 'wish'
                                        ? 'bg-primary font-bold text-white shadow-xs'
                                        : 'text-white/70 hover:text-white'
                                        " @click="activePhoneMode = 'wish'">
                                        Pesan
                                    </button>
                                </div>

                                <!-- Camera Shutter Bottom Controls -->
                                <div class="flex w-full items-center justify-between px-4 pt-2">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white">
                                        <Images class="h-[18px] w-[18px]" />
                                    </div>
                                    <!-- Shutter Button -->
                                    <Link :href="$page.props.auth.user ? dashboard() : register()"
                                        aria-label="Mulai buat photobooth"
                                        class="flex h-16 w-16 cursor-pointer items-center justify-center rounded-full border-4 border-white/40 p-1 transition-transform hover:scale-105">
                                        <div
                                            class="coral-gradient flex h-full w-full items-center justify-center rounded-full text-white shadow-lg">
                                            <Camera class="h-[26px] w-[26px]" />
                                        </div>
                                    </Link>
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white">
                                        <SwitchCamera class="h-[18px] w-[18px]" />
                                    </div>
                                </div>

                                <!-- Home Bar Indicator -->
                                <div class="mt-2 h-1 w-28 rounded-full bg-white/30"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. SECTION: BANDINGKAN DENGAN PHOTOBOOTH KONVENSIONAL -->
        <section id="perbandingan" class="border-y border-outline-variant/40 bg-white py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <span
                        class="rounded-full bg-primary-fixed/60 px-4 py-1.5 text-xs font-bold tracking-wider text-primary uppercase">
                        Hemat Waktu &amp; Biaya
                    </span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-on-surface sm:text-4xl">
                        Kenapa Pilih Photobooth Digital?
                    </h2>
                    <p class="mt-2 text-base text-on-surface-variant">
                        Lihat perbandingan langsung antara sewa photobooth fisik konvensional vs
                        titip.moment.
                    </p>
                </div>

                <!-- Comparison Table Grid -->
                <div class="mx-auto grid max-w-5xl grid-cols-1 items-stretch gap-8 md:grid-cols-2">
                    <!-- Kolom Kiri: Photobooth Fisik Konvensional -->
                    <div
                        class="flex flex-col justify-between rounded-3xl border border-outline-variant/70 bg-surface-container-low/70 p-8">
                        <div>
                            <div
                                class="mb-4 inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-3 py-1 text-xs font-bold tracking-wide text-slate-700 uppercase">
                                <X class="h-3.5 w-3.5 text-rose-500" />
                                <span>Photobooth Fisik Biasa</span>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-800">
                                Sewa Mesin Fotobox Konvensional
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Biaya mahal dan sering bikin tamu bosan antre.
                            </p>
                            <div class="my-6 border-t border-slate-200"></div>
                            <ul class="space-y-4 text-sm text-slate-700">
                                <li class="flex items-start gap-3">
                                    <X class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" />
                                    <span>
                                        <strong>Biaya Mahal:</strong> Rata-rata Rp 3.000.000 – Rp
                                        5.000.000 per 3 jam acara.
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <X class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" />
                                    <span>
                                        <strong>Antrean Panjang Mengular:</strong> Tamu malas foto
                                        karena harus antre 10–20 menit di satu titik.
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <X class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" />
                                    <span>
                                        <strong>Cuma Dapat Cetakan Terbatas:</strong> Kalau foto
                                        bareng 5 orang, yang dapat kertas cetak sering cuma 1 orang.
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <X class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" />
                                    <span>
                                        <strong>Ribet Ruang &amp; Operator:</strong> Butuh area
                                        luas, kabel colokan listrik besar, dan kru operator di
                                        venue.
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-8 rounded-xl bg-slate-100 p-3.5 text-center text-xs font-medium text-slate-500">
                            Budget membengkak &amp; hanya sedikit tamu yang sempat foto.
                        </div>
                    </div>

                    <!-- Kolom Kanan: titip.moment -->
                    <div
                        class="glass-card relative flex flex-col justify-between rounded-3xl border-2 border-primary-container p-8 shadow-xl shadow-primary-container/10">
                        <!-- Floating highlight badge with Sparkles icon -->
                        <div
                            class="coral-gradient absolute -top-3.5 right-6 flex items-center gap-1 rounded-full px-4 py-1 text-xs font-black tracking-wider text-white uppercase shadow-md">
                            <Sparkles class="h-3.5 w-3.5" />
                            <span>Solusi Modern &amp; Cerdas</span>
                        </div>
                        <div>
                            <div
                                class="mb-4 inline-flex items-center gap-1.5 rounded-full bg-primary-fixed px-3 py-1 text-xs font-bold tracking-wide text-primary uppercase">
                                <Sparkles class="h-3.5 w-3.5 text-primary" />
                                <span>titip.moment Digital Photobooth</span>
                            </div>
                            <h3 class="text-2xl font-black text-on-surface">
                                Photobooth Digital dari HP Tamu
                            </h3>
                            <p class="mt-1 text-sm font-bold text-primary">
                                Hemat hingga 90% dengan kebebasan tanpa batas!
                            </p>
                            <div class="my-6 border-t border-primary-container/30"></div>
                            <ul class="space-y-4 text-sm text-on-surface">
                                <li class="flex items-start gap-3">
                                    <CheckCircle2 class="mt-0.5 h-[22px] w-[22px] shrink-0 text-emerald-600" />
                                    <span>
                                        <strong>Mulai {{ packagePrices.basic }}:</strong> Super
                                        hemat, tanpa biaya sewa mesin jutaan atau transport kru.
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <CheckCircle2 class="mt-0.5 h-[22px] w-[22px] shrink-0 text-emerald-600" />
                                    <span>
                                        <strong>Bebas Antre (Unlimited Device):</strong> Ratusan
                                        tamu bisa foto serentak.
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <CheckCircle2 class="mt-0.5 h-[22px] w-[22px] shrink-0 text-emerald-600" />
                                    <span>
                                        <strong>Semua Tamu Dapat Softfile HD:</strong> Hasil
                                        photostrip berbingkai langsung tersimpan di galeri dan bisa
                                        diunduh.
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <CheckCircle2 class="mt-0.5 h-[22px] w-[22px] shrink-0 text-emerald-600" />
                                    <span>
                                        <strong>Multi-Media Momen:</strong> Bukan cuma foto, tamu
                                        juga bisa titip <em>Video ucapan</em>,
                                        <em>Voice Note</em>, dan doa tertulis.
                                    </span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <CheckCircle2 class="mt-0.5 h-[22px] w-[22px] shrink-0 text-emerald-600" />
                                    <span>
                                        <strong>Galeri Real-Time &amp; Moderasi Penuh:</strong>
                                        Atur galeri Publik/Privat, sembunyikan kiriman, dan unduh
                                        semua dalam 1 ZIP.
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-8">
                            <a class="coral-gradient flex w-full items-center justify-center gap-2 rounded-full py-3.5 text-center text-sm font-bold text-white shadow-md transition-transform hover:scale-[1.02]"
                                href="#paket">
                                <span>Pilih Paket Photobooth Acaramu</span>
                                <ArrowRight class="h-[18px] w-[18px]" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. SECTION: CARA KERJA DALAM 3 LANGKAH MUDAH -->
        <section id="cara-kerja" class="bg-surface py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 sm:mb-16 max-w-2xl text-center">
                    <span
                        class="rounded-full bg-primary-container/20 px-4 py-1.5 text-xs font-bold tracking-wider text-primary uppercase">
                        Sederhana &amp; Cepat
                    </span>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-on-surface sm:text-4xl">
                        Cara Kerja Semudah <span class="whitespace-nowrap">1-2-3</span>
                    </h2>
                    <p class="mt-2 text-sm sm:text-base text-on-surface-variant">
                        Tidak perlu alat khusus atau instruksi rumit. Siap dipakai dalam hitungan
                        menit!
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <!-- Step 1 Card -->
                    <div
                        class="group relative flex flex-col items-center rounded-3xl border border-outline-variant/60 bg-white p-5 sm:p-7 text-center shadow-xs transition-all hover:-translate-y-1.5">
                        <span
                            class="coral-gradient absolute -top-3.5 rounded-full px-4 py-1 text-xs font-extrabold text-white shadow-sm">
                            LANGKAH 01
                        </span>
                        <div
                            class="my-4 flex h-40 sm:h-44 w-full items-center justify-center rounded-2xl border border-outline-variant/40 bg-gradient-to-br from-rose-50 to-orange-50 p-3 sm:p-4">
                            <div
                                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-2.5 sm:p-3 shadow-md">
                                <div
                                    class="flex h-14 w-14 sm:h-16 sm:w-16 flex-col justify-between rounded-lg bg-slate-900 p-1.5">
                                    <div class="flex justify-between">
                                        <div class="h-2.5 w-2.5 sm:h-3 sm:w-3 bg-white"></div>
                                        <div class="h-2.5 w-2.5 sm:h-3 sm:w-3 bg-white"></div>
                                    </div>
                                    <div class="flex justify-between">
                                        <div class="h-2.5 w-2.5 sm:h-3 sm:w-3 bg-white"></div>
                                        <div class="h-1.5 w-1.5 bg-white"></div>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <p class="text-[11px] font-black text-primary uppercase">
                                        Scan untuk Foto
                                    </p>
                                    <p class="text-[10px] font-medium text-slate-500">
                                        Stand Meja Tamu
                                    </p>
                                    <span
                                        class="mt-1 inline-block rounded bg-emerald-100 px-1.5 py-0.5 text-[8px] font-bold text-emerald-800">
                                        QR Siap Cetak
                                    </span>
                                </div>
                            </div>
                        </div>
                        <h3 class="mb-2 text-lg sm:text-xl font-bold text-on-surface">
                            1. Pasang QR di Meja / Undangan
                        </h3>
                        <p class="text-xs sm:text-sm leading-relaxed text-on-surface-variant">
                            Kamu dapat barcode QR dan tautan web khusus acaramu. Cukup cetak di
                            kartu meja tamu, standing acrylic, atau bagikan di undangan digital.
                        </p>
                    </div>

                    <!-- Step 2 Card -->
                    <div
                        class="group relative flex flex-col items-center rounded-3xl border border-outline-variant/60 bg-white p-5 sm:p-7 text-center shadow-xs transition-all hover:-translate-y-1.5">
                        <span
                            class="coral-gradient absolute -top-3.5 rounded-full px-4 py-1 text-xs font-extrabold text-white shadow-sm">
                            LANGKAH 02
                        </span>
                        <div
                            class="my-4 flex h-40 sm:h-44 w-full items-center justify-center rounded-2xl border border-outline-variant/40 bg-gradient-to-br from-orange-50 to-amber-50 p-3 sm:p-4">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-28 w-18 sm:h-32 sm:w-20 flex-col justify-between rounded-xl border border-slate-700 bg-slate-900 p-1 shadow-lg">
                                    <div class="relative h-18 sm:h-20 w-full overflow-hidden rounded bg-rose-200">
                                        <img alt="Selfie tamu" class="h-full w-full object-cover"
                                            src="/images/stitch/img-09.jpg" />
                                        <div
                                            class="absolute right-1 bottom-1 rounded bg-primary px-1 text-[7px] font-bold text-white">
                                            FRAME
                                        </div>
                                    </div>
                                    <div class="coral-gradient mx-auto my-1 h-4 w-4 sm:h-5 sm:w-5 rounded-full"></div>
                                </div>
                                <div class="flex flex-col gap-1 text-left text-xs font-bold text-on-surface">
                                    <span
                                        class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-0.5 text-[10px] text-emerald-700">
                                        <Sparkles class="h-3 w-3" />
                                        <span>Tanpa Unduh App</span>
                                    </span>
                                    <span class="text-[11px] text-slate-600">
                                        Buka Kamera &gt; Pose &gt; Jadi!
                                    </span>
                                </div>
                            </div>
                        </div>
                        <h3 class="mb-2 text-lg sm:text-xl font-bold text-on-surface">
                            2. Tamu Scan &amp; Foto Langsung
                        </h3>
                        <p class="text-xs sm:text-sm leading-relaxed text-on-surface-variant">
                            Begitu QR discan kamera HP, halaman photobooth langsung terbuka. Tamu
                            bebas berpose dengan template bingkai estetik acaramu.
                        </p>
                    </div>

                    <!-- Step 3 Card -->
                    <div
                        class="group relative flex flex-col items-center rounded-3xl border border-outline-variant/60 bg-white p-5 sm:p-7 text-center shadow-xs transition-all hover:-translate-y-1.5">
                        <span
                            class="coral-gradient absolute -top-3.5 rounded-full px-4 py-1 text-xs font-extrabold text-white shadow-sm">
                            LANGKAH 03
                        </span>
                        <div
                            class="my-4 flex h-40 sm:h-44 w-full items-center justify-center rounded-2xl border border-outline-variant/40 bg-gradient-to-br from-rose-50 to-purple-50 p-3">
                            <div
                                class="flex w-full flex-col gap-1.5 rounded-xl border border-slate-800 bg-slate-900 p-2.5 sm:p-3 text-white shadow-lg">
                                <div class="flex items-center justify-between text-[9px] text-slate-400">
                                    <span class="flex items-center gap-1 font-bold text-emerald-400">
                                        <span class="h-1.5 w-1.5 animate-ping rounded-full bg-emerald-400"></span>
                                        LIVE ALBUM
                                    </span>
                                    <span>Galeri Realtime</span>
                                </div>
                                <div class="grid grid-cols-3 gap-1.5">
                                    <div class="aspect-square overflow-hidden rounded bg-slate-700">
                                        <img alt="Foto tamu" class="h-full w-full object-cover"
                                            src="/images/stitch/img-10.jpg" />
                                    </div>
                                    <div class="aspect-square overflow-hidden rounded bg-slate-700">
                                        <img alt="Foto tamu" class="h-full w-full object-cover"
                                            src="/images/stitch/img-11.jpg" />
                                    </div>
                                    <div class="aspect-square overflow-hidden rounded bg-slate-700">
                                        <img alt="Foto tamu" class="h-full w-full object-cover"
                                            src="/images/stitch/img-12.jpg" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h3 class="mb-2 text-lg sm:text-xl font-bold text-on-surface">
                            3. Masuk ke Galeri &amp; Unduh ZIP
                        </h3>
                        <p class="text-xs sm:text-sm leading-relaxed text-on-surface-variant">
                            Semua hasil foto, video, voice note, dan ucapan tamu langsung tersimpan
                            rapi di dashboard kamu secara otomatis dan siap diunduh format ZIP HD!
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. SECTION: CONTOH GAYA PHOTOSTRIP ASLI KOREA -->
        <section id="gaya-foto" class="border-t border-outline-variant/40 bg-white py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <span
                        class="rounded-full bg-primary-fixed/60 px-4 py-1.5 text-xs font-bold tracking-wider text-primary uppercase">
                        Aesthetic Photobox
                    </span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-on-surface sm:text-4xl">
                        Pilihan Gaya Photostrip ala Korea
                    </h2>
                    <p class="mt-2 text-base text-on-surface-variant">
                        Hasil foto langsung terpasang otomatis dalam bingkai estetik yang
                        menyesuaikan warna tema acaramu dan nama pengantin, tanpa perlu repot upload
                        file bingkai.
                    </p>
                </div>

                <!-- 4 Real Photostrip Visual Cards -->
                <div class="mx-auto grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Frame Style 1: Solo 1-Pose Classic -->
                    <div
                        class="flex flex-col items-center rounded-3xl border border-outline-variant/70 bg-surface-container-low p-5 transition-transform duration-300 hover:-translate-y-2">
                        <div
                            class="photostrip-shadow flex w-full flex-col items-center gap-2 rounded-2xl border border-slate-100 bg-white p-3">
                            <div class="relative aspect-[4/5] w-full overflow-hidden rounded-xl bg-rose-100">
                                <img alt="Solo 1 Pose" class="h-full w-full object-cover"
                                    src="/images/stitch/img-13.jpg" />
                                <span
                                    class="absolute top-2 right-2 flex items-center gap-0.5 rounded-full bg-white/90 px-2 py-0.5 text-[9px] font-black text-rose-600">
                                    <Heart class="h-2.5 w-2.5 fill-rose-600" />
                                    <span>SO CUTE</span>
                                </span>
                            </div>
                            <div class="py-1 text-center">
                                <p class="text-xs font-extrabold text-primary">ANISA &amp; RIZKY</p>
                                <p class="text-[9px] text-tertiary">2026 • Magetan</p>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-base font-bold text-on-surface">
                                1. Solo Cut (1 Pose)
                            </h3>
                            <p class="text-xs text-on-surface-variant">
                                Cocok untuk foto formal, polaroid look, dan selfie estetik.
                            </p>
                        </div>
                    </div>

                    <!-- Frame Style 2: Duo 2-Pose Vertikal -->
                    <div
                        class="flex flex-col items-center rounded-3xl border border-outline-variant/70 bg-surface-container-low p-5 transition-transform duration-300 hover:-translate-y-2">
                        <div
                            class="photostrip-shadow flex w-full flex-col items-center gap-2 rounded-2xl border border-rose-200/60 bg-[#FFF0ED] p-3">
                            <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-white">
                                <img alt="Duo Pose 1" class="h-full w-full object-cover"
                                    src="/images/stitch/img-14.jpg" />
                            </div>
                            <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-white">
                                <img alt="Duo Pose 2" class="h-full w-full object-cover"
                                    src="/images/stitch/img-15.jpg" />
                            </div>
                            <div class="py-1 text-center">
                                <p class="text-xs font-extrabold text-[#934a2a]">
                                    LOVE BIRDS MOMENT
                                </p>
                                <p class="text-[9px] text-tertiary">Our Wedding Day • 2026</p>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-base font-bold text-on-surface">
                                2. Duo Strip (2 Pose)
                            </h3>
                            <p class="text-xs text-on-surface-variant">
                                Favorit pasangan dan bestie untuk pose sebelum &amp; sesudah senyum.
                            </p>
                        </div>
                    </div>

                    <!-- Frame Style 3: 3-Cut Strip (Life Four Cuts Vibe) -->
                    <div
                        class="flex flex-col items-center rounded-3xl border border-outline-variant/70 bg-surface-container-low p-5 transition-transform duration-300 hover:-translate-y-2">
                        <div
                            class="photostrip-shadow flex w-full flex-col items-center gap-1.5 rounded-2xl border border-slate-700 bg-[#181d27] p-2.5 text-white">
                            <div class="aspect-[16/10] w-full overflow-hidden rounded-lg bg-slate-800">
                                <img alt="Cut 1" class="h-full w-full object-cover" src="/images/stitch/img-16.jpg" />
                            </div>
                            <div class="aspect-[16/10] w-full overflow-hidden rounded-lg bg-slate-800">
                                <img alt="Cut 2" class="h-full w-full object-cover" src="/images/stitch/img-17.jpg" />
                            </div>
                            <div class="aspect-[16/10] w-full overflow-hidden rounded-lg bg-slate-800">
                                <img alt="Cut 3" class="h-full w-full object-cover" src="/images/stitch/img-18.jpg" />
                            </div>
                            <div class="py-1 text-center">
                                <p class="text-[10px] font-black tracking-wider text-amber-300">
                                    THE WEDDING PARTY
                                </p>
                                <p class="text-[8px] text-slate-400">Trio Cut Strip Edition</p>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-base font-bold text-on-surface">
                                3. Trio Cut (3 Pose)
                            </h3>
                            <p class="text-xs text-on-surface-variant">
                                Gaya photobox Korea paling populer untuk grup teman &amp;
                                bridesmaid.
                            </p>
                        </div>
                    </div>

                    <!-- Frame Style 4: Quad 4-Grid Aesthetic -->
                    <div
                        class="flex flex-col items-center rounded-3xl border border-outline-variant/70 bg-surface-container-low p-5 transition-transform duration-300 hover:-translate-y-2">
                        <div
                            class="photostrip-shadow flex w-full flex-col items-center gap-1.5 rounded-2xl border border-blue-200 bg-[#EBF0FF] p-2.5">
                            <div class="grid w-full grid-cols-2 gap-1">
                                <div class="aspect-square overflow-hidden rounded-lg bg-white">
                                    <img alt="Grid 1" class="h-full w-full object-cover"
                                        src="/images/stitch/img-19.jpg" />
                                </div>
                                <div class="aspect-square overflow-hidden rounded-lg bg-white">
                                    <img alt="Grid 2" class="h-full w-full object-cover"
                                        src="/images/stitch/img-20.jpg" />
                                </div>
                                <div class="aspect-square overflow-hidden rounded-lg bg-white">
                                    <img alt="Grid 3" class="h-full w-full object-cover"
                                        src="/images/stitch/img-21.jpg" />
                                </div>
                                <div class="aspect-square overflow-hidden rounded-lg bg-white">
                                    <img alt="Grid 4" class="h-full w-full object-cover"
                                        src="/images/stitch/img-22.jpg" />
                                </div>
                            </div>
                            <div class="py-1 text-center">
                                <p class="text-[10px] font-extrabold text-blue-900 uppercase">
                                    CELEBRATE TOGETHER
                                </p>
                                <p class="text-[8px] text-blue-700">4-Grid Memories Collection</p>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-base font-bold text-on-surface">
                                4. Quad (4 Grid)
                            </h3>
                            <p class="text-xs text-on-surface-variant">
                                4 pose seru lengkap untuk rombongan keluarga atau geng sahabat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. SECTION: FITUR LENGKAP BENTO GRID -->
        <section id="fitur" class="bg-surface py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <span
                        class="rounded-full bg-primary-container/20 px-4 py-1.5 text-xs font-bold tracking-wider text-primary uppercase">
                        Lebih dari Sekadar Foto
                    </span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-on-surface sm:text-4xl">
                        Fitur Komplit untuk Acara Impianmu
                    </h2>
                    <p class="mt-2 text-base text-on-surface-variant">
                        Semua kenangan dari sudut pandang para tamu, terdokumentasi rapi tanpa
                        terlewat.
                    </p>
                </div>

                <!-- Bento Grid -->
                <div class="grid grid-cols-1 items-stretch gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div
                        class="group flex h-full flex-col justify-between rounded-3xl border border-outline-variant/60 bg-white p-7 shadow-xs transition-all hover:border-primary-container/80 hover:shadow-md">
                        <div>
                            <div
                                class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-primary transition-transform group-hover:scale-110">
                                <Camera class="h-[26px] w-[26px]" />
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-on-surface">
                                Photobooth Strip Instan
                            </h3>
                            <p class="text-sm leading-relaxed text-on-surface-variant">
                                Tamu foto selfie 1–4 pose dengan timer otomatis dan template frame
                                yang sudah disesuaikan dengan tema acaramu.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="group flex h-full flex-col justify-between rounded-3xl border border-outline-variant/60 bg-white p-7 shadow-xs transition-all hover:border-primary-container/80 hover:shadow-md">
                        <div>
                            <div
                                class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-600 transition-transform group-hover:scale-110">
                                <Video class="h-[26px] w-[26px]" />
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-on-surface">
                                Video Ucapan Singkat
                            </h3>
                            <p class="text-sm leading-relaxed text-on-surface-variant">
                                Bukan hanya gambar diam! Tamu bisa merekam video ucapan tulus 20–45
                                detik langsung dari kamera ponsel.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="group flex h-full flex-col justify-between rounded-3xl border border-outline-variant/60 bg-white p-7 shadow-xs transition-all hover:border-primary-container/80 hover:shadow-md">
                        <div>
                            <div
                                class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 transition-transform group-hover:scale-110">
                                <Mic class="h-[26px] w-[26px]" />
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-on-surface">
                                Pesan Suara (Voice Note)
                            </h3>
                            <p class="text-sm leading-relaxed text-on-surface-variant">
                                Rekaman suara tawa, kehangatan, dan ucapan emosional dari sahabat
                                yang bisa kamu dengarkan kembali selamanya.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div
                        class="group flex h-full flex-col justify-between rounded-3xl border border-outline-variant/60 bg-white p-7 shadow-xs transition-all hover:border-primary-container/80 hover:shadow-md">
                        <div>
                            <div
                                class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-100 text-purple-700 transition-transform group-hover:scale-110">
                                <ShieldCheck class="h-[26px] w-[26px]" />
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-on-surface">
                                Galeri Live &amp; Moderasi Tamu
                            </h3>
                            <p class="text-sm leading-relaxed text-on-surface-variant">
                                Pantau foto dan ucapan tamu secara real-time. Kamu bebas mengatur
                                privasi galeri (Publik/Privat) dan memoderasi kiriman dari dashboard.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div
                        class="group flex h-full flex-col justify-between rounded-3xl border border-outline-variant/60 bg-white p-7 shadow-xs transition-all hover:border-primary-container/80 hover:shadow-md">
                        <div>
                            <div
                                class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 transition-transform group-hover:scale-110">
                                <CloudDownload class="h-[26px] w-[26px]" />
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-on-surface">
                                Unduh Semua File (ZIP HD)
                            </h3>
                            <p class="text-sm leading-relaxed text-on-surface-variant">
                                Download seluruh foto berbingkai WebP HD, video ucapan, rekaman audio,
                                serta kumpulan ucapan teks dalam satu klik arsip.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div
                        class="group flex h-full flex-col justify-between rounded-3xl border border-outline-variant/60 bg-white p-7 shadow-xs transition-all hover:border-primary-container/80 hover:shadow-md">
                        <div>
                            <div
                                class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 transition-transform group-hover:scale-110">
                                <Palette class="h-[26px] w-[26px]" />
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-on-surface">
                                Bingkai Sesuai Tema Acara
                            </h3>
                            <p class="text-sm leading-relaxed text-on-surface-variant">
                                Warna bingkai foto otomatis mengikuti tema dekorasi acaramu secara
                                elegan, tanpa perlu repot upload file bingkai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. SECTION: PAKET HARGA -->
        <section id="paket" class="border-t border-outline-variant/40 bg-white py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <span
                        class="rounded-full bg-emerald-100 px-4 py-1.5 text-xs font-bold tracking-wider text-emerald-800 uppercase">
                        Harga Transparan • Sekali Bayar
                    </span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-on-surface sm:text-4xl">
                        Pilih Paket Photobooth Digital
                    </h2>
                    <p class="mt-2 text-base text-on-surface-variant">
                        Hemat hingga 90% dibanding sewa mesin fotobooth konvensional jutaan rupiah!
                    </p>
                </div>

                <!-- 3 Pricing Tiers Grid -->
                <div class="mx-auto grid max-w-6xl grid-cols-1 items-stretch gap-8 lg:grid-cols-3">
                    <!-- Plan 1: BASIC -->
                    <div
                        class="flex flex-col justify-between rounded-3xl border border-outline-variant/70 bg-surface-container-low p-8 transition-all hover:shadow-lg">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold text-on-surface">Basic</h3>
                                <span
                                    class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    Untuk Acara Intim
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-on-surface-variant">
                                Semua fitur inti digital photobooth, praktis siap pakai di hari-H.
                            </p>
                            <div class="mt-6 flex items-baseline gap-1">
                                <span class="text-4xl font-black text-on-surface">
                                    {{ packagePrices.basic }}
                                </span>
                                <span class="text-xs font-medium text-on-surface-variant">
                                    / acara
                                </span>
                            </div>
                            <div class="my-6 border-t border-outline-variant/40"></div>
                            <ul class="space-y-3.5 text-sm text-on-surface">
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Halaman photobooth dengan nama &amp; tanggal acara</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>QR Code &amp; tautan acara custom</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Unlimited tamu yang foto</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Galeri real-time &amp; moderasi kiriman</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>1 pilihan layout foto (1 pose berbingkai tema)</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Kiriman voice note &amp; ucapan tertulis</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Galeri aktif {{ packagePrices.basicDays }} hari</span>
                                </li>
                            </ul>
                        </div>
                        <Link :href="$page.props.auth.user ? dashboard() : register()"
                            class="mt-8 w-full rounded-full bg-inverse-surface py-3.5 text-center text-sm font-bold text-white transition-opacity hover:opacity-90">
                            Pilih Basic
                        </Link>
                    </div>

                    <!-- Plan 2: PLUS (PALING LARIS / HERO TIER) -->
                    <div
                        class="relative flex transform flex-col justify-between rounded-3xl border-2 border-primary-container bg-[#181d27] p-8 text-white shadow-2xl lg:-translate-y-4">
                        <div
                            class="coral-gradient absolute -top-3.5 left-1/2 flex -translate-x-1/2 items-center gap-1 rounded-full px-5 py-1 text-xs font-black tracking-wider whitespace-nowrap text-white uppercase shadow-md">
                            <Flame class="h-3.5 w-3.5" />
                            <span>PALING BANYAK DIPILIH</span>
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-black text-white">Plus</h3>
                                <span
                                    class="rounded-full border border-primary-container/30 bg-primary-container/20 px-2.5 py-1 text-xs font-bold text-primary-container">
                                    Rekomendasi Wedding
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-300">
                                Paling pas untuk resepsi pernikahan dengan tema warna sesuai
                                dekorasi.
                            </p>
                            <div class="mt-6 flex items-baseline gap-1">
                                <span class="text-4xl font-black text-white">
                                    {{ packagePrices.plus }}
                                </span>
                                <span class="text-xs font-medium text-slate-400">/ acara</span>
                            </div>
                            <div class="my-6 border-t border-slate-700"></div>
                            <ul class="space-y-3.5 text-sm text-slate-200">
                                <li class="flex items-start gap-2.5">
                                    <CheckCircle2 class="mt-0.5 h-[18px] w-[18px] shrink-0 text-primary-container" />
                                    <span><strong>Semua fitur di paket Basic</strong></span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <CheckCircle2 class="mt-0.5 h-[18px] w-[18px] shrink-0 text-primary-container" />
                                    <span class="font-bold text-white">
                                        2 pilihan layout foto (1–2 pose berbingkai)
                                    </span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <CheckCircle2 class="mt-0.5 h-[18px] w-[18px] shrink-0 text-primary-container" />
                                    <span class="font-semibold text-primary-container">
                                        Tema warna custom (bingkai otomatis sesuai tema)
                                    </span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <CheckCircle2 class="mt-0.5 h-[18px] w-[18px] shrink-0 text-primary-container" />
                                    <span>Kiriman video ucapan tamu (maks 20 detik)</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <CheckCircle2 class="mt-0.5 h-[18px] w-[18px] shrink-0 text-primary-container" />
                                    <span>Unduh semua file sekaligus (ZIP HD)</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <CheckCircle2 class="mt-0.5 h-[18px] w-[18px] shrink-0 text-primary-container" />
                                    <span>Galeri aktif {{ packagePrices.plusDays }} hari</span>
                                </li>
                            </ul>
                        </div>
                        <Link :href="$page.props.auth.user ? dashboard() : register()"
                            class="coral-gradient mt-8 w-full rounded-full py-4 text-center text-sm font-extrabold text-white shadow-lg shadow-primary-container/30 transition-transform hover:scale-[1.02]">
                            Pilih Paket Plus (Rekomendasi)
                        </Link>
                    </div>

                    <!-- Plan 3: FULL -->
                    <div
                        class="flex flex-col justify-between rounded-3xl border border-outline-variant/70 bg-surface-container-low p-8 transition-all hover:shadow-lg">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold text-on-surface">Full Complete</h3>
                                <span
                                    class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    All-Inclusive
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-on-surface-variant">
                                Paling lengkap tanpa batas untuk acara besar dan festival kantor.
                            </p>
                            <div class="mt-6 flex items-baseline gap-1">
                                <span class="text-4xl font-black text-on-surface">
                                    {{ packagePrices.full }}
                                </span>
                                <span class="text-xs font-medium text-on-surface-variant">
                                    / acara
                                </span>
                            </div>
                            <div class="my-6 border-t border-outline-variant/40"></div>
                            <ul class="space-y-3.5 text-sm text-on-surface">
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span><strong>Semua fitur di paket Plus</strong></span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span class="font-bold">
                                        Lengkap 4 pilihan layout foto (1–4 pose: Solo, Duo, Trio, Quad)
                                    </span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Kiriman video ucapan panjang (maks 45 detik)</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Tema warna custom &amp; bingkai otomatis di semua layout</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Unduh semua file sekaligus (ZIP HD)</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <Check class="mt-0.5 h-[18px] w-[18px] shrink-0 text-emerald-600" />
                                    <span>Galeri aktif {{ packagePrices.fullDays }} hari</span>
                                </li>
                            </ul>
                        </div>
                        <Link :href="$page.props.auth.user ? dashboard() : register()"
                            class="mt-8 w-full rounded-full bg-inverse-surface py-3.5 text-center text-sm font-bold text-white transition-opacity hover:opacity-90">
                            Pilih Full
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. SECTION: FAQ (PERTANYAAN UMUM) -->
        <section id="faq" class="bg-surface py-16 sm:py-24">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-14 max-w-xl text-center">
                    <span
                        class="rounded-full bg-primary-container/20 px-4 py-1.5 text-xs font-bold tracking-wider text-primary uppercase">
                        Pertanyaan Umum
                    </span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-on-surface sm:text-4xl">
                        Masih Ragu? Cek di Sini
                    </h2>
                    <p class="mt-2 text-base text-on-surface-variant">
                        Semua hal teknis yang sering ditanyakan oleh calon pengantin dan event
                        organizer.
                    </p>
                </div>

                <div class="space-y-4">
                    <div v-for="(faq, idx) in faqs" :key="faq.question"
                        class="rounded-2xl border border-outline-variant/60 bg-white p-5 shadow-xs">
                        <button type="button"
                            class="flex w-full items-center justify-between text-left text-base font-bold text-on-surface focus:outline-none sm:text-lg"
                            :aria-expanded="openFaqIndex === idx" @click="toggleFaq(idx)">
                            <span class="pr-4">{{ faq.question }}</span>
                            <ChevronDown class="h-5 w-5 shrink-0 text-primary transition-transform duration-200"
                                :class="{ 'rotate-180': openFaqIndex === idx }" />
                        </button>
                        <div v-show="openFaqIndex === idx"
                            class="mt-3 border-t border-outline-variant/30 pt-3 text-sm leading-relaxed text-on-surface-variant">
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. BOTTOM CALL TO ACTION BANNER -->
        <section class="bg-surface px-4 py-12 sm:px-6 sm:py-20 lg:px-8">
            <div
                class="relative mx-auto flex max-w-5xl flex-col items-center gap-6 overflow-hidden rounded-[36px] bg-[#181d27] p-8 text-center text-white shadow-2xl sm:p-14">
                <div
                    class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full bg-primary-container/25 blur-3xl">
                </div>
                <div
                    class="pointer-events-none absolute -right-24 -bottom-24 h-80 w-80 rounded-full bg-secondary-container/25 blur-3xl">
                </div>

                <div
                    class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-bold text-white">
                    <Rocket class="h-3.5 w-3.5" />
                    <span>Setup Kilat Hanya 5 Menit</span>
                </div>
                <h2 class="max-w-2xl text-3xl leading-tight font-black sm:text-5xl">
                    Siap Bikin Acaramu Penuh Momen Seru &amp; Tak Terlupakan?
                </h2>
                <p class="max-w-xl text-base text-slate-300 sm:text-lg">
                    Gak perlu sewa mesin fotobox mahal jutaan rupiah. Biarkan para tamu
                    mengabadikan keceriaan mereka sendiri!
                </p>
                <div class="flex flex-col items-center gap-4 pt-2 sm:flex-row">
                    <Link :href="$page.props.auth.user ? dashboard() : register()"
                        class="coral-gradient inline-flex items-center justify-center gap-2.5 rounded-full px-8 py-4 text-base font-extrabold text-white shadow-xl shadow-primary-container/40 transition-transform hover:scale-105">
                        <span>Bikin Photobooth Acaramu Sekarang</span>
                        <ArrowRight class="h-[18px] w-[18px]" />
                    </Link>
                    <a href="#gaya-foto"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-white/20 bg-white/10 px-6 py-4 text-base font-bold text-white transition-all hover:bg-white/20">
                        <Smartphone class="h-5 w-5" />
                        <span>Lihat Gaya Photostrip</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-slate-800 bg-slate-950 text-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <!-- Top Footer Grid -->
                <div class="grid grid-cols-1 gap-10 md:grid-cols-12 md:gap-8">
                    <!-- Brand Column -->
                    <div class="flex flex-col gap-3 md:col-span-5">
                        <Link href="/" class="flex items-center gap-2">
                            <img src="/images/titip-momen-logo-web.png" alt="titip.moment"
                                class="h-8 w-auto object-contain brightness-0 invert" />
                        </Link>
                        <p class="max-w-sm text-xs leading-relaxed text-slate-400">
                            Platform photobooth digital modern untuk pernikahan, ulang tahun, dan
                            event tanpa sewa alat fisik mahal. Tamu tinggal scan QR langsung dari ponsel.
                        </p>
                        <div class="pt-1">
                            <a href="mailto:kontak@titipmoment.id"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900/90 px-3.5 py-1.5 text-xs font-semibold text-slate-200 transition-colors hover:border-primary-container hover:text-white">
                                <Mail class="h-3.5 w-3.5 text-primary-container" />
                                <span>kontak@titipmoment.id</span>
                            </a>
                        </div>
                    </div>

                    <!-- Navigation Links Columns (Clean 2 columns on mobile) -->
                    <div class="grid grid-cols-2 gap-6 sm:gap-8 md:col-span-7">
                        <!-- Column 1: Fitur & Acara -->
                        <div class="flex flex-col gap-3">
                            <p class="text-xs font-bold tracking-wider text-white uppercase">
                                Menu Acara
                            </p>
                            <nav class="flex flex-col gap-2.5 text-xs text-slate-400">
                                <a href="#cara-kerja" class="transition-colors hover:text-white"
                                    @click="scrollToSection('cara-kerja', $event)">
                                    Cara Kerja
                                </a>
                                <a href="#perbandingan" class="transition-colors hover:text-white"
                                    @click="scrollToSection('perbandingan', $event)">
                                    Bandingkan Biaya
                                </a>
                                <a href="#gaya-foto" class="transition-colors hover:text-white"
                                    @click="scrollToSection('gaya-foto', $event)">
                                    Pilihan Gaya Frame
                                </a>
                                <a href="#fitur" class="transition-colors hover:text-white"
                                    @click="scrollToSection('fitur', $event)">
                                    Fitur Tamu
                                </a>
                                <a href="#paket" class="transition-colors hover:text-white"
                                    @click="scrollToSection('paket', $event)">
                                    Harga Paket
                                </a>
                                <a href="#faq" class="transition-colors hover:text-white"
                                    @click="scrollToSection('faq', $event)">
                                    Tanya Jawab (FAQ)
                                </a>
                            </nav>
                        </div>

                        <!-- Column 2: Legalitas & Informasi -->
                        <div class="flex flex-col gap-3">
                            <p class="text-xs font-bold tracking-wider text-white uppercase">
                                Legal &amp; Info
                            </p>
                            <nav class="flex flex-col gap-2.5 text-xs text-slate-400">
                                <Link href="/terms" class="transition-colors hover:text-white">
                                    Ketentuan Layanan
                                </Link>
                                <Link href="/privacy" class="transition-colors hover:text-white">
                                    Kebijakan Privasi
                                </Link>
                                <a href="mailto:kontak@titipmoment.id" class="transition-colors hover:text-white">
                                    Hubungi Kami
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="my-8 h-px w-full bg-slate-800/80"></div>

                <!-- Bottom Footer Bar -->
                <div
                    class="flex flex-col items-center justify-between gap-3 text-center text-xs text-slate-500 sm:flex-row sm:text-left">
                    <p class="leading-relaxed">
                        © {{ new Date().getFullYear() }} titip.moment · Seluruh hak cipta dilindungi.
                    </p>
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <Heart class="h-3.5 w-3.5 fill-rose-500 text-rose-500" />
                        <span>Abadikan momen spesialmu dengan mudah.</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
