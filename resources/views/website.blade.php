@php
    /** @var array<string, mixed> $page */
    $seo = $page['props']['seo'] ?? [];
    $title = $seo['title'] ?? config('app.name', 'DT Propix');
    $description = $seo['description'] ?? '';
    $image = $seo['image'] ?? url('/images/brand/logo-512.png');
    $canonical = $seo['canonical'] ?? url()->current();
    $ogLocale = $seo['locale'] ?? 'en_US';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ $title }}</title>
        <meta name="description" content="{{ $description }}">
        <meta name="robots" content="{{ $seo['robots'] ?? 'index, follow' }}">
        <link rel="canonical" href="{{ $canonical }}">
        @foreach ($seo['alternates'] ?? [] as $lang => $href)
            <link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}">
        @endforeach
        @isset($seo['alternates']['en'])
            <link rel="alternate" hreflang="x-default" href="{{ $seo['alternates']['en'] }}">
        @endisset

        <meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
        <meta property="og:site_name" content="{{ $seo['site_name'] ?? config('app.name') }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $image }}">
        <meta property="og:locale" content="{{ $ogLocale }}">
        <meta property="og:locale:alternate" content="{{ $ogLocale === 'en_US' ? 'bn_BD' : 'en_US' }}">
        @if (! empty($seo['published_time']))
            <meta property="article:published_time" content="{{ $seo['published_time'] }}">
        @endif
        @if (! empty($seo['modified_time']))
            <meta property="article:modified_time" content="{{ $seo['modified_time'] }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title }}">
        <meta name="twitter:description" content="{{ $description }}">
        <meta name="twitter:image" content="{{ $image }}">

        <meta name="theme-color" content="#0b63ce">
        <link rel="icon" href="/favicon.ico" sizes="32x32">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/images/brand/logo-512.png">

        @foreach ($seo['json_ld'] ?? [] as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
        @endforeach

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="bg-surface text-foreground antialiased">
        @inertia
    </body>
</html>
