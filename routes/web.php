<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Zoho\ZohoWebhookController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/driver.php';

// Tenant routes
Route::middleware(['auth', 'tenant'])
     ->prefix('{tenantSlug}')
     ->group(function () {
    require __DIR__.'/tenant/dashboard.php';
    require __DIR__.'/tenant/settings.php';
    require __DIR__.'/tenant/users-roles.php';
    require __DIR__.'/tenant/performance.php';
    require __DIR__.'/tenant/repair-orders.php';
    require __DIR__.'/tenant/safety.php';
    require __DIR__.'/tenant/trucks.php';
    require __DIR__.'/tenant/ontime.php';
    require __DIR__.'/tenant/drivers.php';
    require __DIR__.'/tenant/acceptance.php';
    require __DIR__.'/tenant/miles-driven.php';
    require __DIR__.'/tenant/support.php';
    require __DIR__.'/tenant/sms-coaching.php';
});

// Super admin routes
Route::middleware(['auth', 'superAdmin'])->group(function () {
    require __DIR__.'/superadmin/dashboard.php';
    require __DIR__.'/superadmin/users-roles.php';
    require __DIR__.'/superadmin/performance.php';
    require __DIR__.'/superadmin/safety.php';
    require __DIR__.'/superadmin/trucks.php';
    require __DIR__.'/superadmin/acceptance.php';
    require __DIR__.'/superadmin/ontime.php';
    require __DIR__.'/superadmin/drivers.php';
    require __DIR__.'/superadmin/repair-orders.php';
    require __DIR__.'/superadmin/miles-driven.php';
    require __DIR__.'/superadmin/support.php';
});
