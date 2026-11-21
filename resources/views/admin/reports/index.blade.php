<!DOCTYPE html>
<html lang="en">
<head>
    @include('admin.partials.head', ['title' => 'Reports'])
</head>

@php
    // Keep the current filters when switching report type or exporting
    $keep = array_filter([
        'q'      => $filters['q'],
        'status' => $filters['status'],
    ]);
    $activeLabel = $activeType ? $types[$activeType]['label'] : null;
    $hasFilters = $filters['q'] !== '' || $filters['status'] !== '';
@endphp

<body class="bg-paper font-sans text-ink antialiased"
      x-data="reportsPage()">

    @include('admin.partials.header')

    <main class="mx-auto max-w-7xl px-5 py-8">

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" role="status"
                 class="mb-6 rounded-xl border border-teal/20 bg-teal-soft px-4 py-3 text-sm font-medium text-teal">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-3xl font-extrabold tracking-tight">Reports</h1>
            <p class="mt-1 text-sm text-slate-500">Choose which report to view, then review, edit or delete submissions.</p>
        </div>

        {{-- Choose which report to view --}}
        <nav aria-label="Report type" class="-mx-1 mb-5 flex flex-wrap gap-2">
            @php $allCount = $typeCounts->sum(); @endphp
            <a href="{{ route('admin.reports.index', $keep) }}"
               @class([
                   'rounded-full px-4 py-2 text-sm font-semibold transition',
                   'bg-ink text-white' => ! $activeType,
                   'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-white hover:text-ink' => $activeType,
               ])
               @if (! $activeType) aria-current="page" @endif>
                All reports <span class="ml-1 font-normal opacity-70">{{ $allCount }}</span>
            </a>
            @foreach ($types as $key => $type)
                <a href="{{ route('admin.reports.index', ['type' => $key] + $keep) }}"
                   @class([
                       'rounded-full px-4 py-2 text-sm font-semibold transition',
                       'bg-ink text-white' => $activeType === $key,
                       'bg-white text-slate-600 ring-1 ring-slate-200 hover:text-ink' => $activeType !== $key,
                   ])
                   @if ($activeType === $key) aria-current="page" @endif>
                    {{ $type['label'] }} <span class="ml-1 font-normal opacity-70">{{ $typeCounts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        <section class="rounded-2xl bg-white ring-1 ring-slate-200/70">
            {{-- Search, status filter and export --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-2">
                    @if ($activeType) <input type="hidden" name="type" value="{{ $activeType }}"> @endif

                    <label for="q" class="sr-only">Search</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search name, unit, phone, department"
                           class="w-72 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">

                    <label for="status" class="sr-only">Status</label>
                    <select id="status" name="status"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
                        <option value="">Any status</option>
                        <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                        <option value="approved" @selected($filters['status'] === 'approved')>Approved</option>
                    </select>

                    <button class="rounded-xl bg-teal px-4 py-2 text-sm font-semibold text-white hover:bg-teal/90">Search</button>

                    @if ($hasFilters)
                        <a href="{{ route('admin.reports.index', array_filter(['type' => $activeType])) }}"
                           class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-paper">Clear</a>
                    @endif
                </form>

                <div class="flex items-center gap-1 text-sm font-medium">
                    <span class="mr-1 text-slate-500">Export {{ $activeLabel ? strtolower($activeLabel) . 's' : 'all' }}:</span>
                    @foreach (['excel' => 'Excel', 'csv' => 'CSV', 'pdf' => 'PDF', 'word' => 'Word'] as $format => $name)
                        <a href="{{ route('admin.reports.export', ['format' => $format] + array_filter(['type' => $activeType]) + $keep) }}"
                           class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">{{ $name }}</a>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-paper text-xs font-semibold text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Department or group</th>
                            @unless ($activeType)
                                <th class="px-4 py-3">Report</th>
                            @endunless
                            <th class="px-4 py-3">Contact</th>
                            <th class="px-4 py-3">Period</th>
                            <th class="px-4 py-3">Content</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Admin comment</th>
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $r)
                            @php
                                $submission = [
                                    'name'     => $r->fullName(),
                                    'label'    => $r->label(),
                                    'meta'     => $r->entityLabel() . ': ' . $r->entity_name . '. Unit: ' . $r->unit . '. Designation: ' . $r->designation . '.',
                                    'contact'  => $r->phone . ($r->kingschat ? ' / ' . $r->kingschat : ''),
                                    'period'   => $r->periodText(),
                                    'sections' => $r->sections(),
                                    'files'    => $r->attachments->map(fn ($f) => [
                                        'name' => $f->original_name,
                                        'kind' => $f->kind,
                                        'size' => $f->humanSize(),
                                        'url'  => route('admin.attachments.download', $f),
                                    ])->all(),
                                ];
                            @endphp
                            <tr class="align-top hover:bg-paper/60">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $r->fullName() }}</p>
                                    <p class="text-xs text-slate-500">{{ $r->unit }} / {{ $r->designation }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $r->entity_name }}
                                    <p class="text-xs text-slate-500">{{ $r->entityLabel() }}</p>
                                </td>
                                @unless ($activeType)
                                    <td class="px-4 py-3">{{ $r->config()['short'] }}</td>
                                @endunless
                                <td class="px-4 py-3">
                                    <p>{{ $r->phone }}</p>
                                    <p class="text-xs text-slate-500">{{ $r->kingschat }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $r->periodText() }}</td>
                                <td class="min-w-[16rem] max-w-xs px-4 py-3">
                                    @php $typedText = $r->isReport() ? ($r->executive_summary ?: null) : $r->citation; @endphp
                                    @if ($typedText)
                                        <p>{{ \Illuminate\Support\Str::limit($typedText, 70) }}</p>
                                    @endif

                                    @forelse ($r->attachments as $file)
                                        <a href="{{ route('admin.attachments.download', $file) }}"
                                           class="mt-1 flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs hover:border-teal hover:bg-teal-soft"
                                           title="Download {{ $file->original_name }}">
                                            <svg class="h-4 w-4 shrink-0 text-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v3a1 1 0 001 1h14a1 1 0 001-1v-3M12 4v12m0 0l-4-4m4 4l4-4"/></svg>
                                            <span class="min-w-0 flex-1 truncate font-semibold text-teal">{{ $file->original_name }}</span>
                                            <span class="shrink-0 capitalize text-slate-500">{{ $file->humanSize() }}</span>
                                        </a>
                                    @empty
                                        @unless ($typedText)
                                            <p class="text-slate-400">No file uploaded</p>
                                        @endunless
                                    @endforelse

                                    <button type="button" class="mt-1.5 text-xs font-semibold text-teal hover:underline"
                                            @click="view({{ Js::from($submission) }})">View details</button>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.reports.approval', $r) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-full px-3 py-1 text-xs font-semibold {{ $r->approved ? 'bg-teal-soft text-teal' : 'bg-gold-soft text-gold-dark' }}">
                                            {{ $r->approved ? 'Approved' : 'Pending' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="min-w-[14rem] px-4 py-3"
                                    x-data="commentBox('{{ route('admin.reports.comment', $r) }}', {{ Js::from($r->admin_comment ?? '') }})">
                                    <div x-show="!editing" class="flex items-start justify-between gap-2">
                                        <span class="text-slate-600" x-text="comment || 'No comment yet'"></span>
                                        <button @click="editing = true" class="shrink-0 text-xs font-semibold text-teal hover:underline">Edit</button>
                                    </div>
                                    <div x-show="editing" x-cloak class="space-y-2">
                                        <textarea x-model="comment" rows="2" maxlength="500" aria-label="Admin comment"
                                                  class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" placeholder="Add a comment"></textarea>
                                        <p x-show="failed" x-cloak class="text-xs text-red-600">The comment could not be saved. Try again.</p>
                                        <div class="flex gap-2 text-xs font-semibold">
                                            <button @click="save()" class="rounded-lg bg-teal px-3 py-1.5 text-white">Save</button>
                                            <button @click="remove()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-red-600">Clear</button>
                                            <button @click="editing = false" class="px-2 py-1.5 text-slate-500">Cancel</button>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $r->created_at?->format('d M Y, H:i') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a href="{{ route('admin.reports.edit', $r) }}"
                                       class="rounded-lg px-3 py-1.5 text-xs font-semibold text-teal hover:bg-teal-soft">Edit</a>
                                    <button type="button"
                                            @click="deleteTarget = { url: '{{ route('admin.reports.destroy', $r) }}', summary: {{ Js::from(strtolower($r->label()) . ' by ' . $r->name) }} }"
                                            class="rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $activeType ? 9 : 10 }}" class="px-4 py-14 text-center text-slate-500">
                                    @if ($hasFilters)
                                        Nothing matches these filters.
                                        <a href="{{ route('admin.reports.index', array_filter(['type' => $activeType])) }}" class="font-semibold text-teal hover:underline">Clear the filters</a>
                                    @elseif ($activeType)
                                        No {{ strtolower($activeLabel) }}s yet. They appear here as soon as people submit.
                                    @else
                                        No reports yet. They appear here as soon as people submit.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($reports->hasPages())
                <div class="border-t border-slate-100 p-4">{{ $reports->links() }}</div>
            @endif
        </section>
    </main>

    {{-- Full submission --}}
    <div x-show="reader.open" x-cloak @keydown.escape.window="reader.open = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 p-4" @click.self="reader.open = false">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="reader-title">
            <p class="text-sm font-semibold text-teal" x-text="reader.label"></p>
            <h3 id="reader-title" class="text-xl font-bold" x-text="reader.name"></h3>
            <p class="mt-1 text-sm text-slate-500" x-text="reader.meta"></p>
            <p class="text-sm text-slate-500" x-text="'Contact: ' + reader.contact + '. Period: ' + reader.period + '.'"></p>

            <template x-for="section in reader.sections" :key="section[0]">
                <section class="mt-6">
                    <h4 class="text-sm font-bold text-teal" x-text="section[0]"></h4>
                    <p class="mt-1 whitespace-pre-wrap leading-relaxed text-slate-700"
                       x-text="section[1] || 'Nothing was entered.'"
                       :class="section[1] ? '' : 'italic text-slate-400'"></p>
                </section>
            </template>

            <section class="mt-6">
                <h4 class="text-sm font-bold text-teal">Uploaded files</h4>
                <p x-show="!reader.files.length" class="mt-1 text-sm italic text-slate-400">No files were uploaded.</p>
                <ul class="mt-2 space-y-2">
                    <template x-for="file in reader.files" :key="file.url">
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <span class="min-w-0">
                                <span class="block truncate font-semibold" x-text="file.name"></span>
                                <span class="text-xs capitalize text-slate-500" x-text="file.kind + ' · ' + file.size"></span>
                            </span>
                            <a :href="file.url" class="shrink-0 rounded-lg bg-teal px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal/90">Download</a>
                        </li>
                    </template>
                </ul>
            </section>

            <button @click="reader.open = false" class="mt-6 rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-white">Close</button>
        </div>
    </div>

    @include('admin.partials.delete-modal')

    <script>
        function commentBox(url, initial) {
            return {
                comment: initial, editing: false, failed: false,
                send(value) {
                    this.failed = false;
                    return fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ comment: value })
                    }).then((response) => { if (!response.ok) throw new Error('save failed'); });
                },
                save() { this.send(this.comment).then(() => this.editing = false).catch(() => this.failed = true); },
                remove() { this.send(null).then(() => { this.comment = ''; this.editing = false; }).catch(() => this.failed = true); }
            };
        }

        function reportsPage() {
            return {
                deleteTarget: null,
                reader: { open: false, label: '', name: '', meta: '', contact: '', period: '', sections: [], files: [] },
                view(submission) { this.reader = { open: true, ...submission }; },
            };
        }
    </script>
</body>
</html>
