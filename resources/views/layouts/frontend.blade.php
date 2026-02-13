<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/leadcliq-favicon.png') }}">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">
        
        <!-- Sitemap -->
        <link rel="sitemap" type="application/xml" href="{{ url('/sitemap.xml') }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        @stack('meta')

        <!-- DNS Prefetch & Preconnect -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Swiper Slider -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

        <link rel="manifest" href="/manifest.json">

        <meta name="theme-color" content="#0f172a">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="LeadCliq">

        


        <!-- Styles / Scripts -->
        @vite(['resources/css/frontend.css', 'resources/css/app.css',  'resources/js/app.js'])

        @stack('styles')
    </head>
    <body class="font-sans antialiased" data-aos-easing="ease" data-aos-duration="400" data-aos-delay="0" x-data="{ mobileMenuOpen: false }">

        <x-frontend-header />

        @yield('content')

        <!-- Footer -->
        <x-frontend-footer />

        @stack('scripts')
    </body>
</html>
