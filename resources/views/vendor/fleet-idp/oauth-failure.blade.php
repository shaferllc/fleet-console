@extends('layouts.console')

@section('title', trans('fleet-idp::oauth.failure_title'))

@section('simple_header')
    <div class="px-4 pt-12 pb-4 text-center sm:pt-16">
        @include('partials.fleet-logo-mark', ['variant' => 'login'])
        <h1 class="fc-heading mt-6 text-2xl font-bold tracking-tight text-white sm:text-3xl">
            {{ trans('fleet-idp::oauth.failure_title') }}
        </h1>
    </div>
@endsection

@section('content')
    <div class="fc-glass rounded-2xl p-8 sm:p-9">
        <p class="text-sm leading-relaxed text-zinc-300">{{ $message }}</p>
        <p class="mt-6">
            <a
                href="{{ $tryAgainUrl }}"
                class="inline-flex w-full items-center justify-center rounded-xl border border-zinc-600/80 bg-zinc-950/60 px-4 py-3 text-sm font-semibold text-zinc-100 transition hover:border-cyan-500/40 hover:bg-zinc-900 hover:text-cyan-100 sm:w-auto"
            >
                {{ trans('fleet-idp::oauth.try_again') }}
            </a>
        </p>
    </div>
@endsection
