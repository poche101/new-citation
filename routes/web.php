<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentCitationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\GroupCitationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\GroupController;




Route::prefix('groups')->name('groups.')->group(function () {
    Route::get('/export/excel', [GroupExportController::class, 'exportExcel'])->name('exportExcel');
    Route::get('/export/csv', [GroupExportController::class, 'exportCSV'])->name('exportCSV');
    Route::get('/export/pdf', [GroupExportController::class, 'exportPDF'])->name('exportPDF');
    Route::get('/export/word', [GroupExportController::class, 'exportWord'])->name('exportWord');
});

Route::prefix('departments')->group(function () {
    Route::get('/export/excel', [DepartmentCitationController::class, 'exportExcel'])->name('departments.exportExcel');
    Route::get('/export/csv',   [DepartmentCitationController::class, 'exportCsv'])->name('departments.exportCSV');
    Route::get('/export/pdf',   [DepartmentCitationController::class, 'exportPdf'])->name('departments.exportPDF');
    Route::get('/export/word',  [DepartmentCitationController::class, 'exportWord'])->name('departments.exportWord');
});



Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
// web.php
Route::get('/dashboard/counts', [AdminDashboardController::class, 'getCounts'])
     ->name('dashboard.counts');



Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
});


Route::get('/group-form/{groupId?}', [GroupCitationController::class, 'create'])
     ->name('group-citations.create');

     Route::get('/group-form', [GroupCitationController::class, 'create'])
    ->name('group-form.create');

Route::post('/group-form', [GroupCitationController::class, 'store'])
     ->name('group-citations.store');

// Simple route using closure
Route::get('/groups', function() {
    return view('components.groups');
});

Route::get('/departments', function () {
    return view('components.departments');
})->name('departments.index');

Route::get('/departments', [DepartmentController::class, 'index'])
    ->name('departments.index');

Route::get('/department-form', [DepartmentCitationController::class, 'create'])->name('department-form.create');
Route::post('/department-form', [DepartmentCitationController::class, 'store'])->name('department-form.store');

Route::get('/', [HomeController::class, 'index']);


// Departments listing page
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

