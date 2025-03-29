<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\Zoho\ZohoWebhookController;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::post('/zoho/webhook', [ZohoWebhookController::class, 'handleZohoWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/superAdmin.php';
require __DIR__.'/tenant.php';
