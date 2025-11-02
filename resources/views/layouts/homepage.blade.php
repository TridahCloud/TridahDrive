<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Home') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5.3.x CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    
    @stack('styles')
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
    
    @stack('scripts')
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17684753649"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-17684753649');
    </script>
</body>
</html>

