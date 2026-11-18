@php
    $seo = \App\Support\SeoMeta::fromPage($page ?? [], request());
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no" class="notranslate" style="color-scheme: light;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="google" content="notranslate">
        <meta name="color-scheme" content="light">
        <meta name="supported-color-schemes" content="light">
        <meta name="theme-color" content="#ffffff">
        <meta name="application-name" content="{{ $seo['og_site_name'] }}">
        <meta name="apple-mobile-web-app-title" content="{{ $seo['og_site_name'] }}">

        {{-- Primary SEO Meta Tags --}}
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="keywords" content="{{ $seo['keywords'] }}">
        <meta name="robots" content="{{ $seo['robots'] }}">
        <meta name="googlebot" content="{{ $seo['robots'] }}">
        <meta name="author" content="{{ $seo['og_site_name'] }}">
        <link rel="canonical" href="{{ $seo['canonical'] }}">

        {{-- Open Graph / WhatsApp / Facebook / Telegram / Instagram --}}
        <meta property="og:type" content="{{ $seo['og_type'] }}">
        <meta property="og:site_name" content="{{ $seo['og_site_name'] }}">
        <meta property="og:locale" content="{{ $seo['og_locale'] }}">
        <meta property="og:title" content="{{ $seo['og_title'] }}">
        <meta property="og:description" content="{{ $seo['og_description'] }}">
        <meta property="og:url" content="{{ $seo['og_url'] }}">
        <meta property="og:image" content="{{ $seo['og_image'] }}">
        <meta property="og:image:secure_url" content="{{ $seo['og_image'] }}">
        <meta property="og:image:type" content="{{ $seo['og_image_type'] }}">
        <meta property="og:image:width" content="{{ $seo['og_image_width'] }}">
        <meta property="og:image:height" content="{{ $seo['og_image_height'] }}">
        <meta property="og:image:alt" content="{{ $seo['og_image_alt'] }}">
        <meta property="og:image" content="{{ $seo['og_square_image'] }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="600">
        <meta property="og:image:height" content="600">
        <meta property="og:image:alt" content="{{ $seo['og_image_alt'] }}">

        {{-- Twitter / X Card --}}
        <meta name="twitter:card" content="{{ $seo['twitter_card'] }}">
        <meta name="twitter:title" content="{{ $seo['og_title'] }}">
        <meta name="twitter:description" content="{{ $seo['og_description'] }}">
        <meta name="twitter:image" content="{{ $seo['og_image'] }}">
        <meta name="twitter:image:alt" content="{{ $seo['og_image_alt'] }}">

        {{-- titip.moment is strictly light-only. Never add the dark class or enable dark mode. --}}
        <style>
            :root, html, body {
                color-scheme: light !important;
                background-color: #ffffff;
                background-color: oklch(1 0 0);
            }
        </style>

        {{-- Favicons & Web App Icons (1:1 square multiples of 48px for Google Search + uncropped full logo) --}}
        <link rel="icon" type="image/x-icon" href="/favicon.ico" sizes="16x16 32x32 48x48 64x64">
        <link rel="icon" type="image/png" sizes="48x48" href="/favicon-48x48.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/icon-512.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">

        @if (! empty($seo['json_ld']))
            @foreach ($seo['json_ld'] as $schema)
                <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
            @endforeach
        @endif

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title inertia>{{ $seo['title'] }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased notranslate" translate="no" style="color-scheme: light;">
        <x-inertia::app />
    </body>
</html>
