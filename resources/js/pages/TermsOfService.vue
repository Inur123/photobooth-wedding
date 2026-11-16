<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    FileText,
    Mail,
    ShieldCheck,
} from '@lucide/vue';
import { dashboard, login, register } from '@/routes';

const sections = [
    {
        number: '01',
        title: 'Ruang Lingkup Layanan titip.moment',
        paragraphs: [
            'titip.moment adalah platform photobooth digital dan buku tamu interaktif mandiri (self-service) berbasis web. Kami menyediakan sistem perangkat lunak bagi penyelenggara acara (Client) untuk membuat halaman acara digital yang diakses tamu melalui pemindaian kode QR atau tautan web unik tanpa mengunduh aplikasi tambahan.',
            'Layanan ini 100% berbasis web digital. titip.moment tidak menyewakan perangkat kamera fisik, mesin photobox cetak, kertas foto, maupun personel/kru lapangan di lokasi acara.',
        ],
    },
    {
        number: '02',
        title: 'Akun Penyelenggara (Client) & Verifikasi Email',
        paragraphs: [
            'Pendaftaran akun di titip.moment hanya diperuntukkan bagi penyelenggara acara (pengantin, panitia acara, atau event organizer). Tamu undangan tidak memerlukan akun untuk mengirim foto, video, pesan suara, maupun ucapan.',
            'Setiap Client wajib mendaftarkan alamat email aktif dan menyelesaikan proses verifikasi email melalui tautan resmi yang dikirim sistem sebelum dapat mengakses Dashboard dan membuat halaman acara.',
            'Client bertanggung jawab penuh menjaga kerahasiaan kata sandi akun serta seluruh aktivitas pengelolaan acara yang dilakukan melalui akun tersebut.',
        ],
    },
    {
        number: '03',
        title: 'Kapasitas Paket, Durasi Galeri, dan Fitur Acara',
        paragraphs: [
            'Fitur yang tersedia pada setiap acara mengikuti paket layanan yang dipilih oleh Client (Basic, Plus, atau Full):',
        ],
        bullets: [
            'Paket Basic: Mendukung 1 tata letak bingkai foto (Solo Cut 1 pose), rekaman pesan suara, ucapan tertulis, serta masa aktif galeri 30 hari kalender.',
            'Paket Plus: Mendukung 2 tata letak bingkai foto (Solo & Duo Strip), kustomisasi tema warna acara (bingkai otomatis menyesuaikan tema), kiriman video tamu hingga 20 detik, unduhan arsip penuh (.zip), serta masa aktif galeri 60 hari kalender.',
            'Paket Full: Mendukung 4 tata letak foto (1 hingga 4 pose: Solo, Duo, Trio, Quad), kustomisasi tema warna acara (bingkai otomatis menyesuaikan tema), kiriman video tamu hingga 45 detik, unduhan arsip penuh (.zip), serta masa aktif galeri 90 hari kalender.',
        ],
    },
    {
        number: '04',
        title: 'Kepemilikan Konten & Tanggung Jawab Moderasi',
        paragraphs: [
            'Seluruh hak cipta atas foto, video, rekaman suara, dan pesan teks yang dikirimkan oleh tamu tetap menjadi milik masing-masing pemilik acara dan/atau tamu yang bersangkutan. Dengan mengunggah konten, pengguna memberikan izin teknis terbatas kepada titip.moment untuk mengolah, mengompresi (termasuk konversi ke format WebP), menyimpan di cloud storage, dan menampilkannya pada halaman galeri acara terkait.',
            'Client memiliki kendali penuh untuk mengatur visibilitas galeri (Publik atau Privat) serta memoderasi (menyembunyikan atau menghapus) kiriman tamu melalui menu Moderasi Kiriman di Dashboard. Konten yang melanggar hukum, mengandung unsur pornografi, perjudian, ujaran kebencian, atau pelanggaran hak cipta dilarang keras dan dapat dihapus oleh administrator sistem.',
        ],
    },
    {
        number: '05',
        title: 'Masa Retensi Data & Pengunduhan Arsip (.zip)',
        paragraphs: [
            'Galeri acara dan berkas media tamu akan tetap aktif sesuai masa simpan paket (30, 60, atau 90 hari sejak tanggal acara). Client sangat disarankan untuk mengunduh seluruh arsip kenangan (fitur Download ZIP pada paket Plus dan Full) sebelum masa aktif galeri berakhir.',
            'Setelah masa retensi berakhir atau apabila Client menghapus acara secara mandiri melalui Dashboard, berkas media dapat dihapus secara permanen dari ruang penyimpanan cloud kami.',
        ],
    },
    {
        number: '06',
        title: 'Ketersediaan Sistem & Batasan Tanggung Jawab',
        paragraphs: [
            'Kualitas tangkapan kamera, kejernihan mikrofon, serta kecepatan unggah saat acara berlangsung bergantung pada perangkat ponsel masing-masing tamu dan kondisi jaringan internet (seluler/Wi-Fi) di lokasi acara.',
            'titip.moment berupaya menjaga ketersediaan server dan penyimpanan cloud secara optimal, namun tidak bertanggung jawab atas gangguan konektivitas jaringan di lokasi venue atau pembatalan acara di luar kendali teknis platform.',
        ],
    },
];
</script>

<template>
    <Head title="Ketentuan Penggunaan — titip.moment" />

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
                        <FileText class="h-3.5 w-3.5" />
                        Dokumen Resmi Platform
                    </span>

                    <!-- Legal Switcher Pill -->
                    <div
                        class="inline-flex items-center rounded-full border border-outline-variant/80 bg-surface-container-low p-1 text-xs font-bold"
                    >
                        <Link
                            href="/terms"
                            class="coral-gradient rounded-full px-4 py-1.5 text-white shadow-xs"
                        >
                            Ketentuan Penggunaan
                        </Link>
                        <Link
                            href="/privacy"
                            class="rounded-full px-4 py-1.5 text-on-surface-variant transition-colors hover:text-primary"
                        >
                            Kebijakan Privasi
                        </Link>
                    </div>
                </div>

                <h1
                    class="mt-6 text-3xl font-black tracking-tight text-on-surface sm:text-4xl lg:text-5xl"
                >
                    Ketentuan Penggunaan
                </h1>
                <p
                    class="mt-3 max-w-2xl text-sm leading-relaxed text-on-surface-variant sm:text-base"
                >
                    Ketentuan ini mengatur penggunaan layanan photobooth digital
                    dan buku tamu interaktif <strong>titip.moment</strong> baik
                    bagi penyelenggara acara (Client) maupun tamu undangan.
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
                    <div
                        class="flex items-center gap-2 text-xs font-bold text-primary-container uppercase"
                    >
                        <ShieldCheck class="h-4 w-4" />
                        Pusat Bantuan Resmi
                    </div>
                    <h3 class="mt-1 text-xl font-black text-white">
                        Punya pertanyaan terkait ketentuan layanan kami?
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
                        href="/privacy"
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-5 py-3 text-xs font-bold text-white transition-colors hover:bg-white/20"
                    >
                        Baca Kebijakan Privasi
                    </Link>
                </div>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="border-t border-slate-800 bg-slate-950 text-white">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-12 sm:px-6 lg:px-8"
            >
                <div
                    class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center"
                >
                    <div class="flex items-center gap-2.5">
                        <img
                            src="/images/titip-momen-logo-web.png"
                            alt="titip.moment"
                            class="h-7 w-auto object-contain brightness-0 invert"
                        />
                    </div>
                    <div
                        class="flex flex-wrap gap-6 text-xs font-semibold text-slate-300"
                    >
                        <Link href="/" class="hover:text-primary-container"
                            >Beranda</Link
                        >
                        <Link href="/terms" class="text-primary-container">
                            Ketentuan Penggunaan
                        </Link>
                        <Link
                            href="/privacy"
                            class="hover:text-primary-container"
                        >
                            Kebijakan Privasi
                        </Link>
                        <Link
                            :href="login()"
                            class="hover:text-primary-container"
                            >Masuk</Link
                        >
                        <a
                            href="mailto:kontak@titipmoment.id"
                            class="hover:text-primary-container"
                        >
                            kontak@titipmoment.id
                        </a>
                    </div>
                </div>
                <div class="h-px w-full bg-slate-800"></div>
                <div
                    class="flex flex-col items-center justify-between gap-3 text-center text-xs text-slate-500 sm:flex-row sm:text-left"
                >
                    <p class="leading-relaxed">
                        © {{ new Date().getFullYear() }} titip.moment · Seluruh
                        hak cipta dilindungi.
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
