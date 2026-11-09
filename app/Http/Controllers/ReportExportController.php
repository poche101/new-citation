<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;

/**
 * Exports the reports the admin is looking at (same ?type=, ?q= and ?status=
 * filters as the list). Admin only (see routes/web.php).
 */
class ReportExportController extends Controller
{
    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'csv', 'pdf', 'word'], true), 404);

        $type = in_array($request->query('type'), Report::typeKeys(), true) ? $request->query('type') : null;

        $reports = Report::with('attachments')
            ->ofType($type)
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->latest()
            ->get();

        $name = 'reports-' . ($type ? str_replace('_', '-', $type) : 'all') . '-' . now()->format('Y-m-d');

        return match ($format) {
            'excel' => Excel::download(new ReportsExport($reports), $name . '.xlsx'),
            'csv'   => Excel::download(new ReportsExport($reports), $name . '.csv', ExcelFormat::CSV),
            'pdf'   => Pdf::loadView('admin.exports.reports_pdf', ['reports' => $reports, 'title' => $this->title($type)])
                ->download($name . '.pdf'),
            'word'  => $this->word($reports, $name . '.docx', $this->title($type)),
        };
    }

    private function title(?string $type): string
    {
        return $type ? Report::typeConfig($type)['label'] . 's' : 'All reports and citations';
    }

    private function word($reports, string $filename, string $title)
    {
        $word = new PhpWord();
        $word->setDefaultFontName('Calibri');
        $word->setDefaultFontSize(11);
        $section = $word->addSection();

        $section->addTitle($title, 1);
        $section->addText($reports->count() . ' submission(s). Exported ' . now()->format('j F Y') . '.');

        foreach ($reports as $r) {
            $section->addTextBreak(1);
            $section->addTitle($r->fullName() . ': ' . $r->label(), 2);
            $section->addText($r->entityLabel() . ': ' . $r->entity_name . '. Unit: ' . $r->unit . '. Designation: ' . $r->designation . '.');
            $section->addText('Kingschat: ' . $r->kingschat . '. Phone: ' . $r->phone . '.');
            $section->addText('Period: ' . $r->periodText() . '. Status: ' . ($r->approved ? 'Approved' : 'Pending') . '.');

            foreach ($r->sections() as [$heading, $text]) {
                $section->addText($heading, ['bold' => true]);
                // Keep line breaks the visitor typed
                foreach (preg_split('/\R/', (string) $text) as $line) {
                    $section->addText($line);
                }
            }

            if ($r->attachments->isNotEmpty()) {
                $section->addText('Files: ' . $r->attachments->pluck('original_name')->implode(', '));
            }
            if ($r->admin_comment) {
                $section->addText('Admin comment: ' . $r->admin_comment, ['italic' => true]);
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'rep');
        $word->save($tmp, 'Word2007');

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }
}
