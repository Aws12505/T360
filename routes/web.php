<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImpersonationController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('{tenantSlug}')->group(function () {
        Route::get('dashboard', function (?string $tenantSlug = null) {
            return Inertia::render('Dashboard', [
                'tenantSlug' => $tenantSlug,
            ]);
        })->name('dashboard');

        Route::get('/users-roles', [UserController::class, 'index'])->name('users.roles.index');

    // User routes
    Route::post('/users', [UserController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroyUser'])->name('users.destroy');

    // Role routes
    Route::post('/roles', [UserController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{role}', [UserController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');
    
    });
});



Route::middleware(['auth', 'tenant'])->group(function () {
Route::get('admindashboard', function (?string $tenantSlug = null) {
    return Inertia::render('Dashboard', [
        'tenantSlug' => $tenantSlug,
    ]);
})->name('admin.dashboard');
Route::get('/adminusers-roles', [UserController::class, 'index'])->name('admin.users.roles.index');

    // User routes
    Route::post('/adminusers', [UserController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/adminusers/{user}', [UserController::class, 'updateUserAdmin'])->name('admin.users.update');
    Route::delete('/adminusers/{user}', [UserController::class, 'destroyUserAdmin'])->name('admin.users.destroy');

    // Role routes
    Route::post('/adminroles', [UserController::class, 'storeRole'])->name('admin.roles.store');
    Route::put('/adminroles/{role}', [UserController::class, 'updateRoleAdmin'])->name('admin.roles.update');
    Route::delete('/adminroles/{role}', [UserController::class, 'destroyRoleAdmin'])->name('admin.roles.destroy');

    Route::post('/admintenants', [UserController::class, 'storeTenant'])->name('admin.tenants.store');
    Route::put('/admintenants/{tenant}', [UserController::class, 'updateTenant'])->name('admin.tenants.update');
    Route::delete('/admintenants/{tenant}', [UserController::class, 'destroyTenant'])->name('admin.tenants.destroy');
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
