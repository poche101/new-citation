<?php

namespace App\Exports;

use App\Models\Citation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CitationsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Citation::select(
            'title',
            'name',
            'unit',
            'group_name',
            'designation',
            'kingschat',
            'phone',
            'citation',
            'period_from',
            'period_to',
            'created_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Title',
            'Full Name',
            'Unit',
            'Group',
            'Designation',
            'Kingschat Handle',
            'Phone',
            'Citation',
            'Period From',
            'Period To',
            'Created At',
        ];
    }
}
