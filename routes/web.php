<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
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

// Departments listing page (simple closure example)
Route::get('/departments', function () {
    $departments = [
        ['name' => 'Cell Ministry', 'icon' => 'users'],
        ['name' => 'Zonal Operations', 'icon' => 'settings'],
        ['name' => 'Church Admin / Pioneering & Visitation', 'icon' => 'clipboard'],
        ['name' => 'Rhapsody of Realities', 'icon' => 'book-open'],
        ['name' => 'Healing School', 'icon' => 'heart'],
        ['name' => 'Finance', 'icon' => 'credit-card'],
        ['name' => 'TV Production', 'icon' => 'video'],
        ['name' => 'Ministry Material', 'icon' => 'file-text'],
        ['name' => 'Foundation School & First Timer Ministries', 'icon' => 'user-plus'],
        ['name' => 'Love World Music Department', 'icon' => 'music'],
        ['name' => 'Global Mission / HR / Admin', 'icon' => 'globe'],
        ['name' => 'Children & Women Ministries', 'icon' => 'users'],
        ['name' => 'LMMS, LXP, Ministry Programs, Bibles Partnership', 'icon' => 'book'],
        ['name' => 'LW USA, LTM / Radio Brands, Inner City Missions', 'icon' => 'flag'],
        ['name' => 'Follow Up Department', 'icon' => 'refresh-cw'],
        ['name' => 'Prayer', 'icon' => 'heart'],
        ['name' => 'Evangelism', 'icon' => 'send'],
        ['name' => 'Sceptre', 'icon' => 'star'],
    ];

    return view('components.departments', compact('departments'));
})->name('departments.index');

// Group form
Route::get('/group-form/{groupId?}', [GroupCitationController::class, 'create'])->name('group-citations.create');
Route::get('/group-form', [GroupCitationController::class, 'create'])->name('group-form.create');
Route::post('/group-form', [GroupCitationController::class, 'store'])->name('group-citations.store');

// Department form
Route::get('/department-form', [DepartmentCitationController::class, 'create'])->name('department-form.create');
Route::post('/department-form', [DepartmentCitationController::class, 'store'])->name('department-form.store');

// Export routes for groups
Route::prefix('groups')->group(function () {
    Route::get('export/excel', [GroupExportController::class, 'exportExcel'])->name('groups.exportExcel');
    Route::get('export/csv', [GroupExportController::class, 'exportCSV'])->name('groups.exportCSV');
    Route::get('export/pdf', [GroupExportController::class, 'exportPDF'])->name('groups.exportPDF');
    Route::get('export/word', [GroupExportController::class, 'exportWord'])->name('groups.exportWord');
});

// Export routes for departments
Route::prefix('departments')->group(function () {
    Route::get('export/excel', [DepartmentCitationController::class, 'exportExcel'])->name('departments.exportExcel');
    Route::get('export/csv', [DepartmentCitationController::class, 'exportCSV'])->name('departments.exportCSV');
    Route::get('export/pdf', [DepartmentCitationController::class, 'exportPDF'])->name('departments.exportPDF');
    Route::get('export/word', [DepartmentCitationController::class, 'exportWord'])->name('departments.exportWord');
});

// -------------------------
// Admin Routes
// -------------------------
Route::prefix('admin')->name('admin.')->group(function () {

    // Login routes
    Route::get('login', [AdminController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminController::class, 'login'])->name('login.submit');

    // Logout
    Route::post('logout', [AdminController::class, 'logout'])->name('logout')->middleware('auth:admin');

    // Dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Dashboard counts API (if used via JS/AJAX)
  Route::get('/admin/dashboard/counts', [AdminDashboardController::class, 'getCounts'])
     ->name('dashboard.counts'); // remove admin. prefix

});

// -------------------------
// Simple static pages (optional)
// -------------------------
Route::get('/groups', function() {
    return view('components.groups');
});


// Toggle department citation approval
Route::patch('/admin/department-citation/{id}/toggle-approval',
    [AdminDashboardController::class, 'toggleDepartmentApproval'])
    ->name('admin.departmentCitation.toggleApproval');

// Toggle group citation approval
Route::patch('/admin/group-citation/{id}/toggle-approval',
    [AdminDashboardController::class, 'toggleGroupApproval'])
    ->name('admin.groupCitation.toggleApproval');

// Toggle approval
Route::patch('/admin/department-citation/{id}/toggle-approval', [DepartmentCitationController::class, 'toggleApproval'])
    ->name('admin.departmentCitation.toggleApproval');

// Store/edit/delete admin comment (AJAX)
Route::post('/admin/department-citation/{id}/comment', [DepartmentCitationController::class, 'storeComment'])
    ->name('admin.departmentCitation.comment');


Route::post('/admin/group-citation/{id}/comment', [GroupCitationController::class, 'storeComment'])
    ->name('admin.groupCitation.comment');
