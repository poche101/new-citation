<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentCitation;
use App\Models\GroupCitation;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard with counts and tables
     */
    public function index()
    {
        // Fetch department citations paginated
        $departmentCitations = DepartmentCitation::latest()->paginate(10, ['*'], 'departments_page');

        // Fetch group citations paginated
        $groupCitations = GroupCitation::latest()->paginate(10, ['*'], 'groups_page');

        // Counts for dashboard cards
        $departmentsCount = DepartmentCitation::count();
        $groupsCount = GroupCitation::count();
        $citationsCount = $departmentsCount + $groupsCount;

        // Return dashboard view with all data
        return view('dashboard', compact(
            'departmentCitations',
            'groupCitations',
            'departmentsCount',
            'groupsCount',
            'citationsCount'
        ));
    }
}
