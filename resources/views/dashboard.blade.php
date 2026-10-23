<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - CELZ5 Citation</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo2.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] },
            colors: { ink: '#14213D', paper: '#F4F6F9', teal: { DEFAULT: '#0F766E', soft: '#E3F2F0' }, gold: { DEFAULT: '#B8893B', soft: '#F7EEDC' } }
        } } }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>

@php
    $approved = $stats['approved'] ?? 0;
    $pending = $stats['pending'] ?? 0;
    $total = max($approved + $pending, 1);
    $rate = round($approved / $total * 100);
@endphp

<body class="bg-paper font-sans text-ink antialiased"
      x-data="dashboard()" x-init="initCharts()">

    {{-- Top bar + menu --}}
    @include('admin.partials.header')

    <main class="mx-auto max-w-7xl px-5 py-8">

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-6 rounded-xl border border-teal/20 bg-teal-soft px-4 py-3 text-sm font-medium text-teal">
                {{ session('success') }}
            </div>
        @endif

        {{-- Heading + primary action --}}
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">Citations overview</h1>
                <p class="mt-1 text-sm text-slate-500">Track submissions across departments and groups, and review what needs approval.</p>
            </div>
            <button @click="deptModal = true"
                    class="rounded-xl bg-teal px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal focus-visible:ring-offset-2">
                Add department or group
            </button>
        </div>

        {{-- Stats --}}
        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Departments', $departmentsCount, 'text-ink'],
                ['Groups', $groupsCount, 'text-ink'],
                ['Citations submitted', $citationsCount, 'text-ink'],
                ['Approval rate', $rate . '%', 'text-teal'],
            ] as [$label, $value, $color])
                <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/70">
                    <p class="text-sm text-slate-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums {{ $color }}">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        {{-- Analytics --}}
        <section class="mt-4 grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/70 lg:col-span-2">
                <h2 class="font-semibold">Submissions, last 30 days</h2>
                <p class="mb-4 text-sm text-slate-500">Departments and groups combined</p>
                <div class="h-64"><canvas id="trendChart"></canvas></div>
            </div>
            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/70">
                <h2 class="font-semibold">Review status</h2>
                <p class="mb-4 text-sm text-slate-500">{{ $approved }} approved, {{ $pending }} pending</p>
                <div class="relative mx-auto h-52 w-52">
                    <canvas id="statusChart"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-extrabold">{{ $rate }}%</span>
                        <span class="text-xs text-slate-500">approved</span>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/70 lg:col-span-3">
                <h2 class="font-semibold">Citations by department</h2>
                <p class="mb-4 text-sm text-slate-500">Which departments submit the most</p>
                <div class="h-72"><canvas id="deptChart"></canvas></div>
            </div>
        </section>

        {{-- Tables --}}
        <section class="mt-8 rounded-2xl bg-white ring-1 ring-slate-200/70">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4">
                <div class="inline-flex rounded-xl bg-paper p-1 text-sm font-semibold">
                    <button @click="tab = 'dept'" :class="tab === 'dept' ? 'bg-white shadow-sm text-ink' : 'text-slate-500'" class="rounded-lg px-4 py-1.5">Department citations</button>
                    <button @click="tab = 'group'" :class="tab === 'group' ? 'bg-white shadow-sm text-ink' : 'text-slate-500'" class="rounded-lg px-4 py-1.5">Group citations</button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="search" x-model="q" placeholder="Search this table"
                           class="w-56 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
                    <template x-if="tab === 'dept'">
                        <div class="flex gap-1 text-sm font-medium">
                            <a href="{{ route('departments.exportExcel') }}" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">Excel</a>
                            <a href="{{ route('departments.exportCSV') }}" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">CSV</a>
                            <a href="{{ route('departments.exportPDF') }}" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">PDF</a>
                            <a href="{{ route('departments.exportWord') }}" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">Word</a>
                        </div>
                    </template>
                    <template x-if="tab === 'group'">
                        <div class="flex gap-1 text-sm font-medium">
                            <a href="{{ route('groups.exportExcel') }}" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">Excel</a>
                            <a href="{{ route('groups.exportCSV') }}" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">CSV</a>
                            <a href="{{ route('groups.exportPDF') }}" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">PDF</a>
                            <a href="{{ route('groups.exportWord') }}" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-paper">Word</a>
                        </div>
                    </template>
                </div>
            </div>

            @foreach ([
                ['dept', $departmentCitations, 'admin.departmentCitation'],
                ['group', $groupCitations, 'admin.groupCitation'],
            ] as [$key, $rows, $routeBase])
                <div x-show="tab === '{{ $key }}'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm" data-table="{{ $key }}">
                        <thead class="bg-paper text-xs font-semibold text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">{{ $key === 'dept' ? 'Department' : 'Group' }}</th>
                                <th class="px-4 py-3">Contact</th>
                                <th class="px-4 py-3">Period</th>
                                <th class="px-4 py-3">Citation</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Admin comment</th>
                                <th class="px-4 py-3">Submitted</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($rows as $c)
                                @php
                                    $name = $key === 'dept' ? $c->fullname : $c->name;
                                    $body = $key === 'dept' ? $c->citation : $c->description;
                                    $period = $key === 'dept'
                                        ? ($c->period ?? 'N/A')
                                        : (($c->period_from?->format('d M Y') ?? 'N/A') . ' to ' . ($c->period_to?->format('d M Y') ?? 'N/A'));
                                @endphp
                                <tr x-show="match($el)" class="align-top hover:bg-paper/60">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold">{{ $c->title }} {{ $name ?? 'N/A' }}</p>
                                        <p class="text-xs text-slate-500">{{ $c->unit ?? 'N/A' }} / {{ $c->designation ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-4 py-3">{{ ($key === 'dept' ? $c->department : $c->group_name) ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <p>{{ $c->phone ?? 'N/A' }}</p>
                                        <p class="text-xs text-slate-500">{{ $c->kingschat ?? '' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $period }}</td>
                                    <td class="max-w-xs px-4 py-3">
                                        <p>{{ Str::limit($body ?? 'N/A', 60) }}</p>
                                        @if (strlen($body ?? '') > 60)
                                            <button type="button" class="mt-1 text-xs font-semibold text-teal hover:underline"
                                                    @click="open({{ Js::from($name) }}, {{ Js::from($body) }})">Read full citation</button>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route($routeBase . '.toggleApproval', $c->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="rounded-full px-3 py-1 text-xs font-semibold {{ $c->approved ? 'bg-teal-soft text-teal' : 'bg-gold-soft text-gold' }}">
                                                {{ $c->approved ? 'Approved' : 'Pending' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="min-w-[14rem] px-4 py-3"
                                        x-data="commentBox('{{ route($routeBase . '.comment', $c->id) }}', {{ Js::from($c->admin_comment ?? '') }})">
                                        <div x-show="!editing" class="flex items-start justify-between gap-2">
                                            <span class="text-slate-600" x-text="comment || 'No comment yet'"></span>
                                            <button @click="editing = true" class="shrink-0 text-xs font-semibold text-teal hover:underline">Edit</button>
                                        </div>
                                        <div x-show="editing" x-cloak class="space-y-2">
                                            <textarea x-model="comment" rows="2" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20" placeholder="Add a comment"></textarea>
                                            <div class="flex gap-2 text-xs font-semibold">
                                                <button @click="save()" class="rounded-lg bg-teal px-3 py-1.5 text-white">Save</button>
                                                <button @click="remove()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-red-600">Delete</button>
                                                <button @click="editing = false" class="px-2 py-1.5 text-slate-500">Cancel</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $c->created_at?->format('d M Y, H:i') ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">No {{ $key === 'dept' ? 'department' : 'group' }} citations yet. They appear here as soon as people submit.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="border-t border-slate-100 p-4">{{ $rows->links() }}</div>
                </div>
            @endforeach
        </section>
    </main>

    {{-- Citation reader --}}
    <div x-show="reader.open" x-cloak @keydown.escape.window="reader.open = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 p-4" @click.self="reader.open = false">
        <div class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-bold" x-text="reader.name"></h3>
            <p class="mt-3 whitespace-pre-wrap leading-relaxed text-slate-700" x-text="reader.body"></p>
            <button @click="reader.open = false" class="mt-5 rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-white">Close</button>
        </div>
    </div>

    {{-- Add department or group --}}
    <div x-show="deptModal" x-cloak @keydown.escape.window="deptModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 p-4" @click.self="deptModal = false">
        <form method="POST" action="{{ route('admin.departments.store') }}"
              class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            @csrf
            <h3 class="text-lg font-bold">Add department or group</h3>
            <p class="mt-1 text-sm text-slate-500">It becomes available on the matching section of the submission form straight away.</p>

            <fieldset class="mt-5">
                <legend class="block text-sm font-semibold">Category</legend>
                <div class="mt-1 grid grid-cols-2 gap-2">
                    @foreach (['department' => 'Department', 'group' => 'Group'] as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="{{ $value }}" class="peer sr-only"
                                   @checked(old('type', 'department') === $value)>
                            <span class="block rounded-xl border border-slate-200 px-3 py-2.5 text-center text-sm font-semibold text-slate-500 transition peer-checked:border-teal peer-checked:bg-teal-soft peer-checked:text-teal peer-focus-visible:ring-2 peer-focus-visible:ring-teal/40">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </fieldset>

            <label for="dept-name" class="mt-5 block text-sm font-semibold">Name</label>
            <input id="dept-name" name="name" value="{{ old('name') }}" required maxlength="120" x-ref="deptName"
                   x-effect="if (deptModal) $nextTick(() => $refs.deptName.focus())"
                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/20">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

            <div class="mt-6 flex justify-end gap-2 text-sm font-semibold">
                <button type="button" @click="deptModal = false" class="rounded-xl px-4 py-2 text-slate-500 hover:bg-paper">Cancel</button>
                <button class="rounded-xl bg-teal px-5 py-2 text-white hover:bg-teal/90">Add</button>
            </div>
        </form>
    </div>

    <script>
        const chartData = {
            trend: @json($stats['trend'] ?? ['labels' => [], 'values' => []]),
            depts: @json($stats['byDepartment'] ?? ['labels' => [], 'values' => []]),
            approved: {{ $approved }},
            pending: {{ $pending }},
        };

        function commentBox(url, initial) {
            return {
                comment: initial, editing: false,
                send(value) {
                    return fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ comment: value })
                    });
                },
                save() { this.send(this.comment).then(() => this.editing = false); },
                remove() { this.send(null).then(() => { this.comment = ''; this.editing = false; }); }
            };
        }

        function dashboard() {
            return {
                tab: 'dept', q: '', deptModal: {{ $errors->has('name') || $errors->has('type') ? 'true' : 'false' }},
                reader: { open: false, name: '', body: '' },
                open(name, body) { this.reader = { open: true, name, body }; },
                match(row) { return !this.q || row.innerText.toLowerCase().includes(this.q.toLowerCase()); },
                initCharts() {
                    Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
                    Chart.defaults.color = '#64748b';
                    const grid = { color: '#eef1f5' };
                    new Chart(document.getElementById('trendChart'), {
                        type: 'line',
                        data: { labels: chartData.trend.labels, datasets: [{ data: chartData.trend.values, borderColor: '#0F766E', backgroundColor: 'rgba(15,118,110,.10)', fill: true, tension: .35, pointRadius: 0, pointHoverRadius: 5, borderWidth: 2.5 }] },
                        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } }, y: { beginAtZero: true, grid, ticks: { precision: 0 } } } }
                    });
                    new Chart(document.getElementById('statusChart'), {
                        type: 'doughnut',
                        data: { labels: ['Approved', 'Pending'], datasets: [{ data: [chartData.approved, chartData.pending], backgroundColor: ['#0F766E', '#B8893B'], borderWidth: 0 }] },
                        options: { cutout: '74%', plugins: { legend: { display: false } } }
                    });
                    new Chart(document.getElementById('deptChart'), {
                        type: 'bar',
                        data: { labels: chartData.depts.labels, datasets: [{ data: chartData.depts.values, backgroundColor: '#14213D', borderRadius: 6, maxBarThickness: 28 }] },
                        options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid, ticks: { precision: 0 } }, y: { grid: { display: false } } } }
                    });
                }
            };
        }
    </script>
</body>
</html>
