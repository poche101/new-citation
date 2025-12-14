<?php

namespace App\Exports;

use App\Models\DepartmentCitation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DepartmentCitationsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DepartmentCitation::all();
    }

    /**
     * Set headings for Excel columns
     */
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
            'Citation',
            'Period',
            'Created At',
            'Updated At'
        ];
    }
}
