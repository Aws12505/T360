<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwilioController;

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

use Inertia\Inertia;
use App\Http\Controllers\Web\Zoho\ZohoWebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

Route::get('/', function () {
    // Safely get the authenticated user's tenant slug if available
    $tenantSlug = null;
    
    if (Auth::check() && Auth::user()->tenant) {
        $tenantSlug = Auth::user()->tenant->slug;
    }
    
    return Inertia::render('Welcome', [
        'tenantSlug' => $tenantSlug,
    ]);
})->name('home');


Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/superadmin.php';
require __DIR__.'/tenant.php';

// Twilio Routes
Route::prefix('api/twilio')->group(function () {
    Route::post('/send-sms', [TwilioController::class, 'sendSms'])->name('twilio.send-sms');
});

// Twilio Vue Page Route
Route::get('/twilio', function () {
    return Inertia::render('Twilio/Index');
})->name('twilio');
