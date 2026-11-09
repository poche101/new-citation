@extends('layouts.public')

@section('title', 'Select your department or group')

@section('content')
    @include('partials.public-header')

    <main class="mx-auto max-w-5xl px-5 pb-20 pt-10">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('reports.choose') }}" class="text-royal-700 hover:underline">Choose a report</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-medium text-slate-700">{{ $type['short'] }}</li>
            </ol>
        </nav>

        <h1 class="mt-6 font-display text-4xl font-medium tracking-tight text-royal-900 sm:text-5xl">Select your department or group</h1>
        <p class="mt-4 max-w-2xl text-lg leading-relaxed text-slate-600">
            You are submitting a <span class="font-semibold text-royal-900">{{ strtolower($type['label']) }}</span>.
        </p>

        @php
            $hasAny = $departments->isNotEmpty() || $groups->isNotEmpty();
            $startOn = $departments->isEmpty() && $groups->isNotEmpty() ? 'group' : 'department';
        @endphp

        @if (! $hasAny)
            <div class="mt-10 rounded-xl border border-dashed border-royal-200 bg-white p-8 text-slate-600">
                No departments or groups have been added yet. Ask an admin to add yours, then come back to this page.
            </div>
        @else
            <div id="picker" data-start="{{ $startOn }}" class="mt-10">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div role="tablist" aria-label="Department or group" class="inline-flex rounded-full bg-royal-50 p-1">
                        @foreach (['department' => ['Departments', $departments], 'group' => ['Groups', $groups]] as $key => [$label, $items])
                            <button type="button" role="tab" id="tab-{{ $key }}" data-tab="{{ $key }}"
                                aria-controls="panel-{{ $key }}" aria-selected="{{ $key === $startOn ? 'true' : 'false' }}"
                                class="rounded-full px-5 py-2 text-sm font-semibold text-royal-700 transition aria-selected:bg-royal-800 aria-selected:text-white">
                                {{ $label }}
                                <span class="ml-1 font-normal opacity-80">{{ $items->count() }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="w-full sm:w-72">
                        <label for="pickerSearch" class="sr-only">Search by name</label>
                        <input id="pickerSearch" type="search" placeholder="Search by name" autocomplete="off"
                            class="block w-full rounded-full border border-slate-300 bg-white px-5 py-2.5 text-base shadow-sm placeholder:text-slate-400 focus:border-royal-500 focus:outline-none focus:ring-2 focus:ring-royal-500/30">
                    </div>
                </div>

                @foreach (['department' => ['Departments', $departments], 'group' => ['Groups', $groups]] as $key => [$label, $items])
                    <section id="panel-{{ $key }}" role="tabpanel" aria-labelledby="tab-{{ $key }}" data-panel="{{ $key }}" class="mt-8">
                        <h2 class="font-display text-2xl font-medium text-royal-800">{{ $label }}</h2>

                        @if ($items->isEmpty())
                            <p class="mt-4 text-slate-600">No {{ strtolower($label) }} have been added yet.</p>
                        @else
                            <ul class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($items as $item)
                                    <li data-name="{{ strtolower($item->name) }}">
                                        <a href="{{ route('reports.create', [$type['slug'], $item->id]) }}"
                                            class="flex h-full items-center rounded-lg border border-royal-100 bg-white px-4 py-3.5 font-medium text-royal-900 shadow-sm transition hover:border-royal-500 hover:bg-royal-50">
                                            {{ $item->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <p data-empty hidden class="mt-5 text-slate-600"></p>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif
    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            const picker = document.getElementById('picker');
            if (!picker) return;

            const tabs = picker.querySelectorAll('[data-tab]');
            const panels = picker.querySelectorAll('[data-panel]');
            const search = document.getElementById('pickerSearch');
            let active = picker.dataset.start;

            function render() {
                const term = search.value.trim().toLowerCase();

                tabs.forEach((tab) => tab.setAttribute('aria-selected', tab.dataset.tab === active ? 'true' : 'false'));

                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.panel !== active;

                    let shown = 0;
                    panel.querySelectorAll('li[data-name]').forEach((li) => {
                        const match = !term || li.dataset.name.includes(term);
                        li.hidden = !match;
                        if (match) shown++;
                    });

                    const empty = panel.querySelector('[data-empty]');
                    if (empty) {
                        const none = shown === 0;
                        empty.hidden = !none;
                        empty.textContent = none ? 'Nothing matches "' + search.value.trim() + '". Check the spelling or try the other tab.' : '';
                    }
                });
            }

            tabs.forEach((tab) => tab.addEventListener('click', () => { active = tab.dataset.tab; render(); }));
            search.addEventListener('input', render);
            render();
        })();
    </script>
@endpush
