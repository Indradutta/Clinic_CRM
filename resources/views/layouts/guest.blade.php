<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#eaf7fa">

    <title>{{ config('app.name', 'MediFlow') }} - Secure sign in</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"></noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-text-primary">
    <main class="auth-shell" style="--auth-background-desktop: url('{{ asset('images/login-background-desktop.webp') }}'); --auth-background-mobile: url('{{ asset('images/login-background-mobile.webp') }}');">
        <section class="auth-form-panel" aria-labelledby="login-heading">
            <div class="auth-form-content">
                <a class="auth-wordmark" href="{{ route('login') }}" aria-label="MediFlow sign in">
                    <img src="{{ asset('images/mediflow-logo.svg') }}" alt="" class="h-10 w-10">
                    <span>Medi<span>Flow</span></span>
                </a>

                @yield('content')

                <p class="auth-copyright">&copy; {{ date('Y') }} MediFlow</p>
            </div>
        </section>


    </main>
</body>
</html>
