<!DOCTYPE html>
<html lang="en">
<head>
    @include('admin.partials.head', ['title' => 'Dashboard'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>

@php
    $rate = $total > 0 ? round($approved / $total * 100) : 0;
@endphp

<body class="bg-paper font-sans text-ink antialiased" x-data x-init="initCharts()">

    @include('admin.partials.header')

    <main class="mx-auto max-w-7xl px-5 py-8">

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" role="status"
                 class="mb-6 rounded-xl border border-teal/20 bg-teal-soft px-4 py-3 text-sm font-medium text-teal">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-500">Choose a report to view its submissions, or see how things are going overall.</p>
            </div>
            <a href="{{ route('admin.reports.index') }}"
               class="rounded-xl bg-teal px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal focus-visible:ring-offset-2">
                View all reports
            </a>
        </div>

        {{-- One card per report: click to view it --}}
        <section aria-label="Reports" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($types as $key => $type)
                <a href="{{ route('admin.reports.index', ['type' => $key]) }}"
                   class="group rounded-2xl bg-white p-5 ring-1 ring-slate-200/70 transition hover:ring-teal focus:outline-none focus-visible:ring-2 focus-visible:ring-teal">
                    <p class="text-sm font-semibold text-slate-600">{{ $type['label'] }}</p>
                    <p class="mt-3 text-4xl font-extrabold tabular-nums">{{ $typeCounts[$key] ?? 0 }}</p>
                    <p class="mt-3 text-sm font-semibold text-teal group-hover:underline">View submissions</p>
                </a>
            @endforeach
        </section>

        {{-- Totals --}}
        <section aria-label="Totals" class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Submissions in total', $total, 'text-ink'],
                ['Approved', $approved, 'text-teal'],
                ['Pending review', $pending, 'text-gold-dark'],
                ['Departments and groups', $departmentsCount . ' and ' . $groupsCount, 'text-ink'],
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
                <p class="mb-4 text-sm text-slate-500">All reports and citations combined</p>
                <div class="h-64"><canvas id="trendChart" aria-label="Submissions per day for the last 30 days" role="img"></canvas></div>
            </div>
            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/70">
                <h2 class="font-semibold">Review status</h2>
                <p class="mb-4 text-sm text-slate-500">{{ $approved }} approved, {{ $pending }} pending</p>
                <div class="relative mx-auto h-52 w-52">
                    <canvas id="statusChart" aria-label="Approved compared with pending" role="img"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-extrabold">{{ $rate }}%</span>
                        <span class="text-xs text-slate-500">approved</span>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-200/70 lg:col-span-3">
                <h2 class="font-semibold">Submissions by department or group</h2>
                <p class="mb-4 text-sm text-slate-500">The ten with the most submissions</p>
                @if (count($chart['byEntity']['labels']))
                    <div class="h-72"><canvas id="entityChart" aria-label="Submissions per department or group" role="img"></canvas></div>
                @else
                    <p class="py-10 text-center text-sm text-slate-500">Nothing to show yet. This fills in as people submit.</p>
                @endif
            </div>
        </section>

        {{-- Latest --}}
        <section class="mt-4 rounded-2xl bg-white ring-1 ring-slate-200/70">
            <div class="flex items-center justify-between border-b border-slate-100 p-4">
                <h2 class="font-semibold">Latest submissions</h2>
                <a href="{{ route('admin.reports.index') }}" class="text-sm font-semibold text-teal hover:underline">View all</a>
            </div>

            @forelse ($recent as $r)
                <a href="{{ route('admin.reports.index', ['type' => $r->type, 'q' => $r->name]) }}"
                   class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3 text-sm last:border-b-0 hover:bg-paper/60">
                    <span>
                        <span class="font-semibold">{{ $r->fullName() }}</span>
                        <span class="text-slate-500">: {{ $r->config()['short'] }}, {{ $r->entity_name }}</span>
                    </span>
                    <span class="text-slate-500">{{ $r->created_at?->diffForHumans() }}</span>
                </a>
            @empty
                <p class="px-4 py-12 text-center text-sm text-slate-500">No submissions yet. They appear here as soon as people submit.</p>
            @endforelse
        </section>
    </main>

    <script>
        const chartData = @json($chart);

        function initCharts() {
            if (typeof Chart === 'undefined') return;
            Chart.defaults.font.family = '"Instrument Sans", sans-serif';
            Chart.defaults.color = '#64748b';
            const grid = { color: '#eef1f5' };

            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: { labels: chartData.trend.labels, datasets: [{ data: chartData.trend.values, borderColor: '#143A94', backgroundColor: 'rgba(20,58,148,.10)', fill: true, tension: .35, pointRadius: 0, pointHoverRadius: 5, borderWidth: 2.5 }] },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } }, y: { beginAtZero: true, grid, ticks: { precision: 0 } } } }
            });

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: { labels: ['Approved', 'Pending'], datasets: [{ data: [chartData.approved, chartData.pending], backgroundColor: ['#143A94', '#E5A913'], borderWidth: 0 }] },
                options: { cutout: '74%', plugins: { legend: { display: false } } }
            });

            const entity = document.getElementById('entityChart');
            if (entity) {
                new Chart(entity, {
                    type: 'bar',
                    data: { labels: chartData.byEntity.labels, datasets: [{ data: chartData.byEntity.values, backgroundColor: '#0B1633', borderRadius: 6, maxBarThickness: 28 }] },
                    options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid, ticks: { precision: 0 } }, y: { grid: { display: false } } } }
                });
            }
        }
    </script>
</body>
</html>
