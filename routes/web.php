<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('{tenantSlug?}')->group(function () {
        Route::get('dashboard', function (?string $tenantSlug = null) {
            return Inertia::render('Dashboard', [
                'tenantSlug' => $tenantSlug,
            ]);
        })->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index'); // Show users list
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create'); // Show create form
    Route::post('/users', [UserController::class, 'store'])->name('users.store'); // Store user
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit'); // Show edit form
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update'); // Update user
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy'); // Delete user
    });
});
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('admin')->group(function () {
Route::get('dashboard', function (?string $tenantSlug = null) {
    return Inertia::render('Dashboard', [
        'tenantSlug' => $tenantSlug,
    ]);
})->name('admin.dashboard');
Route::get('/users', [UserController::class, 'index'])->name('admin.users.index'); // Show users list
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create'); // Show create form
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store'); // Store user
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit'); // Show edit form
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update'); // Update user
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy'); // Delete user
});
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
