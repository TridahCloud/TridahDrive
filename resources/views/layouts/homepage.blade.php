<!DOCTYPE html>
@php
    $userTheme = auth()->check() ? (auth()->user()->theme ?? 'dark') : null;
    $initialTheme = $userTheme ?? 'dark';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $initialTheme }}" data-initial-theme="{{ $initialTheme }}" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#31d8b2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- SEO Meta Tags -->
    <title>@yield('title', 'Home') - TridahDrive | All-in-One Business Management Platform</title>
    <meta name="description" content="@yield('description', 'TridahDrive is a comprehensive business management platform combining Invoicer, BookKeeper, and Project Board into one integrated solution. Manage invoices, track finances, and organize projects all in one place.')">
    <meta name="keywords" content="@yield('keywords', 'business management, invoicing, accounting, project management, finance tracking, bookkeeping, team collaboration')">
    <meta name="author" content="TridahCloud">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('title', 'Home') - TridahDrive">
    <meta property="og:description" content="@yield('description', 'All-in-One Business Management Platform')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/tridah icon.png') }}">
    <meta property="og:site_name" content="TridahDrive">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Home') - TridahDrive">
    <meta name="twitter:description" content="@yield('description', 'All-in-One Business Management Platform')">
    <meta name="twitter:image" content="{{ asset('images/tridah icon.png') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/tridah-icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/tridah-icon-512.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Bootstrap 5.3.x CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    
    @stack('styles')

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17684753649"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-17684753649');
    </script>
</head>
<body class="homepage-body">
    <!-- Navigation -->
    @include('partials.navbar')
    
    <!-- Page Content -->
    @yield('content')
    
    <!-- Footer -->
    @include('partials.footer')
    
    <!-- Toast Container -->
    @include('partials.toast-container')
    
    <!-- Bootstrap 5.3.x JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Toggle Script -->
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
    
    <!-- Toast Manager -->
    <script src="{{ asset('js/toast.js') }}"></script>
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch((error) => {
                    console.warn('Service worker registration failed:', error);
                });
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>

