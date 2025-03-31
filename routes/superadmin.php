<?php
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserManagement\UserController;
use App\Http\Controllers\Web\On_Time\DelaysController;
use App\Http\Controllers\Web\Acceptance\RejectionsController;
use App\Http\Controllers\Web\Safety\SafetyDataController;
use App\Http\Controllers\Web\Performance\PerformanceController;
use App\Http\Controllers\Web\UserManagement\ImpersonationController;
use App\Http\Controllers\Web\Performance\PerformanceMetricRuleController;
use App\Http\Controllers\Web\Truck\TruckController;
use App\Http\Controllers\Web\Driver\DriverController;
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

    // Admin Trucks data routes
    Route::get('/trucks', [TruckController::class, 'index'])->name('truck.index.admin');
    Route::post('/trucks', [TruckController::class, 'store'])->name('truck.store.admin');
    Route::put('/trucks/{truck}', [TruckController::class, 'updateAdmin'])->name('truck.update.admin');
    Route::delete('/trucks/{truck}', [TruckController::class, 'destroyAdmin'])->name('truck.destroy.admin');
    Route::post('/trucks/import', [TruckController::class, 'import'])->name('truck.import.admin');
    Route::get('/trucks/export', [TruckController::class, 'export'])->name('truck.export.admin');

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

// Admin Driver routes
Route::get('/drivers', [DriverController::class, 'index'])->name('driver.index.admin');
Route::post('/drivers', [DriverController::class, 'store'])->name('driver.store.admin');
Route::put('/drivers/{driver}', [DriverController::class, 'updateAdmin'])->name('driver.update.admin');
Route::delete('/drivers/{driver}', [DriverController::class, 'destroyAdmin'])->name('driver.destroy.admin');
Route::post('/drivers/import', [DriverController::class, 'import'])->name('driver.import.admin');
Route::get('/drivers/export', [DriverController::class, 'export'])->name('driver.export.admin');

    // Admin delay codes routes
    Route::post('/delay-codes', [DelaysController::class, 'storeCode'])->name('delay_codes.store.admin');
    Route::delete('/delay-codes/{delay_code}', [DelaysController::class, 'destroyCode'])->name('delay_codes.destroy.admin');
});