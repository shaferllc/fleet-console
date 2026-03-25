@extends('layouts.console')

@section('title', 'Sign in')

@section('simple_header')
    <div class="px-4 pt-16 pb-8 text-center sm:pt-20">
        <div class="fc-logo-ring mx-auto flex h-16 w-16 items-center justify-center rounded-2xl p-[2px] shadow-xl shadow-cyan-500/15">
            <span class="flex h-full w-full items-center justify-center rounded-[14px] bg-zinc-950">
                <svg class="h-8 w-8 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M4 14.5A2.5 2.5 0 0 1 6.5 12H8a6 6 0 0 1 6 6v1a2 2 0 0 1-2 2h-1.5" stroke-linecap="round"/>
                    <path d="M8 5.5A2.5 2.5 0 0 0 5.5 8v8A2.5 2.5 0 0 0 8 18.5" stroke-linecap="round"/>
                    <path d="M20 9.5A2.5 2.5 0 0 0 17.5 12H16a6 6 0 0 0-6-6V5a2 2 0 0 1 2-2h1.5" stroke-linecap="round"/>
                    <path d="M16 18.5a2.5 2.5 0 0 0 2.5-2.5V8A2.5 2.5 0 0 0 16 5.5" stroke-linecap="round"/>
                </svg>
            </span>
        </div>
        <h1 class="fc-heading mt-8 text-3xl font-bold tracking-tight text-white sm:text-4xl">Fleet console</h1>
        <p class="mx-auto mt-3 max-w-sm text-sm leading-relaxed text-zinc-400">
            Sign in to poll your apps’ operator APIs and read project READMEs in one place.
        </p>
    </div>
@endsection

@section('content')
    <div class="fc-glass rounded-2xl p-8 sm:p-9">
        <form method="post" action="{{ route('console.login') }}" class="space-y-6">
            @csrf
            <div>
                <label for="password" class="block text-sm font-medium text-zinc-300">Password</label>
                <input type="password" name="password" id="password" required autofocus
                    class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                    autocomplete="current-password"
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                class="fc-btn-primary w-full rounded-xl px-4 py-3 text-sm font-semibold text-white">
                Sign in
            </button>
        </form>
    </div>
    <p class="mt-8 text-center text-xs text-zinc-600">
        Use a strong secret and restrict network access (VPN, IP allowlist, reverse proxy auth) in production.
    </p>
@endsection
