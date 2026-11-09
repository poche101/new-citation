<?php

namespace App\Exports;

use App\Models\Report;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/** Excel and CSV export of reports (already filtered by the controller). */
class ReportsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $reports)
    {
    }

    public function collection(): Collection
    {
        return $this->reports;
    }

    public function headings(): array
    {
        return [
            'ID', 'Report', 'Title', 'Full name', 'Unit', 'Designation', 'Kingschat', 'Phone',
            'Department or group', 'Name', 'Period from', 'Period to',
            'Executive summary', 'Detailed report', 'Citation',
            'Uploaded files', 'Status', 'Admin comment', 'Submitted',
        ];
    }

    /** @param Report $r */
    public function map($r): array
    {
        return array_map([self::class, 'safe'], [
            $r->id,
            $r->label(),
            $r->title,
            $r->name,
            $r->unit,
            $r->designation,
            $r->kingschat,
            $r->phone,
            $r->entityLabel(),
            $r->entity_name,
            $r->period_from?->format('Y-m-d'),
            $r->period_to?->format('Y-m-d'),
            $r->executive_summary,
            $r->detailed_report,
            $r->citation,
            $r->attachments->pluck('original_name')->implode(', '),
            $r->approved ? 'Approved' : 'Pending',
            $r->admin_comment,
            $r->created_at?->format('Y-m-d H:i'),
        ]);
    }

    /**
     * Text typed by visitors can start with = + - or @, which Excel would run as
     * a formula. A leading apostrophe makes it plain text.
     */
    public static function safe(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
