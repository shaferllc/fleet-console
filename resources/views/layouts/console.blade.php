<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'Fleet Console') — {{ config('app.name') }}</title>
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
                    <div class="fc-logo-ring relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl p-[2px] shadow-lg shadow-cyan-500/10">
                        <span class="flex h-full w-full items-center justify-center rounded-[10px] bg-zinc-950 text-sm font-bold text-cyan-400">
                            <svg class="h-5 w-5 text-cyan-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path d="M4 14.5A2.5 2.5 0 0 1 6.5 12H8a6 6 0 0 1 6 6v1a2 2 0 0 1-2 2h-1.5" stroke-linecap="round"/>
                                <path d="M8 5.5A2.5 2.5 0 0 0 5.5 8v8A2.5 2.5 0 0 0 8 18.5" stroke-linecap="round"/>
                                <path d="M20 9.5A2.5 2.5 0 0 0 17.5 12H16a6 6 0 0 0-6-6V5a2 2 0 0 1 2-2h1.5" stroke-linecap="round"/>
                                <path d="M16 18.5a2.5 2.5 0 0 0 2.5-2.5V8A2.5 2.5 0 0 0 16 5.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
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
