<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO Meta Tags -->
    <title>@yield('title', 'Dashboard') - TridahDrive</title>
    <meta name="description" content="@yield('description', 'Manage your business with TridahDrive')">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/tridah icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/tridah icon.png') }}">
    
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
    <div class="wrapper" x-data="{
        sidebarOpen: window.innerWidth >= 992,
        isMobile: window.innerWidth < 992,
        init() {
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 992;
                if (this.isMobile && this.sidebarOpen) {
                    this.sidebarOpen = false;
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && this.isMobile && this.sidebarOpen) {
                    this.sidebarOpen = false;
                }
            });
        }
    }" x-init="init()">
        <!-- Sidebar -->
        @include('partials.sidebar')
        <div class="sidebar-overlay" x-show="sidebarOpen && isMobile" x-transition.opacity @click="sidebarOpen = false" x-cloak></div>
        
        <!-- Main Content -->
        <div class="main-content" :class="{ 'sidebar-collapsed': !sidebarOpen }">
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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Theme Toggle Script -->
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
    
    <!-- Toast Manager -->
    <script src="{{ asset('js/toast.js') }}"></script>
    
    <!-- Notification Manager -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    
    @stack('scripts')
</body>
</html>

