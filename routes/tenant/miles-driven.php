<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Miles\MilesDrivenController;

Route::controller(MilesDrivenController::class)
     ->prefix('miles-driven')
     ->name('miles_driven.')
     ->group(function () {
    Route::get('/', 'index')
         ->name('index')
         ->middleware('permission:miles-driven.view');
    Route::post('/', 'store')
         ->name('store')
         ->middleware('permission:miles-driven.create');
    Route::put('{milesDriven}', 'update')
         ->name('update')
         ->middleware('permission:miles-driven.update');
    Route::delete('{milesDriven}', 'destroy')
         ->name('destroy')
         ->middleware('permission:miles-driven.delete');
});
