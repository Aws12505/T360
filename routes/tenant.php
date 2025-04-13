<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Truck\TruckController;
use App\Http\Controllers\Web\On_Time\DelaysController;
use App\Http\Controllers\Web\Safety\SafetyDataController;
use App\Http\Controllers\Web\UserManagement\UserController;
use App\Http\Controllers\Web\Acceptance\RejectionsController;
use App\Http\Controllers\Web\Performance\SummariesController;
use App\Http\Controllers\Web\Performance\PerformanceController;
use App\Http\Controllers\Web\UserManagement\ImpersonationController;
use App\Http\Controllers\Web\Driver\DriverController;
use App\Http\Controllers\Web\RepairOrder\RepairOrderController;
use App\Http\Controllers\Web\Miles\MilesDrivenController;
use App\Http\Controllers\Settings\TenantSettingsController;

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('{tenantSlug}')->group(function () {
        Route::get('dashboard',  [SummariesController::class, 'getSummaries'])->name('dashboard');

        // Tenant Settings routes
        Route::get('/settings/tenant', [TenantSettingsController::class, 'edit'])->name('tenant.settings.edit');
        Route::post('/settings/tenant', [TenantSettingsController::class, 'update'])->name('tenant.settings.update');

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
        
        Route::get('/repair-orders', [RepairOrderController::class, 'index'])->name('repair_orders.index');
        Route::post('/repair-orders', [RepairOrderController::class, 'store'])->name('repair_orders.store');
        Route::put('/repair-orders/{repair_order}', [RepairOrderController::class, 'update'])->name('repair_orders.update');
        Route::delete('/repair-orders/{repair_order}', [RepairOrderController::class, 'destroy'])->name('repair_orders.destroy');
        Route::post('/repair-orders/import', [RepairOrderController::class, 'import'])->name('repair_orders.import');
        Route::get('/repair-orders/export', [RepairOrderController::class, 'export'])->name('repair_orders.export');
    
        // Safety data routes
        Route::get('/safety', [SafetyDataController::class, 'index'])->name('safety.index');
        Route::post('/safety', [SafetyDataController::class, 'store'])->name('safety.store');
        Route::put('/safety/{id}', [SafetyDataController::class, 'update'])->name('safety.update');
        Route::delete('/safety/{id}', [SafetyDataController::class, 'destroy'])->name('safety.destroy');
        Route::delete('/safety-bulk', [SafetyDataController::class, 'destroyBulk'])->name('safety.destroyBulk');
        Route::post('/safety/import', [SafetyDataController::class, 'import'])->name('safety.import');
        Route::get('/safety/export', [SafetyDataController::class, 'export'])->name('safety.export');

        // Trucks data routes
        Route::get('/trucks', [TruckController::class, 'index'])->name('truck.index');
        Route::post('/trucks', [TruckController::class, 'store'])->name('truck.store');
        Route::put('/trucks/{truck}', [TruckController::class, 'update'])->name('truck.update');
        Route::delete('/trucks/{truck}', [TruckController::class, 'destroy'])->name('truck.destroy');
        Route::post('/trucks/import', [TruckController::class, 'import'])->name('truck.import');
        Route::get('/trucks/export', [TruckController::class, 'export'])->name('truck.export');

        // Delays routes
        Route::get('/ontime', [DelaysController::class, 'index'])->name('ontime.index');
        Route::post('/ontime', [DelaysController::class, 'store'])->name('ontime.store');
        Route::put('/ontime/{delay}', [DelaysController::class, 'update'])->name('ontime.update');
        Route::delete('/ontime/{delay}', [DelaysController::class, 'destroy'])->name('ontime.destroy');
        // The issue is likely here - the route pattern conflicts with the {delay} parameter route
        Route::delete('/ontime-bulk', [DelaysController::class, 'destroyBulk'])->name('ontime.destroyBulk');

// Regular Driver routes
Route::get('/drivers', [DriverController::class, 'index'])->name('driver.index');
Route::post('/drivers', [DriverController::class, 'store'])->name('driver.store');
Route::put('/drivers/{driver}', [DriverController::class, 'update'])->name('driver.update');
Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])->name('driver.destroy');
Route::post('/drivers/import', [DriverController::class, 'import'])->name('driver.import');
Route::get('/drivers/export', [DriverController::class, 'export'])->name('driver.export');

        // acceptance routes
        Route::get('/acceptance', [RejectionsController::class, 'index'])->name('acceptance.index');
        Route::post('/acceptance', [RejectionsController::class, 'store'])->name('acceptance.store');
        Route::put('/acceptance/{rejection}', [RejectionsController::class, 'update'])->name('acceptance.update');
        Route::delete('/acceptance/{rejection}', [RejectionsController::class, 'destroy'])->name('acceptance.destroy');
    
    
// Miles Driven routes
Route::get('/miles-driven', [MilesDrivenController::class, 'index'])->name('miles_driven.index');
Route::post('/miles-driven', [MilesDrivenController::class, 'store'])->name('miles_driven.store');
Route::put('/miles-driven/{milesDriven}', [MilesDrivenController::class, 'update'])->name('miles_driven.update');
Route::delete('/miles-driven/{milesDriven}', [MilesDrivenController::class, 'destroy'])->name('miles_driven.destroy');
    
    });
});

