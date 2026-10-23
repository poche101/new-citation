<?php

namespace App\Http\Controllers;

use App\Models\Department;

class HomeController extends Controller
{
    public function index()
    {
        // Departments (the view expects name + description for each)
        $departments = Department::where('type', 'department')
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => [
                'name'        => $name,
                'description' => 'Click to submit your citation',
            ])
            ->all();

        // Groups (the view expects plain strings)
        $groups = Department::where('type', 'group')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return view('home', compact('departments', 'groups'));
    }
}
