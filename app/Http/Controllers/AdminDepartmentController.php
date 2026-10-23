<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentCitation;
use App\Models\GroupCitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminDepartmentController extends Controller
{
    /**
     * List all departments and groups with their citation counts
     */
    public function index()
    {
        $deptCounts = DepartmentCitation::selectRaw('department, COUNT(*) as n')
            ->groupBy('department')
            ->pluck('n', 'department');

        $groupCounts = GroupCitation::selectRaw('group_name, COUNT(*) as n')
            ->groupBy('group_name')
            ->pluck('n', 'group_name');

        $items = Department::orderBy('name')->get()->map(function ($item) use ($deptCounts, $groupCounts) {
            $item->citations_count = $item->type === 'group'
                ? ($groupCounts[$item->name] ?? 0)
                : ($deptCounts[$item->name] ?? 0);

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
     * Rename a department or group (existing citations follow the new name)
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
                if ($department->type === 'group') {
                    GroupCitation::where('group_name', $oldName)->update(['group_name' => $newName]);
                } else {
                    DepartmentCitation::where('department', $oldName)->update(['department' => $newName]);
                }
            }
        });

        $label = $department->type === 'group' ? 'Group' : 'Department';

        return back()->with('success', "{$label} updated.");
    }

    /**
     * Delete a department or group (past citations are kept)
     */
    public function destroy(Department $department)
    {
        $label = $department->type === 'group' ? 'Group' : 'Department';

        $department->delete();

        return back()->with('success', "{$label} deleted.");
    }
}
