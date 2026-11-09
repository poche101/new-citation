<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminDepartmentController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\CitationAttachmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicReportController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

// -------------------------
// Public site
// -------------------------

// Landing page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Submission flow: choose a report -> choose department/group -> form -> thank you
$reportSlugs = collect(config('citation.reports'))->pluck('slug')->implode('|');

Route::prefix('submit')->name('reports.')->group(function () use ($reportSlugs) {
    Route::get('/', [PublicReportController::class, 'choose'])->name('choose');
    Route::get('done', [PublicReportController::class, 'done'])->name('done');

    Route::get('{type}', [PublicReportController::class, 'entities'])
        ->where('type', $reportSlugs)->name('entities');
    Route::get('{type}/{department}', [PublicReportController::class, 'create'])
        ->where('type', $reportSlugs)->whereNumber('department')->name('create');
    Route::post('{type}/{department}', [PublicReportController::class, 'store'])
        ->where('type', $reportSlugs)->whereNumber('department')->name('store');
});

// Links to the old forms and pages keep working
Route::redirect('/department-form', '/submit', 301);
Route::redirect('/group-form', '/submit', 301);
Route::redirect('/group-form/{groupId}', '/submit', 301);
Route::redirect('/departments', '/submit', 301);
Route::redirect('/groups', '/submit', 301);

// -------------------------
// Admin (URLs: /admin/..., names: admin....)
// -------------------------
Route::prefix('admin')->name('admin.')->group(function () {

    // Login (guests)
    Route::get('login', [AdminController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminController::class, 'login'])->name('login.submit');

    // Everything below requires an admin session
    Route::middleware('auth:admin')->group(function () {

        Route::post('logout', [AdminController::class, 'logout'])->name('logout');

        // Overview
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Reports: browse by type, export, edit, delete, approve, comment
        Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/{format}', [ReportExportController::class, 'export'])->name('reports.export');
        Route::get('reports/{report}/edit', [AdminReportController::class, 'edit'])->name('reports.edit');
        Route::put('reports/{report}', [AdminReportController::class, 'update'])->name('reports.update');
        Route::delete('reports/{report}', [AdminReportController::class, 'destroy'])->name('reports.destroy');
        Route::patch('reports/{report}/approval', [AdminReportController::class, 'toggleApproval'])->name('reports.approval');
        Route::post('reports/{report}/comment', [AdminReportController::class, 'comment'])->name('reports.comment');

        // Download a file uploaded with a submission (private storage)
        Route::get('attachments/{attachment}', [CitationAttachmentController::class, 'download'])
            ->name('attachments.download');

        // Manage departments & groups (list, add, edit, delete)
        Route::get('departments', [AdminDepartmentController::class, 'index'])->name('departments.index');
        Route::post('departments', [AdminDepartmentController::class, 'store'])->name('departments.store');
        Route::put('departments/{department}', [AdminDepartmentController::class, 'update'])->name('departments.update');
        Route::delete('departments/{department}', [AdminDepartmentController::class, 'destroy'])->name('departments.destroy');
    });
});
