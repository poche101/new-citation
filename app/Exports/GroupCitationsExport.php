<?php

namespace App\Exports;

use App\Models\GroupCitation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GroupCitationsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return GroupCitation::with('attachments')->get();
    }

    public function headings(): array
    {
        return [
            'Title',
            'Full Name',
            'Unit',
            'Designation',
            'Kingschat Handle',
            'Phone Number',
            'Group Name',
            'Period From',
            'Period To',
            'Personal Citation',
            'Unit / Group Citation',
            'Personal Report: Executive Summary',
            'Personal Report: Detailed Report',
            'Unit Report: Executive Summary',
            'Unit Report: Detailed Report',
            'Uploaded Files',
        ];
    }

    /** @param GroupCitation $c */
    public function map($c): array
    {
        return [
            $c->title,
            $c->name,
            $c->unit,
            $c->designation,
            $c->kingschat,
            $c->phone,
            $c->group_name,
            $c->period_from?->format('Y-m-d'),
            $c->period_to?->format('Y-m-d'),
            $c->description,
            $c->unit_citation,
            $c->personal_report_summary,
            $c->personal_report_detail,
            $c->unit_report_summary,
            $c->unit_report_detail,
            $c->attachments->pluck('original_name')->implode(', '),
        ];
    }
}
