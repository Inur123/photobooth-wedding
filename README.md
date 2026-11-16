# Titip Moment 📸💍

> **Platform Photobooth Digital & Buku Tamu Interaktif Berbasis Realtime untuk Pernikahan dan Acara Spesial.**

[![CI](https://github.com/Inur123/photobooth-wedding/actions/workflows/tests.yml/badge.svg)](https://github.com/Inur123/photobooth-wedding/actions/workflows/tests.yml)
[![Latest Release](https://img.shields.io/github/v/release/Inur123/photobooth-wedding?color=blue&label=release)](https://github.com/Inur123/photobooth-wedding/releases)
[![PHP Version](https://img.shields.io/badge/PHP-8.4-777bb4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.5-4FC08D.svg?logo=vue.js&logoColor=white)](https://vuejs.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

---

## 🌟 Tentang Aplikasi

**Titip Moment** menghadirkan pengalaman buku tamu modern tanpa perlu mengunduh aplikasi. Tamu cukup memindai kode QR yang tersedia di meja resepsi untuk langsung mengabadikan foto, merekam video _reels_, menuliskan ucapan manis, maupun merekam pesan suara secara instan dari browser ponsel mereka.

Semua ucapan dan momen yang dibagikan akan langsung muncul secara _realtime_ di layar proyektor / panggung acara maupun pada ponsel tamu lainnya melalui koneksi WebSocket.

---

## ✨ Fitur Utama

- **📸 Album Kenangan**: Galeri foto bersama pengantin dengan bingkai digital kustom (digital frame) dan fitur download foto resolusi tinggi.
- **🎬 Reels Video (hingga 45 detik)**: Rekam ucapan video langsung dari kamera HP dengan tampilan feed bergaya Instagram Reels. Dilengkapi patch metadata EBML WebM agar progress bar durasi akurat dan tidak melompat.
- **🎙️ Pesan Suara (Voice Note)**: Rekaman audio personal hingga 60 detik dengan visual audio player.
- **💌 Catatan & Doa**: Ucapan teks dan doa restu yang tersusun rapi dengan infinite scroll.
- **⚡ Sinkronisasi Realtime (Laravel Reverb)**: Setiap unggahan baru dari tamu langsung tersiar ke layar display / proyektor tanpa perlu me-refresh halaman.
- **📅 Manajemen Status Acara Otomatis**:
    - `Draft`: Sebelum tanggal acara ditentukan.
    - `Active`: Buka kiriman otomatis pada hari-H acara.
    - `Closed`: Kunci pengiriman baru otomatis setelah hari-H lewat.
    - `Expired`: Penonaktifan galeri otomatis sesuai durasi paket.
    - Mendukung _override_ manual langsung dari dashboard admin.
- **🎛️ QR Code Generator**: Pembuatan kartu meja cetak berisi QR Code unik untuk setiap event.
- **☁️ Cloudflare R2 Storage**: Penyimpanan foto, audio, dan video berbasis S3-compatible yang cepat, aman, dan hemat biaya.
- **🛡️ Keamanan & Performa**: Dilengkapi Content Security Policy (CSP), proteksi WebSocket, validasi payload, dan optimasi gambar otomatis menggunakan WebP.

---

## 🛠️ Tech Stack

### Backend

- **Framework**: [Laravel 12](https://laravel.com) (PHP 8.4)
- **Realtime Server**: [Laravel Reverb](https://reverb.laravel.com/)
- **Media Processing**: [Intervention Image v3](https://image.intervention.io/) (auto-orient EXIF & WebP converter)
- **Cloud Storage**: Flysystem S3 (`league/flysystem-aws-s3-v3`) untuk Cloudflare R2
- **Testing & Quality**: PHPUnit 12, PHPStan / Larastan, Laravel Pint

### Frontend

- **Framework**: [Vue 3](https://vuejs.org/) (Composition API, `<script setup>`)
- **Routing & State**: [Inertia.js v2](https://inertiajs.com/)
- **Styling**: [Tailwind CSS v4](https://tailwindcss.com/)
- **Komponen UI**: [Reka UI](https://reka-ui.com/), [Lucide Vue](https://lucide.dev/)
- **Client Media**: MediaRecorder API, `fix-webm-duration`
- **Build Tool**: [Vite Plus](https://voidzero.dev/)

---

## 📋 Prasyarat Sistem

Pastikan environment lokal atau server Anda telah memenuhi syarat berikut:

- **PHP**: `>= 8.4` (dengan ekstensi `pdo_sqlite` / `pdo_mysql`, `gd`, `exif`, `mbstring`, `zip`)
- **Composer**: `>= 2.2`
- **Node.js**: `>= 20.x` atau `>= 22.x` (LTS) & NPM
- **Database**: SQLite (default dev/test) atau MySQL / PostgreSQL

---

## 🚀 Panduan Instalasi Lokal

### 1. Clone Repository

```bash
git clone https://github.com/Inur123/photobooth-wedding.git
cd photobooth-wedding
```

### 2. Instal Dependensi & Konfigurasi Environment

```bash
composer setup
```

> Perintah di atas akan menginstal paket Composer, membuat file `.env` dari `.env.example`, mengenerate APP_KEY, menjalankan migrasi database SQLite, menginstal modul NPM, dan mem-build aset frontend.

### 3. Konfigurasi Cloud Storage & WebSocket (Opsional untuk Dev)

Buka file `.env` dan sesuaikan variabel jika ingin menggunakan Cloudflare R2 atau Reverb:

```env
# Storage (Local atau Cloudflare R2)
FILESYSTEM_DISK=r2
R2_ACCESS_KEY_ID=your_key
R2_SECRET_ACCESS_KEY=your_secret
R2_BUCKET=photobooth-wedding
R2_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
R2_URL=https://pub-<id>.r2.dev

# Realtime Broadcasting
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your_id
REVERB_APP_KEY=your_key
REVERB_APP_SECRET=your_secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 4. Menjalankan Server Pengembangan

Jalankan seluruh layanan secara bersamaan:

```bash
composer dev
```

Atau jalankan di terminal terpisah:

```bash
# Terminal 1: Laravel Web Server
php artisan serve

# Terminal 2: Vite Dev Server
npm run dev

# Terminal 3: Laravel Reverb WebSocket Server
php artisan reverb:start --debug
```

Akses aplikasi di browser pada: `http://localhost:8000`

---

## 🧪 Pengujian & Standar Kualitas

Aplikasi ini dilengkapi pengujian otomatis dan pengecekan tipe statis:

```bash
# Menjalankan seluruh pemeriksaan CI (Linter, TypeScript, PHPStan, PHPUnit)
composer ci:check

# Format kode backend (Laravel Pint)
composer lint

# Pengecekan formatting & lint frontend
npm run check

# Menjalankan PHPUnit test
composer test
```

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi [MIT License](LICENSE).
