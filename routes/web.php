<?php

use Illuminate\Support\Facades\Route;
use App\Models\Department;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminDepartmentController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentCitationController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupCitationController;
use App\Http\Controllers\GroupExportController;

// -------------------------
// Public Routes
// -------------------------

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Departments listing page (read from the database, departments only)
Route::get('/departments', function () {
    $departments = Department::where('type', 'department')
        ->orderBy('id')
        ->get()
        ->map(fn ($d) => ['name' => $d->name, 'icon' => 'folder'])
        ->all();

    return view('components.departments', compact('departments'));
})->name('departments.index');

// Group form
Route::get('/group-form/{groupId?}', [GroupCitationController::class, 'create'])->name('group-citations.create');
Route::get('/group-form', [GroupCitationController::class, 'create'])->name('group-form.create');
Route::post('/group-form', [GroupCitationController::class, 'store'])->name('group-citations.store');

// Department form
Route::get('/department-form', [DepartmentCitationController::class, 'create'])->name('department-form.create');
Route::post('/department-form', [DepartmentCitationController::class, 'store'])->name('department-form.store');

// Simple static pages (optional)
Route::get('/groups', function () {
    return view('components.groups');
});

// -------------------------
// Exports (admin only)
// -------------------------
Route::middleware('auth:admin')->group(function () {
    Route::prefix('groups')->group(function () {
        Route::get('export/excel', [GroupExportController::class, 'exportExcel'])->name('groups.exportExcel');
        Route::get('export/csv', [GroupExportController::class, 'exportCSV'])->name('groups.exportCSV');
        Route::get('export/pdf', [GroupExportController::class, 'exportPDF'])->name('groups.exportPDF');
        Route::get('export/word', [GroupExportController::class, 'exportWord'])->name('groups.exportWord');
    });

    Route::prefix('departments')->group(function () {
        Route::get('export/excel', [DepartmentCitationController::class, 'exportExcel'])->name('departments.exportExcel');
        Route::get('export/csv', [DepartmentCitationController::class, 'exportCSV'])->name('departments.exportCSV');
        Route::get('export/pdf', [DepartmentCitationController::class, 'exportPDF'])->name('departments.exportPDF');
        Route::get('export/word', [DepartmentCitationController::class, 'exportWord'])->name('departments.exportWord');
    });
});

// -------------------------
// Admin Routes (URLs: /admin/..., names: admin....)
// -------------------------
Route::prefix('admin')->name('admin.')->group(function () {

    // Login (guests)
    Route::get('login', [AdminController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminController::class, 'login'])->name('login.submit');

    // Everything below requires an admin session
    Route::middleware('auth:admin')->group(function () {

        Route::post('logout', [AdminController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Manage departments & groups (list, add, edit, delete)
        Route::get('departments', [AdminDepartmentController::class, 'index'])->name('departments.index');
        Route::post('departments', [AdminDepartmentController::class, 'store'])->name('departments.store');
        Route::put('departments/{department}', [AdminDepartmentController::class, 'update'])->name('departments.update');
        Route::delete('departments/{department}', [AdminDepartmentController::class, 'destroy'])->name('departments.destroy');

        // Approval toggles
        Route::patch('department-citation/{id}/toggle-approval', [AdminDashboardController::class, 'toggleDepartmentApproval'])
            ->name('departmentCitation.toggleApproval');
        Route::patch('group-citation/{id}/toggle-approval', [AdminDashboardController::class, 'toggleGroupApproval'])
            ->name('groupCitation.toggleApproval');

        // Admin comments (AJAX)
        Route::post('department-citation/{id}/comment', [DepartmentCitationController::class, 'storeComment'])
            ->name('departmentCitation.comment');
        Route::post('group-citation/{id}/comment', [GroupCitationController::class, 'storeComment'])
            ->name('groupCitation.comment');
    });
});
