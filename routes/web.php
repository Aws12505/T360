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

    return response()->json([
        'csrfToken' => $token
    ])->cookie(
        'XSRF-TOKEN',      // Cookie name Laravel looks for
        $token,            // The actual token
        120,               // Expire time in minutes
        '/',               // Path
        'dashboard.trucking360solutions.com',  // Domain — subdomain only
        false,             // Secure: set to true if you're using HTTPS
        false              // httpOnly: false so it's visible in JS + browser
    );
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/superadmin.php';
require __DIR__.'/tenant.php';
