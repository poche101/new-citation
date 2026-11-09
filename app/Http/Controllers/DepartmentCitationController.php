<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCitationSubmission;
use App\Models\DepartmentCitation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DepartmentCitationsExport;
use PDF;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;

class DepartmentCitationController extends Controller
{
    use HandlesCitationSubmission;

    /**
     * Show the department citation form
     */
    public function create()
    {
        return view('components.department-form'); // Blade form
    }

    /**
     * Store a new department citation
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'fullname'     => 'required|string|max:255',
                'unit'         => 'required|string|max:255',
                'designation'  => 'required|string|max:255',
                'kingschat'    => 'required|string|max:255',
                'phone'        => 'required|string|max:20',
                'department'   => 'required|string|max:255',
                'period'       => 'nullable|string|max:255',
                'title'        => 'nullable|string|max:255',
                // Personal citation (max 150 words)
                'citation'     => $this->citationRules(),
            ] + $this->submissionRules(),
            $this->submissionMessages(),
            $this->submissionAttributes()
        );

        // Only validated fields are saved (never the raw request), so a visitor
        // cannot set fields such as "approved" or "admin_comment" themselves.
        $this->createWithAttachments(
            DepartmentCitation::class,
            Arr::except($data, ['attachments']),
            $request
        );

        return redirect()
            ->route('department-form.create')
            ->with('success', 'Department Citation submitted successfully!');
    }

    /**
     * Toggle approval status for a citation
     */
    public function toggleApproval($id)
    {
        $citation = DepartmentCitation::findOrFail($id);
        $citation->approved = !$citation->approved;
        $citation->save();

        return redirect()->back()->with('success', 'Approval status updated.');
    }

    /**
     * Store, update, or delete admin comment (AJAX)
     */
    public function storeComment(Request $request, $id)
    {
        $citation = DepartmentCitation::findOrFail($id);

        $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        // Save or delete comment
        $citation->admin_comment = $request->comment;
        $citation->save();

        return response()->json([
            'success' => true,
            'comment' => $citation->admin_comment
        ]);
    }

    /**
     * Export all department citations as Excel
     */
    public function exportExcel()
    {
        $citations = DepartmentCitation::all();
        return Excel::download(new DepartmentCitationsExport($citations), 'department_citations.xlsx');
    }

    /**
     * Export all department citations as CSV
     */
    public function exportCSV()
    {
        $citations = DepartmentCitation::with('attachments')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="department_citations.csv"',
        ];

        $callback = function () use ($citations) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Title', 'Full Name', 'Unit', 'Department', 'Designation',
                'Kingschat', 'Phone', 'Personal Citation', 'Unit / Department Citation',
                'Personal Report Summary', 'Personal Report Detail',
                'Unit Report Summary', 'Unit Report Detail',
                'Period', 'Uploaded Files', 'Admin Comment', 'Approved'
            ]);

            foreach ($citations as $citation) {
                fputcsv($file, [
                    $citation->title,
                    $citation->fullname,
                    $citation->unit,
                    $citation->department,
                    $citation->designation,
                    $citation->kingschat,
                    $citation->phone,
                    $citation->citation,
                    $citation->unit_citation,
                    $citation->personal_report_summary,
                    $citation->personal_report_detail,
                    $citation->unit_report_summary,
                    $citation->unit_report_detail,
                    $citation->period,
                    $citation->attachments->pluck('original_name')->implode(', '),
                    $citation->admin_comment ?? '',
                    $citation->approved ? 'Approved' : 'Pending',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export all department citations as PDF
     */
    public function exportPDF()
    {
        $citations = DepartmentCitation::all();
        $pdf = PDF::loadView('department_citations_pdf', compact('citations'));
        return $pdf->download('department_citations.pdf');
    }

    /**
     * Export all department citations as Word (basic HTML)
     */
    public function exportWord()
    {
        $citations = DepartmentCitation::all();
        $content = view('department_citations_word', compact('citations'))->render();

        $headers = [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="department_citations.doc"',
        ];

        return Response::make($content, 200, $headers);
    }
}
