@extends('layouts.console')

@section('title', 'Sign in')

@section('simple_header')
    <div class="px-4 pt-16 pb-8 text-center sm:pt-20">
        @include('partials.fleet-logo-mark', ['variant' => 'login'])
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
