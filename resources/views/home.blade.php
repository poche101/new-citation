@extends('layouts.public')

@section('title', 'Annual reports and citations')

@section('content')
    @php
        $types = collect(\App\Models\Report::types());
        $reports = $types->where('kind', 'report');
        $citations = $types->where('kind', 'citation');
        $limit = config('citation.max_words.citation');
    @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-royal-900 text-white"
        style="background-image:
            radial-gradient(60rem 38rem at 88% 8%, rgba(37,87,199,.60), transparent 62%),
            radial-gradient(38rem 26rem at -4% 104%, rgba(229,169,19,.16), transparent 62%);">

        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5">
            <a href="{{ route('home') }}" class="rounded-lg bg-white px-3 py-1.5" aria-label="Christ Embassy Lagos Zone 5, home">
                <img src="{{ asset('images/logo2.png') }}" alt="Christ Embassy Lagos Zone 5" class="h-9 w-auto">
            </a>
            <nav class="hidden gap-1 text-sm font-medium sm:flex" aria-label="Page sections">
                <a href="#what" class="rounded-lg px-3 py-2 text-royal-100 hover:bg-white/10">What you can submit</a>
                <a href="#how" class="rounded-lg px-3 py-2 text-royal-100 hover:bg-white/10">How it works</a>
            </nav>
        </div>

        <div class="mx-auto grid max-w-6xl items-center gap-14 px-5 pb-20 pt-10 lg:grid-cols-12 lg:pb-28 lg:pt-16">
            <div class="lg:col-span-6">
                <h1 class="font-display text-5xl font-medium leading-[1.03] tracking-tight sm:text-6xl lg:text-7xl">
                    Tell the story of your year.
                </h1>
                <p class="mt-6 max-w-[34rem] text-lg leading-relaxed text-royal-100">
                    Submit an annual report or a citation, for yourself or for your unit or department.
                    You can attach a document or spreadsheet.
                </p>
                <div class="mt-9">
                    <a href="{{ route('reports.choose') }}"
                        class="inline-flex items-center justify-center rounded-full bg-gold-500 px-8 py-4 text-base font-semibold text-royal-950 shadow-lg shadow-black/20 transition hover:bg-gold-400">
                        Submit a report
                    </a>
                </div>
            </div>

            {{-- A citation sheet: the thing people are about to write --}}
            <div class="lg:col-span-6">
                <figure class="settle mx-auto max-w-md rounded-sm bg-white p-3 text-ink shadow-2xl shadow-black/40"
                    aria-label="Layout of a citation">
                    <div class="border border-gold-500 p-1.5">
                        <div class="border border-gold-300 px-8 pb-7 pt-8 text-center">
                            <img src="{{ asset('images/logo2.png') }}" alt="" class="mx-auto h-14 w-auto">
                            <p class="mt-6 font-display text-4xl font-medium text-royal-900">Citation</p>
                            <p class="mt-1 text-sm text-slate-500">For the year {{ now()->year }}</p>

                            <div class="mx-auto mt-6 max-w-xs border-b border-royal-200 pb-2">
                                <p class="font-display text-xl italic text-slate-400">Name of the person, unit or department</p>
                            </div>

                            <p class="mt-6 font-display text-lg leading-relaxed text-slate-700">
                                Write up to {{ $limit }} words on the work, faithfulness and results that deserve to be recognised.
                            </p>

                            <div class="mx-auto mt-7 max-w-[12rem]">
                                <div class="h-1 rounded-full bg-royal-100">
                                    <div class="h-1 w-1/3 rounded-full bg-gold-500"></div>
                                </div>
                                <p class="mt-1.5 text-right text-xs text-slate-500">{{ intdiv($limit, 3) }} / {{ $limit }} words</p>
                            </div>
                        </div>
                    </div>
                </figure>
            </div>
        </div>
    </section>

    {{-- What you can submit --}}
    <section id="what" class="bg-white">
        <div class="mx-auto grid max-w-6xl gap-12 px-5 py-20 lg:grid-cols-12 lg:py-24">
            <div class="lg:col-span-4">
                <h2 class="font-display text-4xl font-medium tracking-tight text-royal-900">What you can submit</h2>
                <p class="mt-4 max-w-sm text-lg leading-relaxed text-slate-600">
                    Choose the one that fits. You can submit more than one, each in its own form.
                </p>
            </div>

            <div class="space-y-14 lg:col-span-8">
                @foreach ([['Annual reports', $reports], ['Citations', $citations]] as [$heading, $items])
                    <div>
                        <h3 class="font-display text-2xl font-medium text-royal-800">{{ $heading }}</h3>
                        <ul class="mt-5 space-y-7">
                            @foreach ($items as $item)
                                <li class="border-l-2 border-gold-500 pl-5">
                                    <p class="text-lg font-semibold text-royal-900">{{ $item['label'] }}</p>
                                    <p class="mt-1 max-w-xl leading-relaxed text-slate-600">{{ $item['description'] }}</p>
                                    <p class="mt-2 text-sm font-medium text-royal-700">{{ implode(' and ', $item['includes']) }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how" class="bg-paper">
        <div class="mx-auto max-w-6xl px-5 py-20 lg:py-24">
            <h2 class="font-display text-4xl font-medium tracking-tight text-royal-900">How it works</h2>

            <ol class="mt-12 grid gap-10 md:grid-cols-3">
                @foreach ([
                    ['Choose a report', 'Pick one of the four: a personal or unit annual report, or a personal or unit citation.'],
                    ['Select your department or group', 'This sends your submission to the right place.'],
                    ['Write and attach', 'Enter your details and the dates, write your report or citation, and attach files if you have them.'],
                ] as $i => [$title, $text])
                    <li>
                        <p class="font-display text-6xl font-medium text-gold-500" aria-hidden="true">{{ $i + 1 }}</p>
                        <p class="mt-3 text-xl font-semibold text-royal-900">{{ $title }}</p>
                        <p class="mt-2 max-w-xs leading-relaxed text-slate-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Closing call to action --}}
    <section class="bg-royal-800 text-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-6 px-5 py-14">
            <h2 class="font-display text-3xl font-medium tracking-tight sm:text-4xl">Ready to submit your report?</h2>
            <a href="{{ route('reports.choose') }}"
                class="inline-flex items-center justify-center rounded-full bg-gold-500 px-8 py-4 text-base font-semibold text-royal-950 transition hover:bg-gold-400">
                Submit a report
            </a>
        </div>
    </section>
@endsection
