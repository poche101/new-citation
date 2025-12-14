<?php

namespace App\Http\Controllers;

use App\Models\DepartmentCitation;
use App\Models\GroupCitation;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard with counts and tables
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        // -------------------------
        // Dashboard card counts
        // -------------------------

        // Departments with at least one citation
        $departmentsCount = DepartmentCitation::distinct('department')->count('department');

        // Groups with at least one citation
        $groupsCount = GroupCitation::distinct('group_id')->count('group_id');

        // Total citations submitted (group + department)
        $citationsCount = GroupCitation::count() + DepartmentCitation::count();

        // -------------------------
        // Department citations table
        // -------------------------
        $departmentCitationsQuery = DepartmentCitation::query();

        if ($search) {
            $departmentCitationsQuery->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('kingschat', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('citation', 'like', "%{$search}%");
            });
        }

        $departmentCitations = $departmentCitationsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'departments_page');

        // -------------------------
        // Group citations table
        // -------------------------
        $groupCitationsQuery = GroupCitation::query();

        if ($search) {
            $groupCitationsQuery->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('group_name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('kingschat', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('citation', 'like', "%{$search}%");
            });
        }

        $groupCitations = $groupCitationsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'groups_page');

        // -------------------------
        // Return view with all data
        // -------------------------
        return view('dashboard', compact(
            'departmentsCount',
            'groupsCount',
            'citationsCount',
            'departmentCitations',
            'groupCitations'
        ));
    }
}
