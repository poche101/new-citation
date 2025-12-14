<?php

namespace App\Http\Controllers;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('components.departments');
    }
}
