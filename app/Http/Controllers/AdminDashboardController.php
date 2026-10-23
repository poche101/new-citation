<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentCitation;
use App\Models\GroupCitation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard with counts, analytics and tables
     */
    public function index()
    {
        $departmentCitations = DepartmentCitation::latest()->paginate(10, ['*'], 'departments_page');
        $groupCitations      = GroupCitation::latest()->paginate(10, ['*'], 'groups_page');

        // Cards (now based on the category chosen when the entry was created)
        $departmentsCount = Department::where('type', 'department')->count();
        $groupsCount      = Department::where('type', 'group')->count();
        $citationsCount   = DepartmentCitation::count() + GroupCitation::count();

        // Submissions over the last 30 days (both tables combined)
        $days = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        $perDay = fn ($model) => $model::where('created_at', '>=', $days->first())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as n')
            ->groupBy('d')
            ->pluck('n', 'd');

        $deptPerDay  = $perDay(DepartmentCitation::class);
        $groupPerDay = $perDay(GroupCitation::class);

        // Top departments by number of citations
        $byDepartment = DepartmentCitation::selectRaw('department, COUNT(*) as n')
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderByDesc('n')
            ->limit(10)
            ->pluck('n', 'department');

        $stats = [
            'approved' => DepartmentCitation::where('approved', true)->count()
                        + GroupCitation::where('approved', true)->count(),
            'pending'  => DepartmentCitation::where('approved', false)->count()
                        + GroupCitation::where('approved', false)->count(),
            'trend' => [
                'labels' => $days->map(fn ($d) => $d->format('d M'))->all(),
                'values' => $days->map(fn ($d) => ($deptPerDay[$d->toDateString()] ?? 0)
                                                + ($groupPerDay[$d->toDateString()] ?? 0))->all(),
            ],
            'byDepartment' => [
                'labels' => $byDepartment->keys()->all(),
                'values' => $byDepartment->values()->all(),
            ],
        ];

        return view('dashboard', compact(
            'departmentCitations',
            'groupCitations',
            'departmentsCount',
            'groupsCount',
            'citationsCount',
            'stats'
        ));
    }

    /**
     * Add a new department or group
     */
    public function storeDepartment(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['department', 'group'])],
            'name' => [
                'required',
                'string',
                'max:120',
                // The same name may exist once as a department and once as a group
                Rule::unique('departments', 'name')->where('type', $request->input('type')),
            ],
        ]);

        Department::create([
            'name' => trim($data['name']),
            'type' => $data['type'],
        ]);

        $label = $data['type'] === 'group' ? 'Group' : 'Department';

        return redirect()->route('admin.dashboard')->with('success', "{$label} added.");
    }

    /**
     * Toggle approved status for a department citation
     */
    public function toggleDepartmentApproval($id)
    {
        $citation = DepartmentCitation::findOrFail($id);
        $citation->approved = !$citation->approved;
        $citation->save();

        return redirect()->back()->with('success', 'Department citation approval status updated.');
    }

    /**
     * Toggle approved status for a group citation
     */
    public function toggleGroupApproval($id)
    {
        $citation = GroupCitation::findOrFail($id);
        $citation->approved = !$citation->approved;
        $citation->save();

        return redirect()->back()->with('success', 'Group citation approval status updated.');
    }
}
