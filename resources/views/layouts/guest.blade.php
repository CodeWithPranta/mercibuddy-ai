<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="landing-background landing-locked">
            <!-- Page Content -->
            <main class="landing-main">
                <div class="app-shell">
                    <livewire:navigation />

                    <div class="app-page">
                        <div class="w-full max-w-md mx-auto">
                            <div class="app-page-card">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>

                    <livewire:footer-section />
                </div>
            </main>
        </div>

        @include('components.assistant-chat')

        @livewireScripts
    </body>
</html>
