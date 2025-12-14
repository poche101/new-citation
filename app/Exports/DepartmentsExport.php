<?php

use App\Models\DepartmentCitation;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DepartmentCitationsExport; // Create this export class
use PDF;
use PhpOffice\PhpWord\PhpWord;


class DepartmentCitationController extends Controller
{
   public function exportExcel()
{
    $citations = DepartmentCitation::all();
    return Excel::download(new DepartmentCitationsExport($citations), 'departments.xlsx');
}

public function exportCsv()
{
    $citations = DepartmentCitation::all();

    $filename = 'departments.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function () use ($citations) {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Full Name','Unit','Designation','Department','Citation','Period']);

        foreach ($citations as $c) {
            fputcsv($file, [
                $c->fullname,
                $c->unit,
                $c->designation,
                $c->department,
                $c->citation,
                $c->period,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportPdf()
{
    $citations = DepartmentCitation::all();
    $pdf = PDF::loadView('departments.exports.pdf', compact('citations'));
    return $pdf->download('departments.pdf');
}

public function exportWord()
{
    $citations = DepartmentCitation::all();
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    foreach ($citations as $c) {
        $section->addText("Full Name: {$c->fullname}");
        $section->addText("Unit: {$c->unit}");
        $section->addText("Designation: {$c->designation}");
        $section->addText("Department: {$c->department}");
        $section->addText("Citation: {$c->citation}");
        $section->addText("Period: {$c->period}");
        $section->addTextBreak();
    }

    $filename = 'departments.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
}

}
