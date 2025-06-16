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
use App\Http\Controllers\Web\Support\TicketController;
use App\Http\Controllers\Web\Support\FeedbackController;
use App\Http\Controllers\Web\Support\TicketResponseController;
use App\Http\Controllers\Web\SMSCoaching\SMSScoresThresholdsController;
use App\Http\Controllers\Web\SMSCoaching\SMSCoachingTemplatesController;  

Route::middleware(['auth', 'tenant'])
     ->prefix('{tenantSlug}')
     ->group(function () {

    // Dashboard
    Route::get('dashboard', [SummariesController::class, 'getSummaries'])
    ->name('dashboard');

    Route::get('settings/tenant', [TenantSettingsController::class, 'edit'])
         ->name('tenant.settings.edit')
         ->middleware('permission:tenant-settings.view');
    Route::post('settings/tenant', [TenantSettingsController::class, 'update'])
         ->name('tenant.settings.update')
         ->middleware('permission:tenant-settings.update');

    // Users & Roles
    Route::get('users-roles', [UserController::class, 'index'])
         ->name('users.roles.index')
         ->middleware('permission:users.view|roles.view');
    Route::post('users', [UserController::class, 'storeUser'])
         ->name('users.store')
         ->middleware('permission:users.create');
    Route::put('users/{user}', [UserController::class, 'updateUser'])
         ->name('users.update')
         ->middleware('permission:users.update');
    Route::delete('users/{user}', [UserController::class, 'destroyUser'])
         ->name('users.destroy')
         ->middleware('permission:users.delete');

    Route::post('roles', [UserController::class, 'storeRole'])
         ->name('roles.store')
         ->middleware('permission:roles.create');
    Route::put('roles/{role}', [UserController::class, 'updateRole'])
         ->name('roles.update')
         ->middleware('permission:roles.update');
    Route::delete('roles/{role}', [UserController::class, 'destroyRole'])
         ->name('roles.destroy')
         ->middleware('permission:roles.delete');

    // Impersonation
    Route::get('stopimpersonate', [ImpersonationController::class, 'stopImpersonation'])
         ->name('impersonate.stop');

    // Performance
    Route::get('performance', [PerformanceController::class, 'index'])
         ->name('performance.index')
         ->middleware('permission:performance.view');
    Route::post('performance', [PerformanceController::class, 'store'])
         ->name('performance.store')
         ->middleware('permission:performance.create');
    Route::put('performance/{performance}', [PerformanceController::class, 'update'])
         ->name('performance.update')
         ->middleware('permission:performance.update');
    Route::delete('performance/{performance}', [PerformanceController::class, 'destroy'])
         ->name('performance.destroy')
         ->middleware('permission:performance.delete');
    Route::delete('performance-bulk', [PerformanceController::class, 'destroyBulk'])
         ->name('performance.destroyBulk')
         ->middleware('permission:performance.delete');
    Route::post('performance/import', [PerformanceController::class, 'import'])
         ->name('performance.import')
         ->middleware('permission:performance.import');
    Route::get('performance/export', [PerformanceController::class, 'export'])
         ->name('performance.export')
         ->middleware('permission:performance.export');

    // Repair Orders (Asset Maintenance)
    Route::get('asset-maintenance', [RepairOrderController::class, 'index'])
         ->name('repair_orders.index')
         ->middleware('permission:repair-orders.view|trucks.view|miles-driven.view');
    Route::post('repair-orders', [RepairOrderController::class, 'store'])
         ->name('repair_orders.store')
         ->middleware('permission:repair-orders.create');
    Route::put('repair-orders/{repair_order}', [RepairOrderController::class, 'update'])
         ->name('repair_orders.update')
         ->middleware('permission:repair-orders.update');
    Route::delete('repair-orders/{repair_order}', [RepairOrderController::class, 'destroy'])
         ->name('repair_orders.destroy')
         ->middleware('permission:repair-orders.delete');
    Route::delete('repair-orders-bulk', [RepairOrderController::class, 'destroyBulk'])
         ->name('repair_orders.destroyBulk')
         ->middleware('permission:repair-orders.delete');
    Route::post('repair-orders/import', [RepairOrderController::class, 'import'])
         ->name('repair_orders.import')
         ->middleware('permission:repair-orders.import');
    Route::get('repair-orders/export', [RepairOrderController::class, 'export'])
         ->name('repair_orders.export')
         ->middleware('permission:repair-orders.export');

    // Safety Data
    Route::get('safety', [SafetyDataController::class, 'index'])
         ->name('safety.index')
         ->middleware('permission:safety-data.view');
    Route::post('safety', [SafetyDataController::class, 'store'])
         ->name('safety.store')
         ->middleware('permission:safety-data.create');
    Route::put('safety/{id}', [SafetyDataController::class, 'update'])
         ->name('safety.update')
         ->middleware('permission:safety-data.update');
    Route::delete('safety/{id}', [SafetyDataController::class, 'destroy'])
         ->name('safety.destroy')
         ->middleware('permission:safety-data.delete');
    Route::delete('safety-bulk', [SafetyDataController::class, 'destroyBulk'])
         ->name('safety.destroyBulk')
         ->middleware('permission:safety-data.delete');
    Route::post('safety/import', [SafetyDataController::class, 'import'])
         ->name('safety.import')
         ->middleware('permission:safety-data.import');
    Route::get('safety/export', [SafetyDataController::class, 'export'])
         ->name('safety.export')
         ->middleware('permission:safety-data.export');

    // Trucks
    Route::get('trucks', [TruckController::class, 'index'])->name('truck.index')
         ->middleware('permission:trucks.view');
    Route::post('trucks', [TruckController::class, 'store'])->name('truck.store')
         ->middleware('permission:trucks.create');
    Route::put('trucks/{truck}', [TruckController::class, 'update'])
         ->name('truck.update')
         ->middleware('permission:trucks.update');
    Route::delete('trucks/{truck}', [TruckController::class, 'destroy'])
         ->name('truck.destroy')
         ->middleware('permission:trucks.delete');
    Route::delete('trucks-bulk', [TruckController::class, 'destroyBulk'])
         ->name('truck.destroyBulk')
         ->middleware('permission:trucks.delete');
    Route::post('trucks/import', [TruckController::class, 'import'])
         ->name('truck.import')
         ->middleware('permission:trucks.import');
    Route::get('trucks/export', [TruckController::class, 'export'])
         ->name('truck.export')
         ->middleware('permission:trucks.export');

     // Delays (On‐Time)
     Route::get('ontime', [DelaysController::class, 'index'])
     ->name('ontime.index')
     ->middleware('permission:delays.view');
Route::post('ontime', [DelaysController::class, 'store'])
     ->name('ontime.store')
     ->middleware('permission:delays.create');
Route::put('ontime/{delay}', [DelaysController::class, 'update'])
     ->name('ontime.update')
     ->middleware('permission:delays.update');
Route::delete('ontime/{delay}', [DelaysController::class, 'destroy'])
     ->name('ontime.destroy')
     ->middleware('permission:delays.delete');
Route::delete('ontime-bulk', [DelaysController::class, 'destroyBulk'])
     ->name('ontime.destroyBulk')
     ->middleware('permission:delays.delete');
Route::post('ontime/import', [DelaysController::class, 'import'])
     ->name('ontime.import')
     ->middleware('permission:delays.import');
Route::get('ontime/export', [DelaysController::class, 'export'])
     ->name('ontime.export')
     ->middleware('permission:delays.export');

    // Drivers
    Route::get('drivers', [DriverController::class, 'index'])
         ->name('driver.index')
         ->middleware('permission:drivers.view');
    Route::post('drivers', [DriverController::class, 'store'])
         ->name('driver.store')
         ->middleware('permission:drivers.create');
    Route::post('drivers/{driver}', [DriverController::class, 'update'])
         ->name('driver.update')
         ->middleware('permission:drivers.update');
    Route::delete('drivers/{driver}', [DriverController::class, 'destroy'])
         ->name('driver.destroy')
         ->middleware('permission:drivers.delete');
    Route::delete('drivers-bulk', [DriverController::class, 'destroyBulk'])
         ->name('driver.destroyBulk')
         ->middleware('permission:drivers.delete');
    Route::post('drivers/import', [DriverController::class, 'import'])
         ->name('driver.import')
         ->middleware('permission:drivers.import');
    Route::get('drivers/export', [DriverController::class, 'export'])
         ->name('driver.export')
         ->middleware('permission:drivers.export');


     // Acceptance / Rejections
     Route::get('acceptance', [RejectionsController::class, 'index'])
     ->name('acceptance.index')
     ->middleware('permission:acceptance.view');
Route::post('acceptance', [RejectionsController::class, 'store'])
     ->name('acceptance.store')
     ->middleware('permission:acceptance.create');
Route::put('acceptance/{rejection}', [RejectionsController::class, 'update'])
     ->name('acceptance.update')
     ->middleware('permission:acceptance.update');
Route::delete('acceptance/{rejection}', [RejectionsController::class, 'destroy'])
     ->name('acceptance.destroy')
     ->middleware('permission:acceptance.delete');
Route::delete('acceptance-bulk', [RejectionsController::class, 'destroyBulk'])
     ->name('acceptance.destroyBulk')
     ->middleware('permission:acceptance.delete');
Route::post('acceptance/import', [RejectionsController::class, 'import'])
     ->name('acceptance.import')
     ->middleware('permission:acceptance.import');
Route::get('acceptance/export', [RejectionsController::class, 'export'])
     ->name('acceptance.export')
     ->middleware('permission:acceptance.export');

    // Miles Driven
    Route::get('miles-driven', [MilesDrivenController::class, 'index'])
         ->name('miles_driven.index')
         ->middleware('permission:miles-driven.view');
    Route::post('miles-driven', [MilesDrivenController::class, 'store'])
         ->name('miles_driven.store')
         ->middleware('permission:miles-driven.create');
    Route::put('miles-driven/{milesDriven}', [MilesDrivenController::class, 'update'])
         ->name('miles_driven.update')
         ->middleware('permission:miles-driven.update');
    Route::delete('miles-driven/{milesDriven}', [MilesDrivenController::class, 'destroy'])
         ->name('miles_driven.destroy')
         ->middleware('permission:miles-driven.delete');


    // Feedbacks
    Route::get('feedback', [FeedbackController::class,'index'])
         ->name('support.feedback.index');
    Route::post('feedback', [FeedbackController::class,'store'])
         ->name('support.feedback.store');
    Route::get('feedback/{feedback}', [FeedbackController::class,'show'])
         ->name('support.feedback.show');
    Route::delete('feedback/{feedback}', [FeedbackController::class,'destroy'])
         ->name('support.feedback.destroy');
    Route::delete('feedback-bulk', [FeedbackController::class,'destroyBulk'])
         ->name('support.feedback.destroyBulk');

     // Support Tickets
    Route::get('support', [TicketController::class, 'index'])
         ->name('support.index');
    Route::get('support/{ticket}', [TicketController::class, 'show'])
         ->name('support.show');
    Route::post('support', [TicketController::class, 'store'])
         ->name('support.store');
    Route::put('support/{ticket}/status', [TicketController::class, 'updateStatus'])
         ->name('support.update.status');
    Route::delete('support/{ticket}', [TicketController::class, 'destroy'])
         ->name('support.destroy');
    Route::delete('support-bulk', [TicketController::class, 'destroyBulk'])
         ->name('support.destroyBulk');
    Route::post('support/responses', [TicketResponseController::class, 'store'])
         ->name('support.responses.store');

     //SMS Coaching
     Route::get('/sms-thresholds', [SMSScoresThresholdsController::class, 'edit'])
         ->name('thresholds.edit')->middleware('permission:sms-coaching-thresholds.view');
     Route::post('/sms-thresholds', [SMSScoresThresholdsController::class, 'update'])
         ->name('thresholds.update')->middleware('permission:sms-coaching-thresholds.update');
         
     //SMS Coaching Templates
     Route::get('sms-coaching-templates', [SMSCoachingTemplatesController::class, 'index'])
     ->name('sms-coaching-templates.index')
     ->middleware('permission:sms-coaching-templates.view');

// Show “Create” form (static, before {id})
Route::get('sms-coaching-templates/create', [SMSCoachingTemplatesController::class, 'create'])
     ->name('sms-coaching-templates.create')
     ->middleware('permission:sms-coaching-templates.create');

// Store new template
Route::post('sms-coaching-templates', [SMSCoachingTemplatesController::class, 'store'])
     ->name('sms-coaching-templates.store')
     ->middleware('permission:sms-coaching-templates.create');

// Show a single template (only numeric IDs)
Route::get('sms-coaching-templates/{id}', [SMSCoachingTemplatesController::class, 'show'])
     ->whereNumber('id')
     ->name('sms-coaching-templates.show')
     ->middleware('permission:sms-coaching-templates.view');

// Edit form
Route::get('sms-coaching-templates/{id}/edit', [SMSCoachingTemplatesController::class, 'edit'])
     ->whereNumber('id')
     ->name('sms-coaching-templates.edit')
     ->middleware('permission:sms-coaching-templates.update');

// Update (PUT/PATCH)
Route::match(['put','patch'], 'sms-coaching-templates/{id}', [SMSCoachingTemplatesController::class, 'update'])
     ->whereNumber('id')
     ->name('sms-coaching-templates.update')
     ->middleware('permission:sms-coaching-templates.update');

// Delete
Route::delete('sms-coaching-templates/{id}', [SMSCoachingTemplatesController::class, 'destroy'])
     ->whereNumber('id')
     ->name('sms-coaching-templates.destroy')
     ->middleware('permission:sms-coaching-templates.delete');
     
     Route::get('/drivers/{driver}', [DriverController::class, 'show'])
         ->name('driver.show')->middleware('permission:drivers.view');
});
