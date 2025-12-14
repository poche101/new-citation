<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class GroupController extends Controller
{
    public function exportExcel()
    {
        $groups = Group::all();
        return Excel::download(new \App\Exports\GroupsExport($groups), 'groups.xlsx');
    }

    public function exportCsv()
    {
        $groups = Group::all();

        return Response::stream(function () use ($groups) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Created At']);

            foreach ($groups as $group) {
                fputcsv($file, [$group->name, $group->created_at]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="groups.csv"',
        ]);
    }

    public function exportPdf()
    {
        $groups = Group::all();
        $pdf = PDF::loadView('admin.exports.groups_pdf', compact('groups'));
        return $pdf->download('groups.pdf');
    }
}
