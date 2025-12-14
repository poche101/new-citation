<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PDF;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class GroupExportController extends Controller
{
    public function exportExcel()
    {
        // optional: add later
        abort(501);
    }

    public function exportCSV()
    {
        $groups = Group::all();

        return response()->streamDownload(function () use ($groups) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Created At']);

            foreach ($groups as $group) {
                fputcsv($handle, [$group->id, $group->name, $group->created_at]);
            }

            fclose($handle);
        }, 'groups.csv');
    }

    public function exportPDF()
    {
        $groups = Group::all();
        $pdf = PDF::loadView('admin.exports.groups_pdf', compact('groups'));
        return $pdf->download('groups.pdf');
    }

    public function exportWord()
    {
        $groups = Group::all();

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Groups Export', ['bold' => true, 'size' => 16]);

        foreach ($groups as $group) {
            $section->addText("• {$group->name}");
        }

        $fileName = 'groups.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'groups');

        IOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
