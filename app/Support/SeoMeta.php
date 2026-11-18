<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class SeoMeta
{
    public const PRODUCTION_URL = 'https://photobooth.titipmoment.id';

    public const SITE_NAME = 'titip.moment';

    public const DEFAULT_TITLE = 'titip.moment — Photobooth Digital Pernikahan & Event | Scan QR dari HP Tamu';

    public const DEFAULT_DESCRIPTION = 'Layanan photobooth digital & QR camera untuk pernikahan, lamaran, ulang tahun, dan event. Tamu cukup scan QR dari HP tanpa aplikasi, foto dengan bingkai estetik, rekam video Reels & voice note, langsung tampil di galeri live real-time.';

    public const DEFAULT_KEYWORDS = 'photobooth digital, photobooth pernikahan, wedding photobooth online, scan qr photobooth, photobox digital hp, galeri pernikahan live, buku tamu digital pernikahan, titip moment, photobooth tanpa mesin, qr camera wedding indonesia';

    /**
     * Resolve comprehensive server-side SEO, OpenGraph, Twitter Card, and JSON-LD metadata
     * from the Inertia page payload and current request.
     *
     * @param  array<string, mixed>  $page
     * @return array{
     *     title: string,
     *     description: string,
     *     keywords: string,
     *     robots: string,
     *     canonical: string,
     *     og_type: string,
     *     og_site_name: string,
     *     og_locale: string,
     *     og_title: string,
     *     og_description: string,
     *     og_url: string,
     *     og_image: string,
     *     og_image_type: string,
     *     og_image_width: int,
     *     og_image_height: int,
     *     og_image_alt: string,
     *     og_square_image: string,
     *     twitter_card: string,
     *     json_ld: array<int, array<string, mixed>>
     * }
     */
    public static function fromPage(array $page, Request $request): array
    {
        $component = is_string($page['component'] ?? null) ? $page['component'] : '';
        $props = is_array($page['props'] ?? null) ? $page['props'] : [];

        $baseUrl = rtrim($request->getSchemeAndHttpHost() ?: config('app.url', self::PRODUCTION_URL), '/');
        $path = '/'.ltrim($request->getPathInfo(), '/');
        $canonicalPath = $path === '/' ? '' : rtrim($path, '/');
        $canonical = $baseUrl.$canonicalPath;

        $defaultOgImage = $baseUrl.'/images/og-image.jpg';
        $defaultSquareImage = $baseUrl.'/images/og-logo-square.png';
        $logoUrl = $baseUrl.'/icon-512.png';
        $wordmarkUrl = $baseUrl.'/images/titip-momen-logo-web.png';

        $title = self::DEFAULT_TITLE;
        $description = self::DEFAULT_DESCRIPTION;
        $keywords = self::DEFAULT_KEYWORDS;
        $robots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        $ogType = 'website';
        $ogImage = $defaultOgImage;
        $ogImageType = 'image/jpeg';
        $ogImageWidth = 1200;
        $ogImageHeight = 630;
        $ogImageAlt = 'titip.moment — Photobooth Digital Pernikahan & Event';
        $jsonLd = [];

        if ($component === 'Welcome') {
            $canonical = $baseUrl.'/';
            $jsonLd = self::welcomeJsonLd($baseUrl, $logoUrl, $wordmarkUrl, $defaultOgImage, $props);
        } elseif ($component === 'event/Show') {
            $event = is_array($props['event'] ?? null) ? $props['event'] : [];
            $isOwnerRoute = ! empty($props['ownerToken']) || str_contains($path, '/owner/');
            $slug = is_string($event['slug'] ?? null) ? $event['slug'] : '';

            $partyOne = trim((string) ($event['party_one_name'] ?? ''));
            $partyTwo = trim((string) ($event['party_two_name'] ?? ''));
            $partyDisplay = $partyOne !== '' && $partyTwo !== ''
                ? "{$partyOne} & {$partyTwo}"
                : ($partyOne !== '' ? $partyOne : 'Acara Spesial');

            $title = "{$partyDisplay} — Photobooth & Album Kenangan Digital | ".self::SITE_NAME;
            $ogImageAlt = "Album Kenangan & Photobooth Digital {$partyDisplay} — ".self::SITE_NAME;

            $details = [];
            if (! empty($event['event_date']) && is_string($event['event_date'])) {
                try {
                    $formattedDate = CarbonImmutable::parse($event['event_date'])
                        ->settings(['locale' => 'id'])
                        ->translatedFormat('j F Y');
                    $details[] = $formattedDate;
                } catch (\Throwable) {
                    // Ignore invalid date format
                }
            }
            if (! empty($event['venue']) && is_string($event['venue'])) {
                $details[] = trim($event['venue']);
            }

            $detailSuffix = count($details) > 0 ? ' ('.implode(' · ', $details).')' : '';
            $notes = trim((string) ($event['notes'] ?? ''));

            $description = $notes !== ''
                ? "{$partyDisplay}{$detailSuffix} — {$notes} Titipkan foto photobox, video Reels, voice note, dan ucapan terbaikmu di titip.moment."
                : "Titipkan foto photobox, video Reels, voice note, dan ucapan terbaikmu untuk acara {$partyDisplay}{$detailSuffix} langsung dari browser HP di titip.moment.";

            if ($slug !== '') {
                $canonical = "{$baseUrl}/e/{$slug}";
            }

            if ($isOwnerRoute) {
                $robots = 'noindex, nofollow';
            } else {
                $jsonLd = self::eventJsonLd($baseUrl, $canonical, $partyDisplay, $description, $defaultOgImage, $logoUrl, $event);
            }
        } elseif ($component === 'TermsOfService') {
            $title = 'Ketentuan Penggunaan — '.self::SITE_NAME;
            $description = 'Ketentuan layanan penggunaan platform photobooth digital dan galeri kenangan live titip.moment untuk penyelenggara acara dan tamu.';
            $jsonLd = [self::breadcrumbJsonLd($baseUrl, 'Ketentuan Penggunaan', $canonical)];
        } elseif ($component === 'PrivacyPolicy') {
            $title = 'Kebijakan Privasi — '.self::SITE_NAME;
            $description = 'Kebijakan privasi, perlindungan data, enkripsi galeri foto, video, dan rekaman suara tamu di platform photobooth digital titip.moment.';
            $jsonLd = [self::breadcrumbJsonLd($baseUrl, 'Kebijakan Privasi', $canonical)];
        } elseif ($component === 'auth/Login') {
            $title = 'Masuk Akun Penyelenggara — '.self::SITE_NAME;
            $description = 'Masuk ke dashboard titip.moment untuk mengelola galeri foto live, tema kartu photobooth, moderasi kiriman, dan kode QR acaramu.';
        } elseif ($component === 'auth/Register') {
            $title = 'Daftar Akun Acara — '.self::SITE_NAME;
            $description = 'Buat akun penyelenggara acara di titip.moment dan aktifkan photobooth digital berbasis QR untuk pernikahan atau event spesialmu.';
        } else {
            // Private dashboard, admin, settings, password reset, or error pages
            $robots = 'noindex, nofollow';
        }

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'robots' => $robots,
            'canonical' => $canonical,
            'og_type' => $ogType,
            'og_site_name' => self::SITE_NAME,
            'og_locale' => 'id_ID',
            'og_title' => $title,
            'og_description' => $description,
            'og_url' => $canonical,
            'og_image' => $ogImage,
            'og_image_type' => $ogImageType,
            'og_image_width' => $ogImageWidth,
            'og_image_height' => $ogImageHeight,
            'og_image_alt' => $ogImageAlt,
            'og_square_image' => $defaultSquareImage,
            'twitter_card' => 'summary_large_image',
            'json_ld' => $jsonLd,
        ];
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<int, array<string, mixed>>
     */
    private static function welcomeJsonLd(
        string $baseUrl,
        string $logoUrl,
        string $wordmarkUrl,
        string $ogImage,
        array $props,
    ): array {
        $packages = is_array($props['packages'] ?? null) ? $props['packages'] : [];
        $prices = [];
        $offers = [];

        foreach ($packages as $pkg) {
            if (! is_array($pkg) || ! isset($pkg['price'])) {
                continue;
            }
            $price = (int) $pkg['price'];
            $prices[] = $price;
            $offers[] = [
                '@type' => 'Offer',
                'name' => (string) ($pkg['name'] ?? 'Paket'),
                'description' => (string) ($pkg['tagline'] ?? ''),
                'price' => (string) $price,
                'priceCurrency' => 'IDR',
                'availability' => 'https://schema.org/InStock',
                'url' => $baseUrl.'/#harga',
            ];
        }

        $lowPrice = count($prices) > 0 ? min($prices) : 0;
        $highPrice = count($prices) > 0 ? max($prices) : 349000;

        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => $baseUrl.'/#organization',
            'name' => self::SITE_NAME,
            'alternateName' => 'Titip Moment Photobooth Digital',
            'url' => $baseUrl.'/',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logoUrl,
                'width' => 512,
                'height' => 512,
            ],
            'image' => [
                $logoUrl,
                $wordmarkUrl,
                $ogImage,
            ],
            'description' => self::DEFAULT_DESCRIPTION,
        ];

        $website = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $baseUrl.'/#website',
            'url' => $baseUrl.'/',
            'name' => self::SITE_NAME,
            'alternateName' => ['Titip Moment', 'Photobooth Digital titip.moment'],
            'inLanguage' => 'id-ID',
            'publisher' => [
                '@id' => $baseUrl.'/#organization',
            ],
        ];

        $softwareApp = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'titip.moment — Photobooth Digital Pernikahan & Event',
            'applicationCategory' => 'PhotographyApplication',
            'operatingSystem' => 'Web Browser (iOS Safari, Android Chrome, Desktop)',
            'url' => $baseUrl.'/',
            'image' => $ogImage,
            'description' => self::DEFAULT_DESCRIPTION,
            'offers' => [
                '@type' => 'AggregateOffer',
                'lowPrice' => (string) $lowPrice,
                'highPrice' => (string) $highPrice,
                'priceCurrency' => 'IDR',
                'offerCount' => max(1, count($offers)),
                'offers' => $offers,
            ],
        ];

        $faqPage = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Apakah tamu perlu download aplikasi dari Play Store / App Store?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Sama sekali tidak. Tamu tidak perlu menginstal aplikasi atau membuat akun. Cukup buka kamera HP (Android maupun iPhone), arahkan ke barcode QR, dan halaman web photobooth acaramu langsung terbuka dalam hitungan detik.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Apakah ada mesin fisik atau kru operator yang datang ke lokasi acara?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Tidak. titip.moment adalah layanan photobooth 100% digital berbasis web mandiri (self-service). Semua proses dilakukan langsung dari browser ponsel tamu, sehingga hemat biaya sewa mesin jutaan rupiah.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Berapa banyak foto atau video yang bisa dikirim oleh tamu?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Tidak ada batasan jumlah foto. Tamu bebas berfoto dengan bingkai acara dan mengirim momen sebanyak-banyaknya selama acara berlangsung tanpa kuota jepretan.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Apakah hasil foto bisa disetel privasi atau dimoderasi oleh pengantin?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Bisa. Di dashboard pengelola acara, penyelenggara bebas memilih visibilitas galeri: Publik (semua tamu bisa melihat album) atau Privat (hanya pengantin/penyelenggara yang bisa melihat), serta dapat menyembunyikan atau menghapus kiriman kapan saja.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Bagaimana cara mengunduh semua foto, video, dan suara setelah acara selesai?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Pada paket Plus dan Full, penyelenggara cukup login ke dashboard dan klik tombol Download ZIP untuk mengunduh seluruh foto photostrip berbingkai, video Reels, voice note, serta daftar ucapan tertulis dalam satu file arsip.',
                    ],
                ],
            ],
        ];

        return [$organization, $website, $softwareApp, $faqPage];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, mixed>>
     */
    private static function eventJsonLd(
        string $baseUrl,
        string $canonical,
        string $partyDisplay,
        string $description,
        string $defaultOgImage,
        string $logoUrl,
        array $event,
    ): array {
        $images = [$defaultOgImage, $logoUrl];
        if (! empty($event['cover_url']) && is_string($event['cover_url'])) {
            array_unshift($images, $event['cover_url']);
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => "Photobooth & Album Kenangan {$partyDisplay}",
            'description' => $description,
            'url' => $canonical,
            'image' => $images,
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/MixedEventAttendanceMode',
            'organizer' => [
                '@type' => 'Organization',
                'name' => self::SITE_NAME,
                'url' => $baseUrl.'/',
            ],
        ];

        if (! empty($event['event_date']) && is_string($event['event_date'])) {
            $schema['startDate'] = $event['event_date'];
        }

        $venue = trim((string) ($event['venue'] ?? ''));
        $schema['location'] = [
            '@type' => 'Place',
            'name' => $venue !== '' ? $venue : 'Lokasi Acara',
            'address' => $venue !== '' ? $venue : 'Indonesia',
        ];

        return [
            $schema,
            self::breadcrumbJsonLd($baseUrl, $partyDisplay, $canonical),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function breadcrumbJsonLd(string $baseUrl, string $pageName, string $pageUrl): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => self::SITE_NAME,
                    'item' => $baseUrl.'/',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $pageName,
                    'item' => $pageUrl,
                ],
            ],
        ];
    }
}
