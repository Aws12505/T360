<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Acceptance\RejectionsController;

Route::controller(RejectionsController::class)
     ->prefix('acceptance')
     ->name('acceptance.')
     ->group(function () {
    Route::get('/', 'index')
         ->name('index')
         ->middleware('permission:acceptance.view');
    Route::post('/', 'store')
         ->name('store')
         ->middleware('permission:acceptance.create');
    Route::put('{rejection}', 'update')
         ->name('update')
         ->middleware('permission:acceptance.update');
    Route::delete('{rejection}', 'destroy')
         ->name('destroy')
         ->middleware('permission:acceptance.delete');
    Route::delete('-bulk', 'destroyBulk')
         ->name('destroyBulk')
         ->middleware('permission:acceptance.delete');
    Route::post('/import', 'import')
         ->name('import')
         ->middleware('permission:acceptance.import');
    Route::get('/export', 'export')
         ->name('export')
         ->middleware('permission:acceptance.export');
});
