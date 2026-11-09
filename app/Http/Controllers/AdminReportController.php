<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesReportFiles;
use App\Models\Department;
use App\Models\Report;
use App\Support\ReportRules;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin tools for submitted reports: browse by type, edit, delete, approve, comment.
 */
class AdminReportController extends Controller
{
    use ManagesReportFiles;

    private const PER_PAGE = 15;

    /** Browse reports. ?type= picks which one to view; ?q= and ?status= narrow it down. */
    public function index(Request $request)
    {
        $type = in_array($request->query('type'), Report::typeKeys(), true) ? $request->query('type') : null;

        $reports = Report::with('attachments')
            ->ofType($type)
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports'    => $reports,
            'types'      => Report::types(),
            'activeType' => $type,
            'typeCounts' => Report::selectRaw('type, COUNT(*) as n')->groupBy('type')->pluck('n', 'type'),
            'filters'    => [
                'q'      => (string) $request->query('q', ''),
                'status' => in_array($request->query('status'), ['approved', 'pending'], true) ? $request->query('status') : '',
            ],
        ]);
    }

    public function edit(Report $report)
    {
        return view('admin.reports.edit', [
            'report'      => $report->load('attachments'),
            'departments' => Department::where('type', 'department')->orderBy('name')->get(['id', 'name']),
            'groups'      => Department::where('type', 'group')->orderBy('name')->get(['id', 'name']),
            'words'       => config('citation.max_words'),
            'uploads'     => config('citation.uploads'),
        ]);
    }

    public function update(Request $request, Report $report)
    {
        // Files the admin ticked for removal (only ones that belong to this report)
        $removeIds = $report->attachments()
            ->whereIn('id', array_map('intval', (array) $request->input('remove_attachments', [])))
            ->pluck('id')
            ->all();

        $remaining = $report->attachments()->count() - count($removeIds);

        $data = $request->validate(
            ReportRules::for($report->type)
                + ReportRules::attachments($remaining)
                + [
                    'entity_id'     => ['nullable', 'integer', Rule::exists('departments', 'id')],
                    'approved'      => ['nullable', 'boolean'],
                    'admin_comment' => ['nullable', 'string', 'max:500'],
                ],
            ReportRules::messages(),
            ReportRules::attributes()
        );

        $attributes = collect($data)->except(['attachments', 'entity_id', 'approved'])->all();
        $attributes['approved'] = $request->boolean('approved');

        // Moving the report to another department or group (blank = keep as is)
        if (! empty($data['entity_id'])) {
            $entity = Department::findOrFail($data['entity_id']);
            $attributes += [
                'entity_type' => $entity->type,
                'entity_name' => $entity->name,
                'entity_id'   => $entity->id,
            ];
        }

        $this->updateReportWithFiles($report, $attributes, $removeIds, $request);

        return redirect()
            ->route('admin.reports.index', ['type' => $report->type])
            ->with('success', 'Changes to the ' . strtolower($report->label()) . ' by ' . $report->name . ' were saved.');
    }

    /** Delete a report and its uploaded files. The page asks for confirmation first. */
    public function destroy(Request $request, Report $report)
    {
        $summary = strtolower($report->label()) . ' by ' . $report->name;

        $report->delete();

        // Return to the list the admin was looking at (same filters). Coming from
        // anywhere else, such as the edit page of this report, goes to the plain list.
        $index    = route('admin.reports.index');
        $previous = url()->previous();
        $fromList = str_starts_with($previous, $index)
            && parse_url($previous, PHP_URL_PATH) === parse_url($index, PHP_URL_PATH);

        return redirect($fromList ? $previous : $index)
            ->with('success', "The {$summary} was deleted.");
    }

    public function toggleApproval(Report $report)
    {
        $report->update(['approved' => ! $report->approved]);

        return back()->with('success', 'Marked as ' . ($report->approved ? 'approved' : 'pending') . '.');
    }

    /** Save or clear the admin comment (called by the page without reloading it). */
    public function comment(Request $request, Report $report)
    {
        $data = $request->validate(['comment' => ['nullable', 'string', 'max:500']]);

        $report->update(['admin_comment' => $data['comment'] ?? null]);

        return response()->json(['success' => true, 'comment' => $report->admin_comment]);
    }
}
