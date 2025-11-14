<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BookTransactionController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DriveController;
use App\Http\Controllers\DriveItemController;
use App\Http\Controllers\DriveMemberController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskLabelController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TaskCustomFieldController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\UserItemController;
use App\Http\Controllers\PeopleManagerController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\PeopleManagerProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\UserSelfServiceController;
use App\Http\Controllers\DriveRoleController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Register broadcasting routes for real-time updates
// Broadcast::routes() uses 'web' middleware by default, which includes session and CSRF
// We need to add 'auth' middleware for authentication
Broadcast::routes(['middleware' => ['web', 'auth']]);

// Temporary debug route to test broadcasting auth
Route::post('/broadcasting/auth-debug', function (\Illuminate\Http\Request $request) {
    \Log::info('Broadcasting auth debug', [
        'user' => auth()->user()?->id,
        'authenticated' => auth()->check(),
        'csrf_token' => $request->header('X-CSRF-TOKEN'),
        'session_token' => csrf_token(),
        'all_headers' => $request->headers->all(),
    ]);
    return response()->json([
        'user' => auth()->user()?->id,
        'authenticated' => auth()->check(),
        'csrf_match' => $request->header('X-CSRF-TOKEN') === csrf_token(),
    ]);
})->middleware(['web', 'auth']);

Route::get('/', function () {
    return view('home');
})->name('home');

// Landing pages
Route::get('/invoicer', function () {
    return view('landing.invoicer');
})->name('landing.invoicer');

Route::get('/bookkeeper', function () {
    return view('landing.bookkeeper');
})->name('landing.bookkeeper');

Route::get('/project-board', function () {
    return view('landing.project-board');
})->name('landing.project-board');

Route::get('/people-manager', function () {
    return view('landing.people-manager');
})->name('landing.people-manager');

Route::get('/dashboard', function () {
    return redirect()->route('drives.index');
})->middleware('auth')->name('dashboard');

// Public project board route (no authentication required)
Route::get('public/board/{publicKey}', [ProjectController::class, 'publicShow'])->name('projects.public.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Drive routes
    Route::resource('drives', DriveController::class);
    Route::post('drives/{drive}/invite', [DriveMemberController::class, 'invite'])->name('drives.members.invite');
    Route::patch('drives/{drive}/members/{user}/role', [DriveMemberController::class, 'updateRole'])->name('drives.members.update-role');
    Route::delete('drives/{drive}/members/{user}', [DriveMemberController::class, 'remove'])->name('drives.members.remove');
    Route::post('drives/{drive}/leave', [DriveMemberController::class, 'leave'])->name('drives.members.leave');
    Route::post('drives/{drive}/members/{user}/transfer-ownership', [DriveMemberController::class, 'transferOwnership'])->name('drives.members.transfer-ownership');
    
    // Sub-drive routes
    Route::post('drives/{drive}/sub-drives', [DriveController::class, 'storeSubDrive'])->name('drives.sub-drives.store');
    Route::post('drives/{drive}/settings', [DriveController::class, 'updateSettings'])->name('drives.settings.update');
    
    // Drive item routes
    Route::post('drives/{drive}/items', [DriveItemController::class, 'store'])->name('drives.items.store');
    Route::patch('drives/{drive}/items/{item}', [DriveItemController::class, 'update'])->name('drives.items.update');
    Route::delete('drives/{drive}/items/{item}', [DriveItemController::class, 'destroy'])->name('drives.items.destroy');
    
    // Invoice routes
    Route::resource('drives.invoices', InvoiceController::class);
    Route::resource('drives.clients', ClientController::class);
    Route::resource('drives.user-items', UserItemController::class);
    Route::resource('drives.invoice-profiles', InvoiceProfileController::class);
    
    // BookKeeper routes
    Route::prefix('drives/{drive}/bookkeeper')->name('drives.bookkeeper.')->group(function () {
        Route::get('/', [BookTransactionController::class, 'dashboard'])->name('dashboard');
        Route::get('tax-report', [BookTransactionController::class, 'taxReport'])->name('tax-report');
        Route::resource('accounts', AccountController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('budgets', BudgetController::class);
        Route::resource('transactions', BookTransactionController::class);
        Route::get('transactions/{transaction}/attachments/{attachment}', [BookTransactionController::class, 'showAttachment'])->name('transactions.attachments.show');
        Route::delete('transactions/{transaction}/attachments/{attachment}', [BookTransactionController::class, 'destroyAttachment'])->name('transactions.attachments.destroy');
        
        // Recurring Transactions
        Route::get('recurring-transactions/upcoming', [RecurringTransactionController::class, 'upcoming'])->name('recurring-transactions.upcoming');
        Route::post('recurring-transactions/{recurringTransaction}/generate', [RecurringTransactionController::class, 'generate'])->name('recurring-transactions.generate');
        Route::post('recurring-transactions/{recurringTransaction}/skip', [RecurringTransactionController::class, 'skip'])->name('recurring-transactions.skip');
        Route::resource('recurring-transactions', RecurringTransactionController::class);
    });
    
    // Project Board routes
    Route::prefix('drives/{drive}/projects')->name('drives.projects.')->group(function () {
        Route::resource('projects', ProjectController::class);
        
               Route::prefix('projects/{project}')->name('projects.')->group(function () {
                   Route::resource('tasks', TaskController::class);
                   Route::post('tasks/{task}/duplicate', [TaskController::class, 'duplicate'])->name('tasks.duplicate');
                   Route::post('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
                   Route::post('tasks/{task}/update-labels-members', [TaskController::class, 'updateLabelsAndMembers'])->name('tasks.update-labels-members');
                   Route::post('tasks/{task}/archive', [TaskController::class, 'archive'])->name('tasks.archive');
                   Route::post('tasks/{task}/unarchive', [TaskController::class, 'unarchive'])->name('tasks.unarchive');
                   Route::post('tasks/{task}/dependencies', [TaskController::class, 'addDependency'])->name('tasks.dependencies.store');
                   Route::delete('tasks/{task}/dependencies/{dependency}', [TaskController::class, 'removeDependency'])->name('tasks.dependencies.destroy');
                   Route::get('tasks/{task}/attachments/{attachment}', [TaskController::class, 'showAttachment'])->name('tasks.attachments.show');
                   Route::delete('tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');

                   Route::post('task-statuses/reorder', [TaskStatusController::class, 'reorder'])->name('task-statuses.reorder');
                   Route::post('task-statuses', [TaskStatusController::class, 'store'])->name('task-statuses.store');
                   Route::patch('task-statuses/{taskStatus}', [TaskStatusController::class, 'update'])->name('task-statuses.update');
                   Route::delete('task-statuses/{taskStatus}', [TaskStatusController::class, 'destroy'])->name('task-statuses.destroy');
                   
                   // Task comments
                   Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
                   Route::patch('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'update'])->name('tasks.comments.update');
                   Route::delete('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
                   
                   // Project people assignment
                   Route::post('assign-people', [ProjectController::class, 'assignPeople'])->name('assign-people');
                   
                   // User preferences
                   Route::post('preferences', [ProjectController::class, 'savePreferences'])->name('preferences.store');
                   
                   // Task templates
                   Route::resource('task-templates', TaskTemplateController::class);
                   Route::post('custom-fields', [TaskCustomFieldController::class, 'store'])->name('custom-fields.store');
                   Route::delete('custom-fields/{customField}', [TaskCustomFieldController::class, 'destroy'])->name('custom-fields.destroy')->where('customField', '[0-9]+');
               });
        
        Route::resource('task-labels', TaskLabelController::class);
    });
    
    // People Manager routes
    Route::prefix('drives/{drive}/people-manager')->name('drives.people-manager.')->group(function () {
        Route::get('/', [PeopleManagerController::class, 'dashboard'])->name('dashboard');
        Route::resource('profiles', PeopleManagerProfileController::class);
        Route::resource('people', PeopleController::class);
        Route::get('schedules/builder', [ScheduleController::class, 'builder'])->name('schedules.builder');
        Route::post('schedules/bulk-create', [ScheduleController::class, 'bulkCreate'])->name('schedules.bulk-create');
        Route::resource('schedules', ScheduleController::class);
        Route::resource('time-logs', TimeLogController::class);
        Route::post('time-logs/{timeLog}/approve', [TimeLogController::class, 'approve'])->name('time-logs.approve');
        Route::post('time-logs/{timeLog}/reject', [TimeLogController::class, 'reject'])->name('time-logs.reject');
        Route::get('time-logs/{person}/print-report', [TimeLogController::class, 'printReport'])->name('time-logs.print-report');
        Route::resource('payroll', PayrollController::class);
        Route::post('payroll/{payrollEntry}/sync', [PayrollController::class, 'syncToBookKeeper'])->name('payroll.sync');
        Route::post('payroll/{payroll}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('payroll.mark-paid');
        Route::post('payroll/{payroll}/mark-unpaid', [PayrollController::class, 'markAsUnpaid'])->name('payroll.mark-unpaid');
        Route::post('payroll/{payrollEntry}/mark-paid-and-sync', [PayrollController::class, 'markAsPaidAndSync'])->name('payroll.mark-paid-and-sync');
        Route::post('payroll/generate-from-time-logs', [PayrollController::class, 'generateFromTimeLogs'])->name('payroll.generate-from-time-logs');
    });
    
    // Drive roles and permissions
    Route::prefix('drives/{drive}/roles')->name('drives.roles.')->group(function () {
        Route::get('/', [DriveRoleController::class, 'index'])->name('index');
        Route::get('create', [DriveRoleController::class, 'create'])->name('create');
        Route::post('/', [DriveRoleController::class, 'store'])->name('store');
        Route::get('{role}', [DriveRoleController::class, 'show'])->name('show');
        Route::get('{role}/edit', [DriveRoleController::class, 'edit'])->name('edit');
        Route::patch('{role}', [DriveRoleController::class, 'update'])->name('update');
        Route::delete('{role}', [DriveRoleController::class, 'destroy'])->name('destroy');
        Route::post('assign', [DriveRoleController::class, 'assignRole'])->name('assign');
        Route::delete('assignments/{assignment}', [DriveRoleController::class, 'removeAssignment'])->name('remove-assignment');
    });
    
    // User Self-Service routes (for users linked to People records)
    Route::prefix('drives/{drive}/my-time')->name('user-self-service.')->group(function () {
        Route::get('schedules', [UserSelfServiceController::class, 'schedules'])->name('schedules');
        Route::get('time-logs', [UserSelfServiceController::class, 'timeLogs'])->name('time-logs');
        Route::post('schedules/{schedule}/clock-in', [UserSelfServiceController::class, 'clockIn'])->name('clock-in');
        Route::post('schedules/{schedule}/clock-out', [UserSelfServiceController::class, 'clockOut'])->name('clock-out');
        Route::get('schedules/{schedule}/create-time-log', [UserSelfServiceController::class, 'createTimeLogForSchedule'])->name('create-time-log-for-schedule');
        Route::get('time-logs/{timeLog}/edit', [UserSelfServiceController::class, 'editTimeLog'])->name('edit-time-log');
        Route::patch('time-logs/{timeLog}', [UserSelfServiceController::class, 'updateTimeLog'])->name('update-time-log');
    });
    
    // Notification routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/password-reset', [AdminUserController::class, 'sendPasswordReset'])->name('users.password-reset');
    });
});

require __DIR__.'/auth.php';
