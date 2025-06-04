<?php

use App\Http\Controllers\Web\Driver\DriverController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:driver')->group(function () {
    Route::get('/driver-dashboard', [DriverController::class, 'indexProfile'])
        ->name('driver.dashboard');
});

