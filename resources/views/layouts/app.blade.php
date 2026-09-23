<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Платформа для участия в онлайн-конкурсах">

        <title>{{ config('app.name', 'Талант-центр') }}</title>

        <!-- Favicon -->
        @if(!empty($siteSettings[\App\Models\SiteSettings::SITE_FAVICON]))
            <link rel="icon" href="{{ asset('storage/' . $siteSettings[\App\Models\SiteSettings::SITE_FAVICON]) }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

        {{-- Sidebar narrowed on this device: applied before the first paint so the page doesn't jump (resources/js/app-shell.js) --}}
        <script>
            try { if (localStorage.getItem('tc.sidebar.compact') === '1') document.documentElement.classList.add('sidebar-compact'); } catch (e) {}
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-cream">
        {{-- Keyboard users can jump past the sidebar straight to the page --}}
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-primary focus:shadow-lg focus:ring-2 focus:ring-primary/40">
            Перейти к содержимому
        </a>

        <x-notify />

        <div x-data="appShell" @keydown.escape.window="closeSidebar()">

            {{-- Dims the page behind the drawer on small screens --}}
            <div x-show="sidebarOpen" x-cloak
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="closeSidebar()"
                class="fixed inset-0 z-40 bg-dark/40 backdrop-blur-sm xl:hidden"
                aria-hidden="true"></div>

            <x-app-sidebar />

            <div class="flex min-h-screen flex-col transition-[padding] duration-300 ease-[cubic-bezier(0.32,0.72,0,1)] motion-reduce:transition-none xl:pl-64 xl:sidebar-compact:pl-20" :inert="sidebarOpen">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header>
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main id="main-content" tabindex="-1" class="flex-1 focus:outline-none">
                    {{ $slot }}
                </main>

                @include('layouts.footer')
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
