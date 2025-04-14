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
use App\Http\Controllers\Web\RepairOrder\RepairOrderController;
use App\Http\Controllers\Web\Miles\MilesDrivenController;
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
    Route::get('/metrics', [PerformanceMetricRuleController::class, 'editGlobal'])->name('performance-metrics.edit');
    Route::post('/metrics', [PerformanceMetricRuleController::class, 'updateGlobal'])->name('performance-metrics.update');

    // Admin performance routes
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index.admin');
    Route::post('/performance', [PerformanceController::class, 'store'])->name('performance.store.admin');
    Route::put('/performance/{performance}', [PerformanceController::class, 'adminUpdate'])->name('performance.update.admin');
    Route::delete('/performance/{performance}', [PerformanceController::class, 'adminDestroy'])->name('performance.destroy.admin');
    Route::delete('/performance-bulk', [PerformanceController::class, 'destroyBulkAdmin'])->name('performance.destroyBulk.admin');
    Route::post('/performance/import', [PerformanceController::class, 'import'])->name('performance.import.admin');
    Route::get('/performance/export', [PerformanceController::class, 'export'])->name('performance.export.admin');

    // Admin safety data routes
    Route::get('/safety', [SafetyDataController::class, 'index'])->name('safety.index.admin');
    Route::post('/safety', [SafetyDataController::class, 'store'])->name('safety.store.admin');
    Route::put('/safety/{id}', [SafetyDataController::class, 'updateAdmin'])->name('safety.update.admin');
    Route::delete('/safety/{id}', [SafetyDataController::class, 'destroyAdmin'])->name('safety.destroy.admin');
    Route::delete('/safety-bulk', [SafetyDataController::class, 'destroyBulkAdmin'])->name('safety.destroyBulk.admin');
    Route::post('/safety/import', [SafetyDataController::class, 'import'])->name('safety.import.admin');
    Route::get('/safety/export', [SafetyDataController::class, 'export'])->name('safety.export.admin');

    // Admin Trucks data routes
    Route::get('/trucks', [TruckController::class, 'index'])->name('truck.index.admin');
    Route::post('/trucks', [TruckController::class, 'store'])->name('truck.store.admin');
    Route::put('/trucks/{truck}', [TruckController::class, 'updateAdmin'])->name('truck.update.admin');
    Route::delete('/trucks/{truck}', [TruckController::class, 'destroyAdmin'])->name('truck.destroy.admin');
    Route::delete('/trucks-bulk', [TruckController::class, 'destroyBulkAdmin'])->name('truck.destroyBulk.admin');
    Route::post('/trucks/import', [TruckController::class, 'import'])->name('truck.import.admin');
    Route::get('/trucks/export', [TruckController::class, 'export'])->name('truck.export.admin');

    // Admin acceptance routes
    Route::get('/acceptance', [RejectionsController::class, 'index'])->name('acceptance.index.admin');
    Route::post('/acceptance', [RejectionsController::class, 'store'])->name('acceptance.store.admin');
    Route::put('/acceptance/{rejection}', [RejectionsController::class, 'updateAdmin'])->name('acceptance.update.admin');
    Route::delete('/acceptance/{rejection}', [RejectionsController::class, 'destroyAdmin'])->name('acceptance.destroy.admin');
    Route::delete('/acceptance-bulk', [RejectionsController::class, 'destroyBulkAdmin'])->name('acceptance.destroyBulk.admin');
// Add new import/export routes for rejections
Route::post('/acceptance/import', [RejectionsController::class, 'importAdmin'])->name('acceptance.import.admin');
Route::get('/acceptance/export', [RejectionsController::class, 'exportAdmin'])->name('acceptance.export.admin');
    // Admin rejection reason codes routes
    Route::post('/rejection-reason-codes', [RejectionsController::class, 'storeReasonCodeAdmin'])->name('rejection_reason_codes.store.admin');
    Route::put('/rejection-reason-codes/{code}', [RejectionsController::class, 'updateReasonCodeAdmin'])->name('rejection_reason_codes.update.admin');
    Route::delete('/rejection-reason-codes/{id}', [RejectionsController::class, 'destroyReasonCodeAdmin'])->name('rejection_reason_codes.destroy.admin');
    Route::post('/rejection-reason-codes/{id}/restore', [RejectionsController::class, 'restoreReasonCodeAdmin'])->name('rejection_reason_codes.restore.admin');
    Route::delete('/rejection-reason-codes/{id}/force', [RejectionsController::class, 'forceDeleteReasonCodeAdmin'])->name('rejection_reason_codes.forceDelete.admin');
    Route::get('/ontime', [DelaysController::class, 'index'])->name('ontime.index.admin');
    Route::post('/acceptance', [RejectionsController::class, 'store'])->name('acceptance.store.admin');
    Route::put('/acceptance/{rejection}', [RejectionsController::class, 'updateAdmin'])->name('acceptance.update.admin');
    Route::delete('/acceptance/{rejection}', [RejectionsController::class, 'destroyAdmin'])->name('acceptance.destroy.admin');

    // Admin rejection reason codes routes
    Route::post('/rejection-reason-codes', [RejectionsController::class, 'storeCode'])->name('rejection_reason_codes.store.admin');
    Route::delete('/rejection-reason-codes/{id}', [RejectionsController::class, 'destroyCode'])->name('rejection_reason_codes.destroy.admin');
    Route::post('/rejection-reason-codes/{id}/restore', [RejectionsController::class, 'restoreCode'])->name('rejection_reason_codes.restore.admin');
    Route::delete('/rejection-reason-codes/{id}/force', [RejectionsController::class, 'forceDeleteCode'])->name('rejection_reason_codes.forceDelete.admin');

    // Admin delays routes
    // Admin delays routes
    Route::get('/ontime', [DelaysController::class, 'index'])->name('ontime.index.admin');
    Route::post('/ontime', [DelaysController::class, 'store'])->name('ontime.store.admin');
    Route::put('/ontime/{delay}', [DelaysController::class, 'updateAdmin'])->name('ontime.update.admin');
    Route::delete('/ontime/{delay}', [DelaysController::class, 'destroyAdmin'])->name('ontime.destroy.admin');
    Route::delete('/ontime-bulk', [DelaysController::class, 'destroyBulkAdmin'])->name('ontime.destroyBulk.admin');
    // Add new import/export routes
    Route::post('/ontime/import', [DelaysController::class, 'importAdmin'])->name('ontime.import.admin');
    Route::get('/ontime/export', [DelaysController::class, 'exportAdmin'])->name('ontime.export.admin');

// Admin Driver routes
Route::get('/drivers', [DriverController::class, 'index'])->name('driver.index.admin');
Route::post('/drivers', [DriverController::class, 'store'])->name('driver.store.admin');
Route::put('/drivers/{driver}', [DriverController::class, 'updateAdmin'])->name('driver.update.admin');
Route::delete('/drivers/{driver}', [DriverController::class, 'destroyAdmin'])->name('driver.destroy.admin');
Route::post('/drivers/import', [DriverController::class, 'import'])->name('driver.import.admin');
Route::get('/drivers/export', [DriverController::class, 'export'])->name('driver.export.admin');
Route::delete('/drivers-bulk', [DriverController::class, 'destroyBulkAdmin'])->name('driver.destroyBulk.admin');


// Admin repair orders routes
Route::get('/repair-orders', [RepairOrderController::class, 'index'])->name('repair_orders.index.admin');
Route::post('/repair-orders', [RepairOrderController::class, 'store'])->name('repair_orders.store.admin');
Route::put('/repair-orders/{repair_order}', [RepairOrderController::class, 'updateAdmin'])->name('repair_orders.update.admin');
Route::delete('/repair-orders/{repair_order}', [RepairOrderController::class, 'destroyAdmin'])->name('repair_orders.destroy.admin');
Route::delete('/repair-orders-bulk', [RepairOrderController::class, 'destroyBulkAdmin'])->name('repair_orders.destroyBulk.admin');
Route::post('/repair-orders/import', [RepairOrderController::class, 'import'])->name('repair_orders.import.admin');
Route::get('/repair-orders/export', [RepairOrderController::class, 'export'])->name('repair_orders.export.admin');

// Areas of concern and vendors management routes
Route::post('/areas-of-concern', [RepairOrderController::class, 'storeAreaOfConcern'])->name('area_of_concerns.store.admin');
Route::delete('/areas-of-concern/{id}', [RepairOrderController::class, 'destroyAreaOfConcern'])->name('area_of_concerns.destroy.admin');
Route::post('/areas-of-concern/{id}/restore', [RepairOrderController::class, 'restoreAreaOfConcern'])->name('area_of_concerns.restore.admin');
Route::delete('/areas-of-concern/{id}/force', [RepairOrderController::class, 'forceDeleteAreaOfConcern'])->name('area_of_concerns.forceDelete.admin');

Route::post('/vendors', [RepairOrderController::class, 'storeVendor'])->name('vendors.store.admin');
Route::delete('/vendors/{id}', [RepairOrderController::class, 'destroyVendor'])->name('vendors.destroy.admin');
Route::post('/vendors/{id}/restore', [RepairOrderController::class, 'restoreVendor'])->name('vendors.restore.admin');
Route::delete('/vendors/{id}/force', [RepairOrderController::class, 'forceDeleteVendor'])->name('vendors.forceDelete.admin');

    // Admin delay codes routes
    Route::post('/delay-codes', [DelaysController::class, 'storeCode'])->name('delay_codes.store.admin');

    // Miles Driven routes
Route::get('/miles-driven', [MilesDrivenController::class, 'index'])->name('miles_driven.index.admin');
Route::post('/miles-driven', [MilesDrivenController::class, 'store'])->name('miles_driven.store.admin');
Route::put('/miles-driven/{milesDriven}', [MilesDrivenController::class, 'updateAdmin'])->name('miles_driven.update.admin');
Route::delete('/miles-driven/{milesDriven}', [MilesDrivenController::class, 'destroyAdmin'])->name('miles_driven.destroy.admin');
Route::delete('/delay-codes/{id}', [DelaysController::class, 'destroyCode'])->name('delay_codes.destroy.admin');
Route::post('/delay-codes/{id}/restore', [DelaysController::class, 'restoreCode'])->name('delay_codes.restore.admin');
Route::delete('/delay-codes/{id}/force', [DelaysController::class, 'forceDeleteCode'])->name('delay_codes.forceDelete.admin');
});