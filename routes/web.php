<?php

use Illuminate\Support\Facades\Route;
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


Route::get('/refresh-csrf', function () {
    $token = csrf_token();

    return response()->json(['csrfToken' => $token])
        ->cookie(
            'XSRF-TOKEN',
            $token,
            120,                     // minutes
            '/',                    // path
            'dashboard.trucking360solutions.com', // exact subdomain only
            false,                   // secure (set to false for localhost)
            false                   // httpOnly: must be false so fetch can read it
        );
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/superadmin.php';
require __DIR__.'/tenant.php';
