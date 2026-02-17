<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Талант-центр') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-dark antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cream pattern-bg">
            <!-- Logo -->
            <div class="mb-6">
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-14 h-14 gradient-gold rounded-full flex items-center justify-center shadow-md">
                        <i class="fas fa-award text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="font-serif text-xl font-bold text-primary">Талант-центр</h1>
                        <p class="text-xs text-warm-gray">Всероссийский центр талантов</p>
                    </div>
                </a>
            </div>

            <!-- Auth Card -->
            <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-lg overflow-hidden sm:rounded-xl border border-gold/20">
                {{ $slot }}
            </div>

            <!-- Back link -->
            <div class="mt-4 text-sm text-warm-gray">
                <a href="/" class="hover:text-primary transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Вернуться на главную
                </a>
            </div>
        </div>
    </body>
</html>
