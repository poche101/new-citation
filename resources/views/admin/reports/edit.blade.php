<!DOCTYPE html>
<html lang="en">
<head>
    @include('admin.partials.head', ['title' => 'Edit report'])
</head>

@php
    $isReport = $report->isReport();
    $maxMb = (int) round($uploads['max_size_kb'] / 1024);
    $allExtensions = collect($uploads['types'])->flatten()->all();
    $currentCount = $report->attachments->count();
@endphp

<body class="bg-paper font-sans text-ink antialiased" x-data="{ deleteTarget: null }">

    @include('admin.partials.header')

    <main class="mx-auto max-w-3xl px-5 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <a href="{{ route('admin.reports.index', ['type' => $report->type]) }}" class="font-semibold text-teal hover:underline">Reports</a>
            <span class="mx-1" aria-hidden="true">/</span>
            <span>Edit</span>
        </nav>

        <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-teal">{{ $report->label() }}</p>
                <h1 class="text-3xl font-extrabold tracking-tight">{{ $report->fullName() }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $report->entityLabel() }}: {{ $report->entity_name }}.
                    Submitted {{ $report->created_at?->format('d M Y, H:i') }}.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert">
                <p class="font-semibold">The changes have not been saved. Please fix the following:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="editForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.reports.update', $report) }}"
              class="mt-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Who submitted it --}}
            <section class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/70">
                <h2 class="font-bold">Submitted by</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="title" label="Title" :value="$report->title" :required="false" />
                    <x-admin.input name="name" label="Full name" :value="$report->name" />
                    <x-admin.input name="unit" label="Unit" :value="$report->unit" />
                    <x-admin.input name="designation" label="Designation" :value="$report->designation" />
                    <x-admin.input name="kingschat" label="Kingschat handle" :value="$report->kingschat" />
                    <x-admin.input name="phone" label="Phone number" type="tel" :value="$report->phone" />
                </div>
            </section>

            {{-- Where it belongs and when --}}
            <section class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/70">
                <h2 class="font-bold">Department or group, and period</h2>

                <div class="mt-4">
                    <label for="entity_id" class="mb-1 block text-sm font-semibold">Department or group</label>
                    <select id="entity_id" name="entity_id"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
                        <option value="">Keep current: {{ $report->entity_name }} ({{ strtolower($report->entityLabel()) }})</option>
                        <optgroup label="Departments">
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" @selected((string) old('entity_id') === (string) $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Groups">
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}" @selected((string) old('entity_id') === (string) $g->id)>{{ $g->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    @error('entity_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="period_from" label="From (first day)" type="date" :value="$report->period_from?->format('Y-m-d')" />
                    <x-admin.input name="period_to" label="To (last day)" type="date" :value="$report->period_to?->format('Y-m-d')" />
                </div>
                <p id="periodSummary" class="mt-3 rounded-lg bg-paper px-3 py-2 text-sm font-medium text-slate-600" aria-live="polite"></p>
            </section>

            {{-- Content --}}
            <section class="space-y-5 rounded-2xl bg-white p-6 ring-1 ring-slate-200/70">
                <h2 class="font-bold">{{ $isReport ? 'Report' : 'Citation' }}</h2>

                @if ($isReport)
                    <p class="text-sm text-slate-600">Annual reports are submitted as uploaded files. Manage the file under "Uploaded files" below.</p>
                    @foreach ($report->sections() as [$heading, $text])
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ $heading }} <span class="font-normal text-slate-400">(typed before the text boxes were removed)</span></p>
                            <p class="mt-1 whitespace-pre-wrap rounded-lg bg-paper px-3 py-2 text-sm text-slate-700">{{ $text }}</p>
                        </div>
                    @endforeach
                @else
                    <x-admin.textarea name="citation" label="Citation" :max="$words['citation']" rows="7" :value="$report->citation" />
                @endif
            </section>

            {{-- Files --}}
            <section class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/70">
                <h2 class="font-bold">Uploaded files</h2>

                @if ($report->attachments->isEmpty())
                    <p class="mt-2 text-sm text-slate-500">No files were uploaded with this submission.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach ($report->attachments as $file)
                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold">{{ $file->original_name }}</span>
                                    <span class="text-xs capitalize text-slate-500">{{ $file->kind }} &middot; {{ $file->humanSize() }}</span>
                                </span>
                                <span class="flex items-center gap-4">
                                    <a href="{{ route('admin.attachments.download', $file) }}" class="text-xs font-semibold text-teal hover:underline">Download</a>
                                    <label class="flex items-center gap-2 text-xs font-semibold text-red-600">
                                        <input type="checkbox" name="remove_attachments[]" value="{{ $file->id }}" data-remove-file
                                               class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                                               @checked(in_array($file->id, array_map('intval', (array) old('remove_attachments', []))))>
                                        Remove when saved
                                    </label>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-5">
                    <label for="attachments" class="mb-1 block text-sm font-semibold">Add files</label>
                    <p class="mb-2 text-xs text-slate-500">
                        Documents and spreadsheets. Up to {{ $uploads['max_files'] }} files in total, {{ $maxMb }} MB each.
                    </p>
                    <input id="attachments" type="file" name="attachments[]" multiple
                           accept=".{{ implode(',.', $allExtensions) }}"
                           data-max-files="{{ $uploads['max_files'] }}"
                           data-max-bytes="{{ $uploads['max_size_kb'] * 1024 }}"
                           data-extensions="{{ implode(',', $allExtensions) }}"
                           class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-teal file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-teal/90">
                    <p id="attachmentError" hidden class="mt-2 text-sm font-medium text-red-600" role="alert"></p>
                </div>
            </section>

            {{-- Review --}}
            <section class="space-y-4 rounded-2xl bg-white p-6 ring-1 ring-slate-200/70">
                <h2 class="font-bold">Review</h2>

                <label class="flex items-center gap-3 text-sm font-semibold">
                    <input type="checkbox" name="approved" value="1" @checked(session()->hasOldInput() ? old('approved') : $report->approved)
                           class="h-4 w-4 rounded border-slate-300 text-teal focus:ring-teal">
                    Approved
                </label>

                <x-admin.textarea name="admin_comment" label="Admin comment" rows="3" :value="$report->admin_comment" :required="false" />
            </section>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <button type="button"
                        @click="deleteTarget = { url: '{{ route('admin.reports.destroy', $report) }}', summary: {{ Js::from(strtolower($report->label()) . ' by ' . $report->name) }} }"
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Delete this submission</button>

                <div class="flex items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('admin.reports.index', ['type' => $report->type]) }}" class="rounded-xl px-4 py-2.5 text-slate-600 hover:bg-white">Cancel</a>
                    <button id="saveBtn" type="submit"
                            class="rounded-xl bg-teal px-6 py-2.5 text-white hover:bg-teal/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal focus-visible:ring-offset-2 disabled:opacity-60">Save changes</button>
                </div>
            </div>
        </form>
    </main>

    @include('admin.partials.delete-modal')

    <script>
        (function () {
            const form = document.getElementById('editForm');

            /* Word counters */
            const count = (t) => (t.trim().match(/\S+/g) || []).length;
            form.querySelectorAll('[data-word-limit]').forEach((box) => {
                const max = parseInt(box.dataset.wordLimit, 10);
                const out = form.querySelector('[data-word-counter="' + box.id + '"]');
                const update = () => {
                    const n = count(box.value);
                    const over = n > max;
                    out.textContent = n.toLocaleString() + ' / ' + max.toLocaleString() + ' words' + (over ? ' (' + (n - max).toLocaleString() + ' over the limit)' : '');
                    out.classList.toggle('text-red-600', over);
                    out.classList.toggle('font-semibold', over);
                    out.classList.toggle('text-slate-500', !over);
                    box.setCustomValidity(over ? 'Please shorten this to ' + max.toLocaleString() + ' words or fewer.' : '');
                };
                box.addEventListener('input', update);
                update();
            });

            /* Dates */
            const from = document.getElementById('period_from');
            const to = document.getElementById('period_to');
            const summary = document.getElementById('periodSummary');
            const readable = (iso) => {
                const [y, m, d] = iso.split('-').map(Number);
                return new Date(y, m - 1, d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
            };
            const updatePeriod = () => {
                const bad = from.value && to.value && to.value < from.value;
                to.setCustomValidity(bad ? 'The "To" date must be the same as or later than the "From" date.' : '');
                summary.classList.toggle('text-red-700', bad);
                summary.classList.toggle('text-slate-600', !bad);
                summary.textContent = bad
                    ? 'The "To" date is earlier than the "From" date. Please change one of them.'
                    : (from.value && to.value ? 'Covers ' + readable(from.value) + ' to ' + readable(to.value) + '.' : 'Choose both a "From" and a "To" date.');
            };
            from.addEventListener('input', updatePeriod);
            to.addEventListener('input', updatePeriod);
            updatePeriod();

            /* Upload checks (count includes files that stay attached) */
            const input = document.getElementById('attachments');
            const error = document.getElementById('attachmentError');
            const maxFiles = parseInt(input.dataset.maxFiles, 10);
            const maxBytes = parseInt(input.dataset.maxBytes, 10);
            const allowed = input.dataset.extensions.split(',');
            const existing = {{ $currentCount }};

            const check = () => {
                const files = Array.from(input.files);
                const removing = form.querySelectorAll('[data-remove-file]:checked').length;
                const problems = [];

                if (existing - removing + files.length > maxFiles) {
                    problems.push('A submission can have at most ' + maxFiles + ' files. Remove some, or choose fewer.');
                }
                files.forEach((file) => {
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!allowed.includes(ext)) problems.push('"' + file.name + '" is not an allowed file type.');
                    if (file.size > maxBytes) problems.push('"' + file.name + '" is larger than ' + Math.round(maxBytes / 1048576) + ' MB.');
                });

                error.hidden = problems.length === 0;
                error.textContent = problems.join(' ');
                input.setCustomValidity(problems.length ? problems[0] : '');
            };

            input.addEventListener('change', check);
            form.querySelectorAll('[data-remove-file]').forEach((box) => box.addEventListener('change', check));
            check();

            form.addEventListener('submit', () => {
                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.textContent = input.files.length ? 'Uploading...' : 'Saving...';
            });
            window.addEventListener('pageshow', () => {
                const btn = document.getElementById('saveBtn');
                btn.disabled = false;
                btn.textContent = 'Save changes';
            });
        })();
    </script>
</body>
</html>
