<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5.3.x CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('partials.sidebar')
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            @include('partials.header')
            
            <!-- Page Content -->
            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Toast Container -->
    @include('partials.toast-container')
    
    <!-- Bootstrap 5.3.x JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Toggle Script -->
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
    
    <!-- Toast Manager -->
    <script src="{{ asset('js/toast.js') }}"></script>
    
    <!-- Notification Manager -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    
    @stack('scripts')
</body>
</html>

