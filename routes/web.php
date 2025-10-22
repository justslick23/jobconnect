<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobRequisitionController;
use App\Http\Controllers\Applicant\ProfileController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LinkedInController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShortlistingSettingController;
use App\Http\Controllers\ShortlistingReportController;

Route::get('/', [HomeController::class, 'home'])->name('home');

Auth::routes();

// ===============================================
// AUTHENTICATED ROUTES - MUST COME FIRST!
// ===============================================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // Job Requisitions Management (SPECIFIC routes before wildcard routes)
    Route::get('/jobs/create', [JobRequisitionController::class, 'create'])->name('job-requisitions.create');
    Route::post('/jobs', [JobRequisitionController::class, 'store'])->name('job-requisitions.store');
    Route::get('/{jobRequisition}/edit', [JobRequisitionController::class, 'edit'])->name('job-requisitions.edit');
    Route::put('/{jobRequisition}', [JobRequisitionController::class, 'update'])->name('job-requisitions.update');
    Route::delete('/jobs/{jobRequisition}', [JobRequisitionController::class, 'destroy'])->name('job-requisitions.destroy');

    Route::post('/job/{jobRequisition}/approve', [JobRequisitionController::class, 'approve'])->name('job-requisitions.approve');
    Route::post('/job/{jobRequisition}/reject', [JobRequisitionController::class, 'reject'])->name('job-requisitions.reject');

    // ===============================================
    // APPLICANT PROFILE ROUTES
    // ===============================================
    
    Route::get('/profile/complete', [ProfileController::class, 'create'])->name('applicant.profile.create');
    Route::get('/profile/edit', [ProfileController::class, 'create'])->name('applicant.profile.edit');
    Route::post('/profile/complete', [ProfileController::class, 'store'])->name('applicant.profile.store');
    Route::put('/profile/complete', [ProfileController::class, 'update'])->name('applicant.profile.update');
    Route::get('/profile', [ProfileController::class, 'index'])->name('applicant.profile.index');
    Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('applicant.profile.show');

    // Job Applications
    Route::get('/applications/create', [JobApplicationController::class, 'create'])->name('job-applications.create');
    Route::get('/applications', [JobApplicationController::class, 'index'])->name('job-applications.index');
    Route::get('/applications/{uuid}', [JobApplicationController::class, 'show'])->name('job-applications.show');
    Route::post('/applications', [JobApplicationController::class, 'store'])->name('job-applications.store');

    Route::patch('/applications/{application}/status', [JobApplicationController::class, 'updateStatus'])->name('applications.update-status');
    Route::get('/applications/{application}/download-resume', [JobApplicationController::class, 'downloadResume'])
    ->name('applications.download-resume');
    Route::get('/applications/{application}/export-profile', [JobApplicationController::class, 'exportProfile'])->name('applications.export-profile');
    Route::post('/applications/{application}/offer-letter/send', [JobApplicationController::class, 'sendOfferLetter'])->name('applications.offerLetter.send');
    Route::post('/applications/{application}/review', [JobApplicationController::class, 'submitReview'])->name('applications.review.submit');
    Route::get('/job-attachments/{id}/download', [JobApplicationController::class, 'downloadAttachment'])
    ->name('job-applications.downloadAttachment');
    Route::post('job-applications/{id}/quick-action', [JobApplicationController::class, 'quickAction'])
    ->name('job-applications.quick-action');
    Route::post('/job-applications/bulk-action', [JobApplicationController::class, 'bulkAction'])->name('job-applications.bulk-action');

    // Export applications
    Route::get('/job-applications/export/{jobId}', [JobApplicationController::class, 'exportByJob'])
    ->name('job-applications.export');
    Route::get('/job-applications/export-all', [JobApplicationController::class, 'exportAll'])
        ->name('job-applications.export-all');    
    Route::get('/application/download-attachment/{id}', [JobApplicationController::class, 'downloadAttachment'])
    ->name('attachments.download');

    // Interviews
    Route::get('/interviews/schedule/{application}', [InterviewController::class, 'create'])->name('interviews.schedule');
    Route::post('/interviews', [InterviewController::class, 'store'])->name('interviews.store');
    Route::get('/interviews', [InterviewController::class, 'index'])->name('interviews.index');
    Route::get('/interviews/{interview}', [InterviewController::class, 'show'])->name('interviews.show');
    Route::post('/interviews/{interview}/score', [InterviewController::class, 'submitScore'])->name('interviews.score.store');
    
    // Departments
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    
    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/resend-password-reset', [UserController::class, 'resendPasswordReset'])
    ->name('users.resendPasswordReset');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/data', [ReportController::class, 'getData'])->name('reports.data');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::post('/skills', [\App\Http\Controllers\SkillController::class, 'store'])->name('skills.store');

    // Shortlisting Settings
    Route::get('shortlisting-settings', [ShortlistingSettingController::class, 'index'])->name('shortlisting-settings.index');
    Route::post('shortlisting-settings', [ShortlistingSettingController::class, 'update'])->name('shortlisting-settings.update');
    
    // Shortlisting Reports
    Route::prefix('shortlisting-reports')->name('shortlisting.')->group(function () {
        Route::post('/generate', [ShortlistingReportController::class, 'generateReport'])->name('generate');
        Route::get('/', [ShortlistingReportController::class, 'listReports'])->name('list');
        Route::get('/download/{fileName}', [ShortlistingReportController::class, 'downloadReport'])
             ->name('download')
             ->where('fileName', 'shortlisting_report_[\d\-_]+\.xlsx');
        Route::delete('/{fileName}', [ShortlistingReportController::class, 'deleteReport'])
             ->name('delete')
             ->where('fileName', 'shortlisting_report_[\d\-_]+\.xlsx');
    });
    
    // API Shortlisting Reports
    Route::prefix('api/shortlisting-reports')->name('api.shortlisting.')->group(function () {
        Route::post('/generate', [ShortlistingReportController::class, 'generateReport'])->name('generate');
        Route::get('/', [ShortlistingReportController::class, 'listReports'])->name('list');
        Route::get('/download/{fileName}', [ShortlistingReportController::class, 'downloadReport'])
             ->name('download')
             ->where('fileName', 'shortlisting_report_[\d\-_]+\.xlsx');
        Route::delete('/{fileName}', [ShortlistingReportController::class, 'deleteReport'])
             ->name('delete')
             ->where('fileName', 'shortlisting_report_[\d\-_]+\.xlsx');
    });
});

// ===============================================
// PUBLIC ROUTES - MUST COME AFTER SPECIFIC ROUTES
// ===============================================

// Public job browsing - accessible to everyone
Route::get('/jobs', [JobRequisitionController::class, 'index'])->name('job-requisitions.index');

// Public job PDF downloads
Route::get('job-requisitions/{id}/download-pdf', [JobRequisitionController::class, 'downloadPdf'])
    ->name('job-requisitions.download-pdf');

// This MUST come last because it's a wildcard route
Route::get('/jobs/{slugUuid}', [JobRequisitionController::class, 'show'])->name('job-requisitions.show');

// LinkedIn OAuth routes
Route::get('/auth/linkedin', [LinkedInController::class, 'redirectToLinkedIn'])->name('auth.linkedin');
Route::get('/auth/linkedin/callback', [LinkedInController::class, 'handleLinkedInCallback'])->name('auth.linkedin.callback');