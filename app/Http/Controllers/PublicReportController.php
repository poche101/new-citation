<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesReportFiles;
use App\Models\Department;
use App\Models\Report;
use App\Support\ReportRules;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * The public submission flow:
 *   /submit                       choose which report to submit
 *   /submit/{type}                choose your department or group
 *   /submit/{type}/{department}   the form (with the upload at the end)
 *   /submit/done                  thank-you page
 */
class PublicReportController extends Controller
{
    use ManagesReportFiles;

    /** Step 1: pick one of the four reports. */
    public function choose()
    {
        $types = collect(Report::types());

        return view('reports.choose', [
            'reports'   => $types->where('kind', 'report'),
            'citations' => $types->where('kind', 'citation'),
        ]);
    }

    /** Step 2: pick the department or group. */
    public function entities(string $type)
    {
        [$key, $config] = $this->resolveType($type);

        $all = Department::orderBy('name')->get(['id', 'name', 'type']);

        return view('reports.entities', [
            'typeKey'     => $key,
            'type'        => $config,
            'departments' => $all->where('type', 'department')->values(),
            'groups'      => $all->where('type', 'group')->values(),
        ]);
    }

    /** Step 3: the form. */
    public function create(string $type, Department $department)
    {
        [$key, $config] = $this->resolveType($type);

        return view('reports.form', [
            'typeKey'    => $key,
            'type'       => $config,
            'department' => $department,
            'words'      => config('citation.max_words'),
            'uploads'    => config('citation.uploads'),
            // Pre-filled so the dates are obvious and easy to change
            'defaultFrom' => now()->startOfYear()->toDateString(),
            'defaultTo'   => now()->endOfYear()->toDateString(),
        ]);
    }

    public function store(Request $request, string $type, Department $department)
    {
        [$key, $config] = $this->resolveType($type);

        $data = $request->validate(
            ReportRules::for($key) + ReportRules::attachments(0, $config['kind'] === 'report'),
            ReportRules::messages(),
            ReportRules::attributes()
        );

        // Only validated fields are saved (never the raw request), so a visitor
        // cannot set fields such as "approved" or "admin_comment".
        $report = $this->createReportWithFiles(
            Arr::except($data, ['attachments']) + [
                'type'        => $key,
                'entity_type' => $department->type,
                'entity_name' => $department->name,
                'entity_id'   => $department->id,
            ],
            $request
        );

        return redirect()->route('reports.done')->with('submitted', [
            'label'  => $config['label'],
            'name'   => $report->fullName(),
            'entity' => $department->name,
            'files'  => $report->attachments()->count(),
        ]);
    }

    public function done()
    {
        if (! session()->has('submitted')) {
            return redirect()->route('reports.choose');
        }

        return view('reports.done', ['submitted' => session('submitted')]);
    }

    /** @return array{0: string, 1: array} the type key and its settings, or a 404 */
    private function resolveType(string $slug): array
    {
        $key = Report::keyForSlug($slug);
        abort_if($key === null, 404);

        return [$key, Report::typeConfig($key)];
    }
}
