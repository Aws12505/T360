<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ImpersonationController;
use App\Http\Controllers\Web\ZohoWebhookController;
use App\Http\Controllers\Web\PerformanceMetricRuleController;
use App\Http\Controllers\Web\PerformanceController;
use App\Http\Controllers\Web\SafetyDataController;
use App\Http\Controllers\Web\SummariesController;
use App\Http\Controllers\Web\RejectionsController;
use App\Http\Controllers\Web\DelaysController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('{tenantSlug}')->group(function () {
        Route::get('dashboard',  [SummariesController::class, 'getSummaries'])->name('dashboard');

        Route::get('/users-roles', [UserController::class, 'index'])->name('users.roles.index');

        // User routes
        Route::post('/users', [UserController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroyUser'])->name('users.destroy');

        // Role routes
        Route::post('/roles', [UserController::class, 'storeRole'])->name('roles.store');
        Route::put('/roles/{role}', [UserController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');

        // Stop impersonation
        Route::get('/stopimpersonate', [ImpersonationController::class, 'stopImpersonation'])->name('impersonate.stop');

        // Performance routes
        Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
        Route::post('/performance', [PerformanceController::class, 'store'])->name('performance.store');
        Route::put('/performance/{performance}', [PerformanceController::class, 'update'])->name('performance.update');
        Route::delete('/performance/{performance}', [PerformanceController::class, 'destroy'])->name('performance.destroy');
        Route::post('/performance/import', [PerformanceController::class, 'import'])->name('performance.import');
        Route::get('/performance/export', [PerformanceController::class, 'export'])->name('performance.export');

        // Safety data routes
        Route::get('/safety', [SafetyDataController::class, 'index'])->name('safety.index');
        Route::post('/safety', [SafetyDataController::class, 'store'])->name('safety.store');
        Route::put('/safety/{id}', [SafetyDataController::class, 'update'])->name('safety.update');
        Route::delete('/safety/{id}', [SafetyDataController::class, 'destroy'])->name('safety.destroy');
        Route::post('/safety/import', [SafetyDataController::class, 'import'])->name('safety.import');
        Route::get('/safety/export', [SafetyDataController::class, 'export'])->name('safety.export');

        // Delays routes
        Route::post('/ontime', [DelaysController::class, 'store'])->name('ontime.store');
        Route::put('/ontime/{delay}', [DelaysController::class, 'update'])->name('ontime.update');
        Route::delete('/ontime/{delay}', [DelaysController::class, 'destroy'])->name('ontime.destroy');
        Route::get('/ontime', [DelaysController::class, 'index'])->name('ontime.index');

        // acceptance routes
        Route::get('/acceptance', [RejectionsController::class, 'index'])->name('acceptance.index');
        Route::post('/acceptance', [RejectionsController::class, 'store'])->name('acceptance.store');
        Route::put('/acceptance/{rejection}', [RejectionsController::class, 'update'])->name('acceptance.update');
        Route::delete('/acceptance/{rejection}', [RejectionsController::class, 'destroy'])->name('acceptance.destroy');
    });
});

Route::middleware(['auth', 'superAdmin'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('admin.dashboard');
    Route::get('/users-roles', [UserController::class, 'index'])->name('admin.users.roles.index');

    // Admin user routes
    Route::post('/users', [UserController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'updateUserAdmin'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroyUserAdmin'])->name('admin.users.destroy');

    // Admin role routes
    Route::post('/roles', [UserController::class, 'storeRole'])->name('admin.roles.store');
    Route::put('/roles/{role}', [UserController::class, 'updateRoleAdmin'])->name('admin.roles.update');
    Route::delete('/roles/{role}', [UserController::class, 'destroyRoleAdmin'])->name('admin.roles.destroy');

    // Tenant routes
    Route::post('/tenants', [UserController::class, 'storeTenant'])->name('admin.tenants.store');
    Route::put('/tenants/{tenant}', [UserController::class, 'updateTenant'])->name('admin.tenants.update');
    Route::delete('/tenants/{tenant}', [UserController::class, 'destroyTenant'])->name('admin.tenants.destroy');

    // Impersonation routes
    Route::get('/impersonate/{id}', [ImpersonationController::class, 'impersonate'])->name('impersonate.start');

    // Performance metric rules
    Route::get('/performancemetrics', [PerformanceMetricRuleController::class, 'editGlobal'])->name('performance-metrics.edit');
    Route::post('/performancemetrics', [PerformanceMetricRuleController::class, 'updateGlobal'])->name('performance-metrics.update');

    // Admin performance routes
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index.admin');
    Route::post('/performance', [PerformanceController::class, 'store'])->name('performance.store.admin');
    Route::put('/performance/{performance}', [PerformanceController::class, 'adminUpdate'])->name('performance.update.admin');
    Route::delete('/performance/{performance}', [PerformanceController::class, 'adminDestroy'])->name('performance.destroy.admin');
    Route::post('/performance/import', [PerformanceController::class, 'import'])->name('performance.import.admin');
    Route::get('/performance/export', [PerformanceController::class, 'export'])->name('performance.export.admin');

    // Admin safety data routes
    Route::get('/safety', [SafetyDataController::class, 'index'])->name('safety.index.admin');
    Route::post('/safety', [SafetyDataController::class, 'store'])->name('safety.store.admin');
    Route::put('/safety/{id}', [SafetyDataController::class, 'updateAdmin'])->name('safety.update.admin');
    Route::delete('/safety/{id}', [SafetyDataController::class, 'destroyAdmin'])->name('safety.destroy.admin');
    Route::post('/safety/import', [SafetyDataController::class, 'import'])->name('safety.import.admin');
    Route::get('/safety/export', [SafetyDataController::class, 'export'])->name('safety.export.admin');

    // Admin acceptance routes
    Route::get('/acceptance', [RejectionsController::class, 'index'])->name('acceptance.index.admin');
    Route::get('/ontime', [DelaysController::class, 'index'])->name('ontime.index.admin');
    Route::post('/acceptance', [RejectionsController::class, 'store'])->name('acceptance.store.admin');
    Route::put('/acceptance/{rejection}', [RejectionsController::class, 'updateAdmin'])->name('acceptance.update.admin');
    Route::delete('/acceptance/{rejection}', [RejectionsController::class, 'destroyAdmin'])->name('acceptance.destroy.admin');

    // Admin rejection reason codes routes
    Route::post('/rejection-reason-codes', [RejectionsController::class, 'storeCode'])->name('rejection_reason_codes.store.admin');
    Route::delete('/rejection-reason-codes/{rejection_reason_code}', [RejectionsController::class, 'destroyCode'])->name('rejection_reason_codes.destroy.admin');

    // Admin delays routes
    Route::post('/ontime', [DelaysController::class, 'store'])->name('ontime.store.admin');
    Route::put('/ontime/{delay}', [DelaysController::class, 'updateAdmin'])->name('ontime.update.admin');
    Route::delete('/ontime/{delay}', [DelaysController::class, 'destroyAdmin'])->name('ontime.destroy.admin');

    // Admin delay codes routes
    Route::post('/delay-codes', [DelaysController::class, 'storeCode'])->name('delay_codes.store.admin');
    Route::delete('/delay-codes/{delay_code}', [DelaysController::class, 'destroyCode'])->name('delay_codes.destroy.admin');
});

Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
