<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Report;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Overview: a card per report type (click to view that report), review
     * status, submissions over time, and the most recent submissions.
     */
    public function index()
    {
        $typeCounts = Report::selectRaw('type, COUNT(*) as n')->groupBy('type')->pluck('n', 'type');

        $approved = Report::where('approved', true)->count();
        $pending  = Report::where('approved', false)->count();

        // Submissions over the last 30 days
        $days = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        $perDay = Report::where('created_at', '>=', $days->first())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as n')
            ->groupBy('d')
            ->pluck('n', 'd');

        // Departments and groups with the most submissions
        $byEntity = Report::selectRaw('entity_name, COUNT(*) as n')
            ->groupBy('entity_name')
            ->orderByDesc('n')
            ->limit(10)
            ->pluck('n', 'entity_name');

        return view('dashboard', [
            'types'       => Report::types(),
            'typeCounts'  => $typeCounts,
            'total'       => $typeCounts->sum(),
            'approved'    => $approved,
            'pending'     => $pending,
            'departmentsCount' => Department::where('type', 'department')->count(),
            'groupsCount'      => Department::where('type', 'group')->count(),
            'recent'      => Report::latest()->limit(6)->get(),
            'chart' => [
                'trend' => [
                    'labels' => $days->map(fn ($d) => $d->format('d M'))->all(),
                    'values' => $days->map(fn ($d) => (int) ($perDay[$d->toDateString()] ?? 0))->all(),
                ],
                'byEntity' => [
                    'labels' => $byEntity->keys()->all(),
                    'values' => $byEntity->values()->all(),
                ],
                'approved' => $approved,
                'pending'  => $pending,
            ],
        ]);
    }
}
