<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ZohoWebhookController;
use App\Http\Controllers\PerformanceMetricRuleController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\SafetyDataController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('{tenantSlug}')->group(function () {
        Route::get('dashboard', function (string $tenantSlug) {
            return Inertia::render('Dashboard',[
                'tenantSlug' => $tenantSlug, // Pass the prefix parameter
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
    // Route to stop impersonation
Route::get('/stopimpersonate', [ImpersonationController::class, 'stopImpersonation'])
->name('impersonate.stop');

Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
Route::post('/performance', [PerformanceController::class, 'store'])->name('performance.store');
Route::put('/performance/{performance}', [PerformanceController::class, 'update'])->name('performance.update');
Route::delete('/performance/{performance}', [PerformanceController::class, 'destroy'])->name('performance.destroy');
Route::post('/performance/import', [PerformanceController::class, 'import'])->name('performance.import');
Route::get('/performance/export', [PerformanceController::class, 'export'])->name('performance.export');
    
Route::get('/safety', [SafetyDataController::class, 'index'])->name('safety.index');
    Route::post('/safety', [SafetyDataController::class, 'store'])->name('safety.store');
    Route::put('/safety/{id}', [SafetyDataController::class, 'update'])->name('safety.update');
    Route::delete('/safety/{id}', [SafetyDataController::class, 'destroy'])->name('safety.destroy');
    Route::post('/safety/import', [SafetyDataController::class, 'import'])->name('safety.import');
    Route::get('/safety/export', [SafetyDataController::class, 'export'])->name('safety.export');

});
});



Route::middleware(['auth','superAdmin'])->group(function () {
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('admin.dashboard');
Route::get('/users-roles', [UserController::class, 'index'])->name('admin.users.roles.index');

    // User routes
    Route::post('/users', [UserController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'updateUserAdmin'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroyUserAdmin'])->name('admin.users.destroy');

    // Role routes
    Route::post('/roles', [UserController::class, 'storeRole'])->name('admin.roles.store');
    Route::put('/roles/{role}', [UserController::class, 'updateRoleAdmin'])->name('admin.roles.update');
    Route::delete('/roles/{role}', [UserController::class, 'destroyRoleAdmin'])->name('admin.roles.destroy');

    Route::post('/tenants', [UserController::class, 'storeTenant'])->name('admin.tenants.store');
    Route::put('/tenants/{tenant}', [UserController::class, 'updateTenant'])->name('admin.tenants.update');
    Route::delete('/tenants/{tenant}', [UserController::class, 'destroyTenant'])->name('admin.tenants.destroy');
    Route::get('/impersonate/{id}', [ImpersonationController::class, 'impersonate'])
    ->name('impersonate.start');
    Route::get('/performancemetrics', [PerformanceMetricRuleController::class, 'editGlobal'])->name('performance-metrics.edit');
    Route::post('/performancemetrics', [PerformanceMetricRuleController::class, 'updateGlobal'])->name('performance-metrics.update');
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index.admin');
Route::post('/performance', [PerformanceController::class, 'store'])->name('performance.store.admin');
Route::put('/performance/{performance}', [PerformanceController::class, 'adminUpdate'])->name('performance.update.admin');
Route::delete('/performance/{performance}', [PerformanceController::class, 'adminDestroy'])->name('performance.destroy.admin');
Route::post('/performance/import', [PerformanceController::class, 'import'])->name('performance.import.admin');
Route::get('/performance/export', [PerformanceController::class, 'export'])->name('performance.export.admin');

Route::get('/safety', [SafetyDataController::class, 'index'])->name('safety.index.admin');
Route::post('/safety', [SafetyDataController::class, 'store'])->name('safety.store.admin');
Route::put('/safety/{id}', [SafetyDataController::class, 'updateAdmin'])->name('safety.update.admin');
Route::delete('/safety/{id}', [SafetyDataController::class, 'destroyAdmin'])->name('safety.destroy.admin');
Route::post('/safety/import', [SafetyDataController::class, 'import'])->name('safety.import.admin');
Route::get('/safety/export', [SafetyDataController::class, 'export'])->name('safety.export.admin');
});


Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
