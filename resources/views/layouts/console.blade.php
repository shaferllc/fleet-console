<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'Fleet Console') — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/fleet-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/fleet-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=JetBrains+Mono:ital,wght@0,400;0,500;1,400&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="fc-app relative min-h-full font-sans text-zinc-100 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 fc-bg" aria-hidden="true"></div>

    @hasSection('simple_header')
        <div class="relative">
            @yield('simple_header')
            <div class="mx-auto max-w-lg px-4 pb-16 pt-4 sm:px-6">
                @yield('content')
            </div>
        </div>
    @else
        <div id="fleet-console-shell">
        <header class="fc-topbar">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('console.dashboard') }}" class="group flex items-center gap-3 rounded-lg outline-none ring-cyan-500/40 focus-visible:ring-2">
                    @include('partials.fleet-logo-mark', ['variant' => 'header'])
                    <div class="min-w-0 text-left">
                        <p class="fc-heading truncate text-base font-semibold tracking-tight text-white group-hover:text-cyan-100">Fleet console</p>
                        <p class="truncate text-xs text-zinc-500">Operator overview</p>
                    </div>
                </a>
                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    @yield('topbar_actions')
                </div>
            </div>
        </header>

        <main class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
            @yield('content')
        </main>
        </div>
        @stack('fleet_overlays')
    @endif
</body>
</html>
