<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RequirementRequestController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Google OAuth routes
Route::get('/login', function () {
    $testUsers = collect();
    if (app()->environment('local')) {
        // Exclude super_admin from fake login
        $testUsers = \App\Models\User::where('role', '!=', 'super_admin')->get();
    }
    return view('auth.login', compact('testUsers'));
})->name('login');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');

// Fake login for local development only
Route::post('/auth/fake-login', function (\Illuminate\Http\Request $request) {
    if (!app()->environment('local')) {
        abort(403, 'Fake login only available in local environment');
    }

    $user = \App\Models\User::find($request->user_id);
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->intended('/dashboard');
    }

    return back()->with('error', 'User not found');
})->name('auth.fake-login');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User settings routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/api/task-counts', [SettingsController::class, 'getTaskCounts'])->name('api.task-counts');

    // FMO User routes - create requests and view own requests
    Route::middleware('role:fmo_user,fmo_admin,super_admin')->group(function () {
        Route::get('/requests/create', [RequirementRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [RequirementRequestController::class, 'store'])->name('requests.store');
        Route::get('/my-requests/{status?}', [RequirementRequestController::class, 'myRequests'])->name('requests.my');
        Route::get('/requests/{request}/edit', [RequirementRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{request}', [RequirementRequestController::class, 'update'])->name('requests.update');
        Route::post('/requests/{requirementRequest}/cancel', [RequirementRequestController::class, 'cancel'])->name('requests.cancel');
        Route::delete('/requests/{requirementRequest}', [RequirementRequestController::class, 'destroy'])->name('requests.destroy');
    });

    // View request details (all authenticated users)
    Route::get('/requests/{request}', [RequirementRequestController::class, 'show'])->name('requests.show');

    // Secure attachment access (all authenticated users)
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');

    // FMO Admin routes - approve/reject and view all requests
    Route::middleware('role:fmo_admin,super_admin')->group(function () {
        Route::get('/requests', [RequirementRequestController::class, 'index'])->name('requests.index');
        Route::post('/requests/{request}/approve', [RequirementRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{request}/reject', [RequirementRequestController::class, 'reject'])->name('requests.reject');
    });

    // PO Admin routes - assign
    Route::middleware('role:po_admin,super_admin')->group(function () {
        Route::post('/requests/{requirementRequest}/assign', [RequirementRequestController::class, 'assign'])->name('requests.assign');
    });

    // PO User/Admin routes - view assigned requests, mark in progress and complete
    Route::middleware('role:po_user,po_admin,super_admin')->group(function () {
        Route::get('/my-assigned/{status?}', [RequirementRequestController::class, 'myAssignedRequests'])->name('requests.my-assigned');
        Route::post('/requests/{requirementRequest}/in-progress', [RequirementRequestController::class, 'markInProgress'])->name('requests.in-progress');
        Route::post('/requests/{requirementRequest}/complete', [RequirementRequestController::class, 'complete'])->name('requests.complete');
    });

    // Admin routes - user management (accessible by fmo_admin, po_admin, and super_admin)
    Route::middleware('role:fmo_admin,po_admin,super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
    });

    // Super admin only routes
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::delete('/users/delete-all', [UserController::class, 'deleteAllUsers'])->name('users.delete-all');
        Route::delete('/requests/delete-all', [UserController::class, 'deleteAllRequests'])->name('requests.delete-all');
        Route::get('/users/import', [UserController::class, 'showImport'])->name('users.import');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import.store');
        Route::get('/users/import/template', [UserController::class, 'downloadTemplate'])->name('users.import.template');
    });

    // Reports routes (accessible by fmo_admin and po_admin)
    Route::middleware('role:fmo_admin,po_admin,super_admin')->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');
    });
});
