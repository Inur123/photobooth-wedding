<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Lock, Mail, ShieldCheck } from '@lucide/vue';
import { dashboard, login, register } from '@/routes';

const sections = [
    {
        number: '01',
        title: 'Data yang Kami Kumpulkan',
        paragraphs: [
            'Kami hanya mengumpulkan informasi yang dibutuhkan secara langsung untuk menjalankan sistem photobooth digital dan buku tamu acara Anda:',
        ],
        bullets: [
            'Data Akun Penyelenggara (Client): Nama lengkap, alamat email, status verifikasi email, dan kata sandi yang dienkripsi satu arah (hashed).',
            'Data Konfigurasi Acara: Judul acara, tanggal acara, lokasi, kutipan/deskripsi, foto sampul, serta palet warna tema yang dipilih oleh Client.',
            'Data Kiriman Tamu (Guest Contributions): Nama panggilan tamu (opsional, dapat dikosongkan sebagai anonim), pilihan visibilitas (Publik/Privat), berkas foto strip, rekaman video singkat, rekaman pesan suara, serta teks ucapan yang dikirimkan melalui halaman acara.',
        ],
    },
    {
        number: '02',
        title: 'Akses Perangkat Kamera & Mikrofon Tamu',
        paragraphs: [
            'Saat tamu membuka mode kamera photobooth, video, atau pesan suara di browser ponsel, sistem meminta izin akses kamera dan/atau mikrofon standar melalui Web API bawaan browser (getUserMedia).',
            'titip.moment tidak pernah merekam kamera atau mikrofon di latar belakang. Pengambilan gambar atau rekaman suara hanya berjalan ketika tamu menekan tombol rekam/ambil foto secara sadar, dan berkas hanya diunggah ke server setelah tamu menekan tombol "Simpan ke Galeri".',
        ],
    },
    {
        number: '03',
        title: 'Pemrosesan Media & Penyimpanan Cloud (Cloudflare R2)',
        paragraphs: [
            'Untuk menjaga kecepatan pemuatan galeri dan efisiensi kuota data tamu, foto yang diunggah diproses melalui antrean server (queue job) untuk digabungkan dengan bingkai acara dan dikonversi ke format WebP teroptimasi.',
            'Seluruh berkas media tamu disimpan di penyimpanan cloud Cloudflare R2 kawasan Asia Pasifik dan disajikan melalui domain media resmi (media.titipmoment.id) yang dilindungi kebijakan Content Security Policy (CSP).',
        ],
    },
    {
        number: '04',
        title: 'Pengaturan Visibilitas Galeri (Publik & Privat)',
        paragraphs: [
            'Privasi kenangan acara sepenuhnya berada di bawah kendali penyelenggara acara (Client) dan tamu yang mengirim momen:',
        ],
        bullets: [
            'Pengaturan Tamu: Saat mengirim momen, tamu dapat memilih apakah kirimannya tampil di Galeri Publik atau bersifat Privat (hanya untuk pemilik acara).',
            'Pengaturan Acara (Client): Melalui Dashboard, Client dapat menyetel visibilitas galeri acara menjadi Publik atau Privat, serta menyembunyikan atau menghapus kiriman apa pun setiap saat.',
        ],
    },
    {
        number: '05',
        title: 'Pengiriman Email Transaksional (SMTP)',
        paragraphs: [
            'Alamat email Client hanya digunakan untuk keperluan autentikasi dan keamanan akun, meliputi pengiriman tautan verifikasi email saat pendaftaran serta tautan pengaturan ulang kata sandi (Forgot Password). Kami tidak menjual, menyewakan, atau membagikan daftar email pengguna kepada pihak ketiga mana pun untuk tujuan iklan.',
        ],
    },
    {
        number: '06',
        title: 'Hak Penghapusan Data & Masa Retensi',
        paragraphs: [
            'Client dapat menghapus kiriman tamu satu per satu atau menghapus seluruh halaman acara beserta berkas medianya kapan saja melalui Dashboard.',
            'Berkas media acara akan disimpan sesuai masa aktif paket (30, 60, atau 90 hari) agar Client memiliki waktu yang cukup untuk mengunduh arsip (.zip), dan selanjutnya dapat dibersihkan secara permanen dari penyimpanan cloud kami.',
        ],
    },
];
</script>

<template>
    <Head title="Kebijakan Privasi — titip.moment" />

    <div
        class="sunset-mesh relative min-h-screen overflow-x-hidden font-sans text-on-surface antialiased selection:bg-primary-container selection:text-white"
    >
        <!-- TOP NAVBAR -->
        <header
            class="sticky top-0 z-50 border-b border-outline-variant/50 bg-white/85 backdrop-blur-md transition-all"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <Link href="/" class="group flex items-center gap-2">
                    <img
                        src="/images/titip-momen-logo-web.png"
                        alt="titip.moment"
                        class="h-8 w-auto object-contain transition-transform group-hover:scale-105 sm:h-9"
                    />
                </Link>

                <div class="flex items-center gap-3">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 rounded-full border border-outline-variant bg-white px-4 py-2 text-xs font-bold text-on-surface shadow-xs transition-all hover:bg-surface-container-low"
                    >
                        <ArrowLeft class="h-3.5 w-3.5 text-primary" />
                        <span>Kembali ke Beranda</span>
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="coral-gradient hidden items-center gap-1.5 rounded-full px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:scale-105 sm:inline-flex"
                    >
                        Buka Dashboard
                    </Link>
                    <Link
                        v-else
                        :href="register()"
                        class="coral-gradient hidden items-center gap-1.5 rounded-full px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:scale-105 sm:inline-flex"
                    >
                        Bikin Photobooth
                    </Link>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main class="mx-auto max-w-4xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <!-- Hero Header Card -->
            <div
                class="glass-card rounded-3xl border border-outline-variant/60 p-8 sm:p-10"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-primary-fixed/70 px-4 py-1.5 text-xs font-bold tracking-wider text-primary uppercase"
                    >
                        <Lock class="h-3.5 w-3.5" />
                        Perlindungan Data &amp; Privasi
                    </span>

                    <!-- Legal Switcher Pill -->
                    <div
                        class="inline-flex items-center rounded-full border border-outline-variant/80 bg-surface-container-low p-1 text-xs font-bold"
                    >
                        <Link
                            href="/terms"
                            class="rounded-full px-4 py-1.5 text-on-surface-variant transition-colors hover:text-primary"
                        >
                            Ketentuan Penggunaan
                        </Link>
                        <Link
                            href="/privacy"
                            class="coral-gradient rounded-full px-4 py-1.5 text-white shadow-xs"
                        >
                            Kebijakan Privasi
                        </Link>
                    </div>
                </div>

                <h1
                    class="mt-6 text-3xl font-black tracking-tight text-on-surface sm:text-4xl lg:text-5xl"
                >
                    Kebijakan Privasi
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-on-surface-variant sm:text-base">
                    Kami menjaga kerahasiaan data akun penyelenggara acara serta seluruh foto,
                    video, rekaman suara, dan ucapan yang dititipkan tamu di
                    <strong>titip.moment</strong>.
                </p>
                <div
                    class="mt-5 inline-flex items-center gap-2 rounded-full border border-outline-variant/60 bg-white px-3.5 py-1.5 text-xs font-semibold text-on-surface-variant"
                >
                    <CheckCircle2 class="h-4 w-4 text-emerald-600" />
                    Terakhir diperbarui: 15 November 2026
                </div>
            </div>

            <!-- Clauses List -->
            <div class="mt-8 space-y-6">
                <section
                    v-for="sec in sections"
                    :key="sec.number"
                    class="rounded-3xl border border-outline-variant/60 bg-white p-7 shadow-xs sm:p-8"
                >
                    <div class="flex items-start gap-4">
                        <span
                            class="coral-gradient flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-xs font-black text-white shadow-sm"
                        >
                            {{ sec.number }}
                        </span>
                        <div class="flex-1">
                            <h2 class="text-xl font-extrabold text-on-surface">
                                {{ sec.title }}
                            </h2>
                            <div
                                class="mt-3 space-y-3 text-sm leading-relaxed text-on-surface-variant sm:text-base"
                            >
                                <p v-for="p in sec.paragraphs" :key="p">
                                    {{ p }}
                                </p>
                            </div>
                            <ul
                                v-if="sec.bullets"
                                class="mt-4 space-y-2.5 rounded-2xl border border-outline-variant/50 bg-surface-container-low/70 p-4 text-sm text-on-surface"
                            >
                                <li
                                    v-for="bullet in sec.bullets"
                                    :key="bullet"
                                    class="flex items-start gap-2.5"
                                >
                                    <CheckCircle2
                                        class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"
                                    />
                                    <span>{{ bullet }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Contact Support Card -->
            <div
                class="mt-10 flex flex-col items-start justify-between gap-6 rounded-3xl bg-[#181d27] p-8 text-white shadow-xl sm:flex-row sm:items-center"
            >
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-primary-container uppercase">
                        <ShieldCheck class="h-4 w-4" />
                        Privasi &amp; Penghapusan Data
                    </div>
                    <h3 class="mt-1 text-xl font-black text-white">
                        Butuh bantuan terkait privasi galeri atau penghapusan data?
                    </h3>
                    <p class="mt-1 text-sm text-slate-300">
                        Hubungi tim kami kapan saja melalui email resmi:
                        <a
                            href="mailto:kontak@titipmoment.id"
                            class="font-bold text-primary-container underline hover:text-white"
                        >
                            kontak@titipmoment.id
                        </a>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="mailto:kontak@titipmoment.id"
                        class="coral-gradient inline-flex items-center gap-2 rounded-full px-5 py-3 text-xs font-extrabold text-white shadow-lg"
                    >
                        <Mail class="h-4 w-4" />
                        Hubungi Kami
                    </a>
                    <Link
                        href="/terms"
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-5 py-3 text-xs font-bold text-white transition-colors hover:bg-white/20"
                    >
                        Baca Ketentuan Penggunaan
                    </Link>
                </div>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="border-t border-slate-800 bg-slate-950 text-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2.5">
                        <img
                            src="/images/titip-momen-logo-web.png"
                            alt="titip.moment"
                            class="h-7 w-auto object-contain brightness-0 invert"
                        />
                    </div>
                    <div class="flex flex-wrap gap-6 text-xs font-semibold text-slate-300">
                        <Link href="/" class="hover:text-primary-container">Beranda</Link>
                        <Link href="/terms" class="hover:text-primary-container">
                            Ketentuan Penggunaan
                        </Link>
                        <Link href="/privacy" class="text-primary-container">
                            Kebijakan Privasi
                        </Link>
                        <Link :href="login()" class="hover:text-primary-container">Masuk</Link>
                        <a
                            href="mailto:kontak@titipmoment.id"
                            class="hover:text-primary-container"
                        >
                            kontak@titipmoment.id
                        </a>
                    </div>
                </div>
                <div class="h-px w-full bg-slate-800"></div>
                <div class="flex flex-col items-center justify-between gap-3 text-center text-xs text-slate-500 sm:flex-row sm:text-left">
                    <p class="leading-relaxed">
                        © {{ new Date().getFullYear() }} titip.moment · Seluruh hak cipta dilindungi.
                    </p>
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <Mail class="h-3.5 w-3.5 text-primary-container" />
                        <span>kontak@titipmoment.id</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
