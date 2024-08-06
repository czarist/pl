<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>{{ $default_title ?? 'Porn Rooster' }}</title>
    <meta name="description" content="{{ $default_description ?? 'The best free adult content' }}">
    <meta name="author" content="czarist">
    <meta name="rating" content="adult">

    <meta name="keywords" content="{{ $default_keywords ?? 'free, adult, content, videos, porn' }}">
    <meta property="og:title" content="{{ $default_title ?? 'Porn Rooster' }}">
    <meta property="og:description" content="{{ $default_description ?? 'The best free adult content' }}">
    <meta property="og:image"
        content="{{ isset($isVideoPage) && $isVideoPage ? $page_thumb ?? asset('icon.png') : asset('icon.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="robots" content="index, follow">
    <meta name="referrer" content="no-referrer">
    <meta http-equiv="Content-Language" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="theme-color" content="#1F2937">
    <meta name="application-name" content="Porn Rooster">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('icon.png') }}">
    <meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self' https:; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:;">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Porn Rooster">
    <link rel="apple-touch-icon" href="{{ asset('icon.ico') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="dns-prefetch" href="https://static-ca-cdn.eporner.com">
    <link rel="dns-prefetch" href="https://ei-ph.rdtcdn.com">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    @if (isset($isVideoPage) && $isVideoPage)
        <meta property="og:video" content="{{ $video->url }}">
        <meta name="twitter:card" content="player">
        <meta name="twitter:image" content="{{ $page_thumb ?? asset('icon.png') }}">
        <meta name="twitter:player" content="{{ $video->url }}">
    @else
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ asset('icon.png') }}">
    @endif
</head>


<body class="bg-gray-900 text-white">
    <div id="app">
        @include('partials.header')
        <main>
            @yield('content')
        </main>
        @include('partials.footer')
    </div>
</body>

</html>
