@extends('layouts.public')

@section('title', 'Choose a report')

@section('content')
    @include('partials.public-header')

    <main class="mx-auto max-w-5xl px-5 pb-20 pt-12">
        <h1 class="font-display text-4xl font-medium tracking-tight text-royal-900 sm:text-5xl">Which report are you submitting?</h1>
        <p class="mt-4 max-w-2xl text-lg leading-relaxed text-slate-600">
            Choose one. Next you will select your department or group, then fill in the form.
        </p>

        {{-- Annual reports --}}
        <section class="mt-12" aria-labelledby="reports-heading">
            <h2 id="reports-heading" class="font-display text-2xl font-medium text-royal-800">Annual reports</h2>

            <ul class="mt-5 space-y-4">
                @foreach ($reports as $key => $type)
                    <li>
                        <a href="{{ route('reports.entities', $type['slug']) }}"
                            class="group flex items-center justify-between gap-6 rounded-xl border border-royal-100 bg-white px-6 py-6 shadow-sm transition hover:border-royal-500 hover:shadow-md">
                            <span>
                                <span class="block text-xl font-semibold text-royal-900">{{ $type['label'] }}</span>
                                <span class="mt-1.5 block max-w-2xl leading-relaxed text-slate-600">{{ $type['description'] }}</span>
                                <span class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($type['includes'] as $item)
                                        <span class="rounded-full bg-royal-50 px-3 py-1 text-sm font-medium text-royal-700">{{ $item }}</span>
                                    @endforeach
                                </span>
                            </span>
                            <svg class="h-6 w-6 shrink-0 text-royal-300 transition group-hover:translate-x-1 group-hover:text-royal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Citations --}}
        <section class="mt-14" aria-labelledby="citations-heading">
            <h2 id="citations-heading" class="font-display text-2xl font-medium text-royal-800">Citations</h2>

            <ul class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($citations as $key => $type)
                    <li>
                        <a href="{{ route('reports.entities', $type['slug']) }}"
                            class="group flex h-full items-center justify-between gap-5 rounded-xl border border-royal-100 bg-white px-6 py-6 shadow-sm transition hover:border-royal-500 hover:shadow-md">
                            <span>
                                <span class="block text-xl font-semibold text-royal-900">{{ $type['label'] }}</span>
                                <span class="mt-1.5 block leading-relaxed text-slate-600">{{ $type['description'] }}</span>
                                <span class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($type['includes'] as $item)
                                        <span class="rounded-full bg-gold-100 px-3 py-1 text-sm font-medium text-gold-700">{{ $item }}</span>
                                    @endforeach
                                </span>
                            </span>
                            <svg class="h-6 w-6 shrink-0 text-royal-300 transition group-hover:translate-x-1 group-hover:text-royal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    </main>
@endsection
