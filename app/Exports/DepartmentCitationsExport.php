<?php

namespace App\Exports;

use App\Models\DepartmentCitation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DepartmentCitationsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return DepartmentCitation::with('attachments')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Full Name',
            'Unit',
            'Department',
            'Designation',
            'Kingschat',
            'Phone',
            'Period',
            'Personal Citation',
            'Unit / Department Citation',
            'Personal Report: Executive Summary',
            'Personal Report: Detailed Report',
            'Unit Report: Executive Summary',
            'Unit Report: Detailed Report',
            'Uploaded Files',
            'Status',
            'Admin Comment',
            'Submitted At',
        ];
    }

    /** @param DepartmentCitation $c */
    public function map($c): array
    {
        return [
            $c->id,
            $c->title,
            $c->fullname,
            $c->unit,
            $c->department,
            $c->designation,
            $c->kingschat,
            $c->phone,
            $c->period,
            $c->citation,
            $c->unit_citation,
            $c->personal_report_summary,
            $c->personal_report_detail,
            $c->unit_report_summary,
            $c->unit_report_detail,
            $c->attachments->pluck('original_name')->implode(', '),
            $c->approved ? 'Approved' : 'Pending',
            $c->admin_comment,
            $c->created_at?->format('Y-m-d H:i'),
        ];
    }
}
