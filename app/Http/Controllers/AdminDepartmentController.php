<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminDepartmentController extends Controller
{
    /**
     * List all departments and groups with how many reports each has received
     */
    public function index()
    {
        $counts = Report::selectRaw('entity_type, entity_name, COUNT(*) as n')
            ->groupBy('entity_type', 'entity_name')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->entity_type . '|' . $row->entity_name => $row->n]);

        $items = Department::orderBy('name')->get()->map(function ($item) use ($counts) {
            $item->citations_count = $counts[$item->type . '|' . $item->name] ?? 0;

            return $item;
        });

        return view('admin.departments', [
            'departments' => $items->where('type', 'department')->values(),
            'groups'      => $items->where('type', 'group')->values(),
        ]);
    }

    /**
     * Add a new department or group
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['department', 'group'])],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('departments', 'name')->where('type', $request->input('type')),
            ],
        ]);

        Department::create([
            'name' => trim($data['name']),
            'type' => $data['type'],
        ]);

        $label = $data['type'] === 'group' ? 'Group' : 'Department';

        return back()->with('success', "{$label} added.");
    }

    /**
     * Rename a department or group (existing reports follow the new name)
     */
    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('departments', 'name')
                    ->where('type', $department->type)
                    ->ignore($department->id),
            ],
        ]);

        $oldName = $department->name;
        $newName = trim($data['name']);

        DB::transaction(function () use ($department, $oldName, $newName) {
            $department->update(['name' => $newName]);

            if ($oldName !== $newName) {
                Report::where('entity_type', $department->type)
                    ->where('entity_name', $oldName)
                    ->update(['entity_name' => $newName]);
            }
        });

        $label = $department->type === 'group' ? 'Group' : 'Department';

        return back()->with('success', "{$label} updated.");
    }

    /**
     * Delete a department or group (past reports are kept)
     */
    public function destroy(Department $department)
    {
        $label = $department->type === 'group' ? 'Group' : 'Department';

        $department->delete();

        return back()->with('success', "{$label} deleted.");
    }
}
