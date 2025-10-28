<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Bootstrap 5.3.x CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        
        <!-- Custom Auth CSS -->
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    </head>
    <body>
        <!-- Theme Toggle -->
        <button class="auth-theme-toggle theme-toggle" title="Toggle Theme">
            <i class="fas fa-moon"></i>
        </button>
        
        <div class="auth-container">
            <div class="auth-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/tridah icon.png') }}" alt="{{ config('app.name') }}" class="img-fluid">
                </a>
            </div>
            
            {{ $slot }}
        </div>
        
        <!-- Bootstrap 5.3.x JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Theme Toggle Script -->
        <script src="{{ asset('js/theme-toggle.js') }}"></script>
    </body>
</html>
