<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NudgeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RequirementRequestController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Redirect login page to home
Route::get('/login', function () {
    if (app()->environment('local')) {
        $testUsers = \App\Models\User::whereIn('role', ['fmo_user', 'fmo_admin', 'po_admin', 'po_user', 'super_admin'])
            ->orderByRaw("CASE role WHEN 'super_admin' THEN 1 WHEN 'fmo_admin' THEN 2 WHEN 'fmo_user' THEN 3 WHEN 'po_admin' THEN 4 WHEN 'po_user' THEN 5 ELSE 6 END")
            ->get();
        return view('auth.login', compact('testUsers'));
    }
    return redirect('/');
})->name('login');

// Google OAuth routes
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
    Route::get('/api/nudge-counts', [NudgeController::class, 'getUnreadCount'])->name('api.nudge-counts');

    // Nudge routes - FMO side sends update requests
    Route::middleware('role:fmo_user,fmo_admin,super_admin')->group(function () {
        Route::post('/requests/{requirementRequest}/nudge', [NudgeController::class, 'store'])->name('nudges.store');
        Route::post('/nudges/{nudge}/mark-reply-seen', [NudgeController::class, 'markReplySeen'])->name('nudges.mark-reply-seen');
        Route::post('/requests/{requirementRequest}/mark-completed-seen', [NudgeController::class, 'markCompletedSeen'])->name('nudges.mark-completed-seen');
    });

    // Nudge routes - PO side responds
    Route::middleware('role:po_user,po_admin,super_admin')->group(function () {
        Route::post('/nudges/{nudge}/acknowledge', [NudgeController::class, 'acknowledge'])->name('nudges.acknowledge');
        Route::post('/nudges/{nudge}/reply', [NudgeController::class, 'reply'])->name('nudges.reply');
    });

    // FMO User routes - create requests and view own requests
    Route::middleware('role:fmo_user,fmo_admin,super_admin')->group(function () {
        Route::get('/requests/create', [RequirementRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [RequirementRequestController::class, 'store'])->name('requests.store');
        Route::get('/my-requests/{status?}', [RequirementRequestController::class, 'myRequests'])->name('requests.my');
        Route::get('/requests/{request}/edit', [RequirementRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{requirementRequest}', [RequirementRequestController::class, 'update'])->name('requests.update');
        Route::post('/requests/{requirementRequest}/cancel', [RequirementRequestController::class, 'cancel'])->name('requests.cancel');
        Route::delete('/requests/{requirementRequest}', [RequirementRequestController::class, 'destroy'])->name('requests.destroy');
        Route::post('/requests/{requirementRequest}/resubmit', [RequirementRequestController::class, 'resubmit'])->name('requests.resubmit');
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
        Route::post('/requests/{requirementRequest}/clarification', [RequirementRequestController::class, 'requestClarification'])->name('requests.clarification');
    });

    // Group Approver routes - accessible by fmo_user and fmo_admin who are designated group approvers
    Route::middleware('role:fmo_user,fmo_admin,super_admin')->group(function () {
        Route::post('/requests/{requirementRequest}/group-approve', [RequirementRequestController::class, 'groupApprove'])->name('requests.group-approve');
        Route::post('/requests/{requirementRequest}/group-reject', [RequirementRequestController::class, 'groupReject'])->name('requests.group-reject');
        Route::post('/requests/{requirementRequest}/group-clarification', [RequirementRequestController::class, 'groupRequestClarification'])->name('requests.group-clarification');
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
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Super admin only routes
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::delete('/users/delete-all', [UserController::class, 'deleteAllUsers'])->name('users.delete-all');
        Route::delete('/requests/delete-all', [UserController::class, 'deleteAllRequests'])->name('requests.delete-all');
        Route::get('/users/import', [UserController::class, 'showImport'])->name('users.import');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import.store');
        Route::get('/users/import/template', [UserController::class, 'downloadTemplate'])->name('users.import.template');
    });

    // Category management — FMO Admin + Super Admin only
    Route::middleware('role:fmo_admin,super_admin')->prefix('admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // FMO Group management — FMO Admin + Super Admin only
    Route::middleware('role:fmo_admin,super_admin')->prefix('admin/fmo-groups')->name('admin.fmo-groups.')->group(function () {
        Route::get('/', [UserGroupController::class, 'index'])->defaults('type', 'fmo')->name('index');
        Route::get('/create', [UserGroupController::class, 'create'])->defaults('type', 'fmo')->name('create');
        Route::post('/', [UserGroupController::class, 'store'])->defaults('type', 'fmo')->name('store');
        Route::get('/{userGroup}/edit', [UserGroupController::class, 'edit'])->name('edit');
        Route::put('/{userGroup}', [UserGroupController::class, 'update'])->name('update');
        Route::delete('/{userGroup}', [UserGroupController::class, 'destroy'])->name('destroy');
        Route::post('/{userGroup}/members', [UserGroupController::class, 'addMember'])->name('members.add');
        Route::delete('/{userGroup}/members/{user}', [UserGroupController::class, 'removeMember'])->name('members.remove');
    });

    // PO Group management — PO Admin + Super Admin only
    Route::middleware('role:po_admin,super_admin')->prefix('admin/po-groups')->name('admin.po-groups.')->group(function () {
        Route::get('/', [UserGroupController::class, 'index'])->defaults('type', 'po')->name('index');
        Route::get('/create', [UserGroupController::class, 'create'])->defaults('type', 'po')->name('create');
        Route::post('/', [UserGroupController::class, 'store'])->defaults('type', 'po')->name('store');
        Route::get('/{userGroup}/edit', [UserGroupController::class, 'edit'])->name('edit');
        Route::put('/{userGroup}', [UserGroupController::class, 'update'])->name('update');
        Route::delete('/{userGroup}', [UserGroupController::class, 'destroy'])->name('destroy');
        Route::post('/{userGroup}/members', [UserGroupController::class, 'addMember'])->name('members.add');
        Route::delete('/{userGroup}/members/{user}', [UserGroupController::class, 'removeMember'])->name('members.remove');
    });

    // Reports routes (accessible by fmo_admin and po_admin)
    Route::middleware('role:fmo_admin,po_admin,super_admin')->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');
    });
});
