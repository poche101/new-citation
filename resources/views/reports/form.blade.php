@extends('layouts.public')

@section('title', $type['label'])

@section('content')
    @include('partials.public-header')

    @php
        $isReport = $type['kind'] === 'report';
        $isUnit = $type['scope'] === 'unit';
        $maxMb = (int) round($uploads['max_size_kb'] / 1024);
        $allExtensions = collect($uploads['types'])->flatten()->all();

        // Fields on each step, so a failed submit can reopen the right step
        $step1Fields = ['title', 'name', 'unit', 'designation', 'kingschat', 'phone'];
        $step2Fields = ['period_from', 'period_to', 'citation'];
        $errorStep = $errors->hasAny($step1Fields) ? 1 : ($errors->hasAny($step2Fields) ? 2 : ($errors->hasAny(['attachments']) || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'attachments')) ? 3 : 0));
    @endphp

    <main class="mx-auto max-w-3xl px-5 pb-20 pt-10">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('reports.choose') }}" class="text-royal-700 hover:underline">Choose a report</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('reports.entities', $type['slug']) }}" class="text-royal-700 hover:underline">{{ $type['short'] }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-medium text-slate-700">{{ $department->name }}</li>
            </ol>
        </nav>

        <h1 class="mt-6 font-display text-4xl font-medium tracking-tight text-royal-900 sm:text-5xl">{{ $type['label'] }}</h1>
        <p class="mt-3 max-w-2xl text-lg leading-relaxed text-slate-600">{{ $type['description'] }}</p>

        <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl border border-royal-100 bg-white px-5 py-4">
            <p class="text-slate-600">
                {{ $department->type === 'group' ? 'Group' : 'Department' }}:
                <span class="font-semibold text-royal-900">{{ $department->name }}</span>
            </p>
            <a href="{{ route('reports.entities', $type['slug']) }}" class="text-sm font-medium text-royal-700 underline underline-offset-4 hover:text-royal-900">
                Choose a different {{ $department->type === 'group' ? 'group' : 'department' }}
            </a>
        </div>

        {{-- Progress --}}
        <div class="mt-10" aria-label="Progress">
            <p id="stepProgressLabel" class="text-sm font-semibold text-royal-800">Step 1 of 3: Your details</p>
            <div class="mt-2 h-1.5 w-full rounded-full bg-royal-100">
                <div id="stepProgressBar" class="h-full rounded-full bg-gold-500 transition-all duration-300" style="width: 33.33%"></div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mt-8 rounded-xl border border-red-200 bg-red-50 p-5 text-red-900" role="alert">
                <p class="font-semibold">Your submission has not been sent yet. Please fix the following and submit again:</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="reportForm" method="POST" enctype="multipart/form-data"
            action="{{ route('reports.store', [$type['slug'], $department->id]) }}"
            data-error-step="{{ $errorStep }}"
            class="mt-8 rounded-2xl border border-royal-100 bg-white p-6 shadow-sm sm:p-9">
            @csrf

            {{-- Step 1: your details --}}
            <div class="form-step space-y-6" data-step-name="Your details">
                <h2 class="font-display text-2xl font-medium text-royal-900">Your details</h2>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-report.input name="title" label="Title" placeholder="Bro, Sis, Pastor, Deacon" :required="false" autocomplete="honorific-prefix" />
                    <x-report.input name="name" label="Full name" placeholder="John Doe" autocomplete="name" />
                    <x-report.input name="unit" label="Your unit" placeholder="For example, Ushering" />
                    <x-report.input name="designation" label="Your designation" placeholder="For example, Unit leader" />
                    <x-report.input name="kingschat" label="Kingschat handle" placeholder="@handle" />
                    <x-report.input name="phone" label="Phone number" type="tel" placeholder="0803 000 0000" autocomplete="tel" />
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" onclick="nextStep()"
                        class="rounded-full bg-royal-800 px-7 py-3 font-semibold text-white transition hover:bg-royal-700">Continue</button>
                </div>
            </div>

            {{-- Step 2: period and content --}}
            <div class="form-step hidden space-y-8" data-step-name="{{ $isReport ? 'Period covered' : 'Your citation' }}">
                <h2 class="font-display text-2xl font-medium text-royal-900">{{ $isReport ? 'Period covered' : 'Your citation' }}</h2>

                <fieldset>
                    <legend class="text-sm font-semibold text-royal-900">
                        {{ $isReport ? 'Period this report covers' : 'Period this citation covers' }}
                    </legend>
                    <p class="mt-1 text-sm text-slate-500">Choose the first and last day. We have filled in the whole of {{ now()->year }}, so change them if yours is different.</p>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <x-report.input name="period_from" label="From (first day)" type="date" :value="$defaultFrom" />
                        <x-report.input name="period_to" label="To (last day)" type="date" :value="$defaultTo" />
                    </div>

                    <p id="periodSummary" class="mt-3 rounded-lg bg-royal-50 px-4 py-2.5 text-sm font-medium text-royal-800" aria-live="polite"></p>
                </fieldset>

                @unless ($isReport)
                    <x-report.textarea name="citation" label="Citation"
                        :max="$words['citation']" rows="8"
                        :hint="$isUnit
                            ? 'Recognise your unit or department and the work it did this year.'
                            : 'Recognise your work and what it achieved this year.'" />
                @endunless

                <div class="flex justify-between pt-2">
                    <button type="button" onclick="prevStep()"
                        class="rounded-full border border-royal-200 px-7 py-3 font-semibold text-royal-800 transition hover:bg-royal-50">Back</button>
                    <button type="button" onclick="nextStep()"
                        class="rounded-full bg-royal-800 px-7 py-3 font-semibold text-white transition hover:bg-royal-700">Continue</button>
                </div>
            </div>

            {{-- Step 3: files, then submit --}}
            <div class="form-step hidden space-y-6" data-step-name="{{ $isReport ? 'Upload your report' : 'Attach files' }}">
                <div>
                    <h2 class="font-display text-2xl font-medium text-royal-900">{{ $isReport ? 'Upload your report' : 'Attach files' }}</h2>
                    <p class="mt-1 text-slate-600">
                        @if ($isReport)
                            Attach your report as a document, spreadsheet, video or audio file. This is required.
                        @else
                            Optional. Add a document, spreadsheet, video or audio file that supports your submission.
                        @endif
                    </p>
                </div>

                <div class="rounded-xl border-2 border-dashed border-royal-200 bg-royal-50/60 p-6">
                    <label for="attachments" class="block font-semibold text-royal-900">Choose files</label>
                    <p class="mt-1 text-sm text-slate-600">
                        Documents (PDF, Word, PowerPoint), spreadsheets (Excel, CSV), video (MP4, MOV) or audio (MP3, WAV, M4A).
                        Up to {{ $uploads['max_files'] }} files, {{ $maxMb }} MB each.
                    </p>

                    <input id="attachments" type="file" name="attachments[]" multiple @required($isReport)
                        accept=".{{ implode(',.', $allExtensions) }}"
                        data-max-files="{{ $uploads['max_files'] }}"
                        data-max-bytes="{{ $uploads['max_size_kb'] * 1024 }}"
                        data-extensions="{{ implode(',', $allExtensions) }}"
                        class="mt-4 block w-full text-sm text-slate-700 file:mr-4 file:rounded-full file:border-0 file:bg-royal-800 file:px-5 file:py-2.5 file:font-semibold file:text-white hover:file:bg-royal-700">

                    <ul id="attachmentList" class="mt-4 space-y-2 text-sm"></ul>
                    <p id="attachmentError" hidden class="mt-3 text-sm font-medium text-red-700" role="alert"></p>

                    @if ($errorStep === 3 || $errors->any())
                        <p class="mt-3 text-sm text-slate-600">If you had chosen files before, please choose them again. Browsers cannot keep files after the page reloads.</p>
                    @endif
                </div>

                <div class="flex justify-between pt-2">
                    <button type="button" onclick="prevStep()"
                        class="rounded-full border border-royal-200 px-7 py-3 font-semibold text-royal-800 transition hover:bg-royal-50">Back</button>
                    <button id="submitBtn" type="submit"
                        class="rounded-full bg-gold-500 px-8 py-3 font-semibold text-royal-950 transition hover:bg-gold-400 disabled:cursor-not-allowed disabled:opacity-60">Submit {{ strtolower($type['short']) }}</button>
                </div>
            </div>
        </form>
    </main>
@endsection

@push('scripts')
    @include('partials.report-form-scripts', ['submitLabel' => 'Submit ' . strtolower($type['short'])])
@endpush
