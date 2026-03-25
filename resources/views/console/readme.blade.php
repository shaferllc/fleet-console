@extends('layouts.console')

@section('title', ($readmeTitle ?? $target['name']).' — README')

@section('topbar_actions')
    <a href="{{ route('console.dashboard') }}" class="rounded-lg px-3 py-2 text-xs font-medium text-zinc-400 transition hover:bg-zinc-800/50 hover:text-white">
        ← Overview
    </a>
    <form method="post" action="{{ route('console.logout') }}" class="inline">
        @csrf
        <button type="submit" class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70 hover:text-white">
            Sign out
        </button>
    </form>
@endsection

@section('content')
    <header class="max-w-4xl">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-500/90">Documentation</p>
        <h1 class="fc-heading mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $readmeTitle ?? $target['name'] }}</h1>
        @if ($readmeSubtitle !== null && $readmeSubtitle !== '')
            <p class="mt-2 text-sm leading-relaxed text-zinc-400">{{ $readmeSubtitle }}</p>
        @endif
        @if ($description !== '')
            <p class="mt-3 text-base leading-relaxed text-zinc-400">{{ $description }}</p>
        @endif
        @if ($readmeFormat !== null && $readmeFormat !== '')
            <p class="mt-2 text-[10px] font-medium uppercase tracking-wider text-zinc-600">Format: {{ $readmeFormat }}</p>
        @endif
        <div class="mt-6 flex flex-wrap items-center gap-3">
            @if ($siteUrl !== '')
                <a href="{{ $siteUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-cyan-500/10 px-4 py-2.5 text-sm font-semibold text-cyan-200 ring-1 ring-cyan-500/25 transition hover:bg-cyan-500/15 hover:text-white" aria-label="Open {{ $target['name'] }} in a new tab">
                    Open site<span class="text-cyan-400/70" aria-hidden="true">↗</span>
                </a>
            @endif
        </div>
        <div class="fc-glass mt-6 rounded-xl p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Operator endpoint</p>
            <p class="fc-url-pill mt-2 break-all text-zinc-400">{{ $readmeUrl }}</p>
        </div>
    </header>

    @if ($error)
        <div class="fc-glass mt-10 rounded-2xl border-red-500/30 p-6 ring-1 ring-red-500/20">
            <p class="text-sm font-medium text-red-200">Could not load README</p>
            <p class="mt-2 text-sm leading-relaxed text-red-300/80">{{ $error }}</p>
        </div>
    @elseif ($rawFallback)
        <p class="mt-10 text-sm text-zinc-400">{{ $rawFallback }}</p>
    @else
        <div class="fc-glass mt-10 min-w-0 overflow-hidden rounded-2xl p-6 sm:p-8 lg:p-10">
            <div class="min-w-0 overflow-x-auto">
                <article
                    class="readme-markdown prose prose-invert prose-zinc min-w-0 max-w-none text-[15px] leading-relaxed
                        prose-headings:scroll-mt-24 prose-headings:font-semibold prose-headings:tracking-tight prose-headings:text-zinc-50
                        prose-h1:text-3xl prose-h2:text-xl prose-h2:border-b prose-h2:border-zinc-700/80 prose-h2:pb-2 prose-h3:text-lg
                        prose-p:text-zinc-300 prose-li:text-zinc-300 prose-strong:text-zinc-100
                        prose-a:font-medium prose-a:text-cyan-400 prose-a:no-underline hover:prose-a:underline prose-a:decoration-cyan-500/50
                        prose-blockquote:border-l-cyan-500/60 prose-blockquote:text-zinc-400 prose-blockquote:not-italic
                        prose-code:rounded-md prose-code:border prose-code:border-zinc-600/60 prose-code:bg-zinc-950/80 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-[0.875em] prose-code:font-normal prose-code:text-cyan-100
                        prose-code:before:content-none prose-code:after:content-none
                        prose-pre:rounded-xl prose-pre:border prose-pre:border-zinc-700/80 prose-pre:bg-zinc-950 prose-pre:shadow-inner prose-pre:text-zinc-200
                        prose-pre:code:bg-transparent prose-pre:code:border-0 prose-pre:code:p-0 prose-pre:code:text-[0.8125rem] prose-pre:code:text-inherit
                        prose-table:w-full prose-table:text-left prose-table:text-sm prose-table:rounded-lg prose-table:border prose-table:border-zinc-700/60
                        prose-thead:border-b prose-thead:border-zinc-600 prose-th:bg-zinc-950/80 prose-th:px-3 prose-th:py-2 prose-th:text-zinc-300 prose-th:font-medium
                        prose-td:px-3 prose-td:py-2 prose-tr:border-b prose-tr:border-zinc-800/80 prose-td:text-zinc-300
                        prose-img:rounded-lg prose-img:border prose-img:border-zinc-700/60 prose-hr:border-zinc-700/80">
                    {!! $html !!}
                </article>
            </div>
        </div>
    @endif
@endsection
