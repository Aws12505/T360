<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\Zoho\ZohoWebhookController;
use Illuminate\Support\Facades\Auth;

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
        'XSRF-TOKEN',
        $token,
        120, // minutes
        '/',
        request()->getHost(), // or '.yourdomain.com' for subdomain coverage
        false, // secure: true if using HTTPS
        false  // httpOnly: false so JavaScript can access it
    );
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/superadmin.php';
require __DIR__.'/tenant.php';
