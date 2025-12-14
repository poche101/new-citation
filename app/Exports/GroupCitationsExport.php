<?php

namespace App\Exports;

use App\Models\GroupCitation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GroupCitationsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return GroupCitation::select(
            'title',
            'name',
            'unit',
            'designation',
            'kingschat',
            'phone',
            'group_name',
            'period_from',
            'period_to',
            'description'
        )->get();
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
            'Citation',
        ];
    }
}
