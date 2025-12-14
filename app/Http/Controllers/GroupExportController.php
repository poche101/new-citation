<?php

namespace App\Http\Controllers;

use App\Models\GroupCitation;
use Illuminate\Http\Request;
use App\Exports\GroupCitationsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;

class GroupExportController extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new GroupCitationsExport, 'group_citations.xlsx');
    }

    public function exportCSV()
    {
        return Excel::download(new GroupCitationsExport, 'group_citations.csv');
    }

    public function exportPDF()
    {
        $groupCitations = GroupCitation::all();
        $pdf = Pdf::loadView('exports.group_citations_pdf', compact('groupCitations'));
        return $pdf->download('group_citations.pdf');
    }

    public function exportWord()
    {
        $groupCitations = GroupCitation::all();
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        foreach($groupCitations as $citation){
            $section->addText("{$citation->title} {$citation->name} - {$citation->group_name}");
            $section->addText("Unit: {$citation->unit}, Designation: {$citation->designation}");
            $section->addText("Kingschat: {$citation->kingschat}, Phone: {$citation->phone}");
            $section->addText("Citation: {$citation->description}");
            $section->addTextBreak(1);
        }

        $file = storage_path('group_citations.docx');
        $phpWord->save($file, 'Word2007');
        return response()->download($file)->deleteFileAfterSend(true);
    }
}
