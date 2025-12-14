<?php

namespace App\Http\Controllers;

use App\Models\DepartmentCitation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DepartmentCitationsExport;
use PDF;
use Illuminate\Support\Facades\Response;

class DepartmentCitationController extends Controller
{
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
        $request->validate([
            'fullname'     => 'required|string|max:255',
            'unit'         => 'required|string|max:255',
            'designation'  => 'required|string|max:255',
            'kingschat'    => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'department'   => 'required|string|max:255',
            'period'       => 'nullable|string|max:255',
            'citation'     => 'required|string',
            'title'        => 'nullable|string|max:255',
        ]);

        if (str_word_count($request->citation) > 150) {
            return back()
                ->withInput()
                ->withErrors(['citation' => 'Citation must not exceed 150 words.']);
        }

        DepartmentCitation::create($request->all());

        return redirect()
            ->route('department-form.create')
            ->with('success', 'Department Citation submitted successfully!');
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
        $citations = DepartmentCitation::all();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="department_citations.csv"',
        ];

        $callback = function () use ($citations) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Title', 'Full Name', 'Unit', 'Department', 'Designation', 'Kingschat', 'Phone', 'Citation', 'Period']);

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
                    $citation->period,
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
