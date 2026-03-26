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
        @if ($errors->has('password') || $errors->has('email'))
            <div class="mb-6 rounded-xl border border-red-500/40 bg-red-950/40 px-4 py-3 text-sm text-red-300" role="alert">
                {{ $errors->first('email') ?: $errors->first('password') }}
            </div>
        @endif

        @if (!empty($fleetIdpEnabled))
            <div class="mb-6">
                <x-fleet-idp::oauth-button variant="console" />
            </div>
        @endif

        @php
            $anyForm = !empty($fleetPasswordLoginEnabled) || !empty($localPasswordEnabled);
        @endphp

        @if (!empty($fleetIdpEnabled) && $anyForm)
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-zinc-700/80"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase tracking-wide text-zinc-500">
                    <span class="bg-zinc-950/40 px-3">
                        @if (!empty($fleetPasswordLoginEnabled))
                            {{ __('Or sign in with email and password') }}
                        @else
                            {{ __('Or sign in below') }}
                        @endif
                    </span>
                </div>
            </div>
        @endif

        @if ($anyForm)
            <form method="post" action="{{ route('console.login') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-300">
                        @if (!empty($fleetPasswordLoginEnabled) && empty($localPasswordEnabled))
                            {{ __('Email') }}
                        @elseif (!empty($localPasswordEnabled) && empty($fleetPasswordLoginEnabled))
                            {{ __('Email') }} <span class="font-normal text-zinc-500">({{ __('optional') }})</span>
                        @else
                            {{ __('Email') }} <span class="font-normal text-zinc-500">({{ __('optional for shared password') }})</span>
                        @endif
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        @if (!empty($fleetPasswordLoginEnabled) && empty($localPasswordEnabled))
                            required
                        @endif
                        @if (!empty($fleetPasswordLoginEnabled))
                            autofocus
                        @endif
                        class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                        autocomplete="username"
                        placeholder="you@example.com"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                @if (!empty($fleetPasswordLoginEnabled))
                    <p class="text-xs leading-relaxed text-zinc-500">
                        {{ __('With email filled in, we verify your password against Fleet Auth and keep your user in sync.') }}
                    </p>
                @elseif (!empty($localPasswordEnabled))
                    <p class="text-xs leading-relaxed text-zinc-500">
                        {{ __('Leave email empty if your team only uses the shared console password from the server configuration.') }}
                    </p>
                @endif
                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-300">{{ __('Password') }}</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        @if (empty($fleetPasswordLoginEnabled))
                            autofocus
                        @endif
                        class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="fc-btn-primary w-full rounded-xl px-4 py-3 text-sm font-semibold text-white">
                    {{ __('Sign in') }}
                </button>
            </form>
        @elseif (empty($fleetIdpEnabled))
            <p class="text-center text-sm text-zinc-400">{{ __('Console login is not configured.') }}</p>
        @endif

        @if (!empty($fleetPasswordLoginEnabled) && !empty($localPasswordEnabled))
            <p class="mt-6 text-center text-xs leading-relaxed text-zinc-500">
                {{ __('Leave email empty to use only the shared console password; fill email and password to use Fleet Auth credentials.') }}
            </p>
        @endif
    </div>
    <p class="mt-8 text-center text-xs text-zinc-600">
        {{ __('Use a strong secret and restrict network access (VPN, IP allowlist, reverse proxy auth) in production.') }}
    </p>
@endsection
