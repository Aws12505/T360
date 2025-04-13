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


// Admin repair order routes
Route::get('/repair-orders', [RepairOrderController::class, 'index'])->name('repair_orders.index.admin');
Route::post('/repair-orders', [RepairOrderController::class, 'store'])->name('repair_orders.store.admin');
Route::put('/repair-orders/{repair_order}', [RepairOrderController::class, 'updateAdmin'])->name('repair_orders.update.admin');
Route::delete('/repair-orders/{repair_order}', [RepairOrderController::class, 'destroyAdmin'])->name('repair_orders.destroy.admin');
Route::post('/repair-orders/import', [RepairOrderController::class, 'import'])->name('repair_orders.import.admin');
Route::get('/repair-orders/export', [RepairOrderController::class, 'export'])->name('repair_orders.export.admin');

// Areas of concern and vendors management routes
Route::post('/areas-of-concern', [RepairOrderController::class, 'storeAreaOfConcern'])->name('area_of_concerns.store.admin');
Route::delete('/areas-of-concern/{id}', [RepairOrderController::class, 'destroyAreaOfConcern'])->name('area_of_concerns.destroy.admin');
Route::post('/vendors', [RepairOrderController::class, 'storeVendor'])->name('vendors.store.admin');
Route::delete('/vendors/{id}', [RepairOrderController::class, 'destroyVendor'])->name('vendors.destroy.admin');

    // Admin delay codes routes
    Route::post('/delay-codes', [DelaysController::class, 'storeCode'])->name('delay_codes.store.admin');
    Route::delete('/delay-codes/{delay_code}', [DelaysController::class, 'destroyCode'])->name('delay_codes.destroy.admin');

    // Miles Driven routes
Route::get('/miles-driven', [MilesDrivenController::class, 'index'])->name('miles_driven.index.admin');
Route::post('/miles-driven', [MilesDrivenController::class, 'store'])->name('miles_driven.store.admin');
Route::put('/miles-driven/{milesDriven}', [MilesDrivenController::class, 'updateAdmin'])->name('miles_driven.update.admin');
Route::delete('/miles-driven/{milesDriven}', [MilesDrivenController::class, 'destroyAdmin'])->name('miles_driven.destroy.admin');
   

 // Repair Orders
 Route::post('/repair-orders/{id}/restore', [RepairOrderController::class, 'restore'])->name('repair-orders.restore');
 Route::delete('/repair-orders/{id}/force', [RepairOrderController::class, 'forceDestroy'])->name('repair-orders.force-destroy');
 
 // Areas of Concern
 Route::post('/areas-of-concern/{id}/restore', [RepairOrderController::class, 'restoreAreaOfConcern'])->name('areas-of-concern.restore');
 Route::delete('/areas-of-concern/{id}/force', [RepairOrderController::class, 'forceDestroyAreaOfConcern'])->name('areas-of-concern.force-destroy');
 Route::get('/areas-of-concern/with-trashed', [RepairOrderController::class, 'getAllAreasOfConcernWithTrashed'])->name('areas-of-concern.with-trashed');
 
 // Vendors
 Route::post('/vendors/{id}/restore', [RepairOrderController::class, 'restoreVendor'])->name('vendors.restore');
 Route::delete('/vendors/{id}/force', [RepairOrderController::class, 'forceDestroyVendor'])->name('vendors.force-destroy');
 Route::get('/vendors/with-trashed', [RepairOrderController::class, 'getAllVendorsWithTrashed'])->name('vendors.with-trashed');
 
 // Rejections
 Route::post('/rejections/{id}/restore', [RejectionsController::class, 'restore'])->name('rejections.restore');
 Route::delete('/rejections/{id}/force', [RejectionsController::class, 'forceDestroy'])->name('rejections.force-destroy');
 
 // Rejection Reason Codes
 Route::post('/rejection-reason-codes/{id}/restore', [RejectionsController::class, 'restoreCode'])->name('rejection-reason-codes.restore');
 Route::delete('/rejection-reason-codes/{id}/force', [RejectionsController::class, 'forceDestroyCode'])->name('rejection-reason-codes.force-destroy');
 Route::get('/rejection-reason-codes/with-trashed', [RejectionsController::class, 'getAllCodesWithTrashed'])->name('rejection-reason-codes.with-trashed');
 
 // Delays
 Route::post('/delays/{id}/restore', [DelaysController::class, 'restore'])->name('delays.restore');
 Route::delete('/delays/{id}/force', [DelaysController::class, 'forceDestroy'])->name('delays.force-destroy');
 
 // Delay Codes
 Route::post('/delay-codes/{id}/restore', [DelaysController::class, 'restoreCode'])->name('delay-codes.restore');
 Route::delete('/delay-codes/{id}/force', [DelaysController::class, 'forceDestroyCode'])->name('delay-codes.force-destroy');
 Route::get('/delay-codes/with-trashed', [DelaysController::class, 'getAllCodesWithTrashed'])->name('delay-codes.with-trashed');

});