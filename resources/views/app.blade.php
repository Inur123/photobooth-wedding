<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no" class="notranslate" style="color-scheme: light;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="google" content="notranslate">
        <meta name="color-scheme" content="light">
        <meta name="supported-color-schemes" content="light">

        {{-- titip.moment is strictly light-only. Never add the dark class or enable dark mode. --}}
        <style>
            :root, html, body {
                color-scheme: light !important;
                background-color: oklch(1 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased notranslate" translate="no" style="color-scheme: light;">
        <x-inertia::app />
    </body>
</html>
