<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BookTransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DriveController;
use App\Http\Controllers\DriveItemController;
use App\Http\Controllers\DriveMemberController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', function () {
    return redirect()->route('drives.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Drive routes
    Route::resource('drives', DriveController::class);
    Route::post('drives/{drive}/invite', [DriveMemberController::class, 'invite'])->name('drives.members.invite');
    Route::patch('drives/{drive}/members/{user}/role', [DriveMemberController::class, 'updateRole'])->name('drives.members.update-role');
    Route::delete('drives/{drive}/members/{user}', [DriveMemberController::class, 'remove'])->name('drives.members.remove');
    Route::post('drives/{drive}/leave', [DriveMemberController::class, 'leave'])->name('drives.members.leave');
    
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
        Route::resource('accounts', AccountController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('transactions', BookTransactionController::class);
        Route::get('transactions/{transaction}/attachments/{attachment}', [BookTransactionController::class, 'showAttachment'])->name('transactions.attachments.show');
        Route::delete('transactions/{transaction}/attachments/{attachment}', [BookTransactionController::class, 'destroyAttachment'])->name('transactions.attachments.destroy');
    });
    
    // Notification routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

require __DIR__.'/auth.php';
