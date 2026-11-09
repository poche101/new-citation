@extends('layouts.public')

@section('title', 'Submitted')

@section('content')
    @include('partials.public-header')

    <main class="mx-auto max-w-2xl px-5 pb-24 pt-16 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gold-100 text-gold-700" aria-hidden="true">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5" /></svg>
        </div>

        <h1 class="mt-8 font-display text-4xl font-medium tracking-tight text-royal-900 sm:text-5xl">Your submission has been received</h1>

        <p class="mt-5 text-lg leading-relaxed text-slate-600">
            Your <span class="font-semibold text-royal-900">{{ strtolower($submitted['label']) }}</span>
            for <span class="font-semibold text-royal-900">{{ $submitted['entity'] }}</span>
            was submitted by {{ $submitted['name'] }}.
            @if (($submitted['files'] ?? 0) > 0)
                {{ $submitted['files'] }} {{ \Illuminate\Support\Str::plural('file', $submitted['files']) }} attached.
            @endif
        </p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('reports.choose') }}"
                class="rounded-full bg-gold-500 px-8 py-3.5 font-semibold text-royal-950 transition hover:bg-gold-400">Submit another report</a>
            <a href="{{ route('home') }}"
                class="rounded-full border border-royal-200 px-8 py-3.5 font-semibold text-royal-800 transition hover:bg-royal-50">Back to home</a>
        </div>
    </main>
@endsection
