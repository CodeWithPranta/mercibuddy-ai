<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <meta content="{{ ($setting ?? null)?->meta_description }}" name="description">
        <meta content="{{ ($setting ?? null)?->keywords }}" name="keywords">

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
                <div class="app-shell">@yield('content')</div>
            </main>
        </div>

        @include('components.assistant-chat')

        @livewireScripts
    </body>
</html>
