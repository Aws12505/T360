<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ZohoWebhookController;
use App\Http\Controllers\PerformanceMetricRuleController;


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
    // Route to stop impersonation
Route::get('/stopimpersonate', [ImpersonationController::class, 'stopImpersonation'])
->name('impersonate.stop');

Route::get('/performance-metric-rule/edit', [PerformanceMetricRuleController::class, 'edit'])->name('performance-metric-rule.edit');
Route::put('/performance-metric-rule', [PerformanceMetricRuleController::class, 'update'])->name('performance-metric-rule.update');

    });
});



Route::middleware(['auth', 'tenant','superAdmin'])->group(function () {
Route::get('admin/dashboard', function (?string $tenantSlug = null) {
    return Inertia::render('Dashboard', [
        'tenantSlug' => $tenantSlug,
    ]);
})->name('admin.dashboard');
Route::get('/admin/users-roles', [UserController::class, 'index'])->name('admin.users.roles.index');

    // User routes
    Route::post('/admin/users', [UserController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserController::class, 'updateUserAdmin'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroyUserAdmin'])->name('admin.users.destroy');

    // Role routes
    Route::post('/admin/roles', [UserController::class, 'storeRole'])->name('admin.roles.store');
    Route::put('/admin/roles/{role}', [UserController::class, 'updateRoleAdmin'])->name('admin.roles.update');
    Route::delete('/admin/roles/{role}', [UserController::class, 'destroyRoleAdmin'])->name('admin.roles.destroy');

    Route::post('/admin/tenants', [UserController::class, 'storeTenant'])->name('admin.tenants.store');
    Route::put('/admin/tenants/{tenant}', [UserController::class, 'updateTenant'])->name('admin.tenants.update');
    Route::delete('/admin/tenants/{tenant}', [UserController::class, 'destroyTenant'])->name('admin.tenants.destroy');
    Route::get('/impersonate/{id}', [ImpersonationController::class, 'impersonate'])
    ->name('impersonate.start');
    Route::get('/admin/performancemetrics', [PerformanceMetricRuleController::class, 'index'])->name('performance-metrics.index');
    Route::post('/admin/performancemetrics', [PerformanceMetricRuleController::class, 'store'])->name('performance-metrics.store');
    Route::put('/admin/performancemetrics/{id}', [PerformanceMetricRuleController::class, 'updateAdmin'])->name('performance-metrics.update');
    Route::get('/admin/performancemetrics/export', [PerformanceMetricRuleController::class, 'export'])->name('performance-metrics.export');
    Route::post('/admin/performancemetrics/import', [PerformanceMetricRuleController::class, 'import'])->name('performance-metrics.import');
});


Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
