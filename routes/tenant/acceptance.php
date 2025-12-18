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

        // NEW: Performance-style import flow
        Route::post('/validate-import', 'validateImport')
            ->name('validateImport')
            ->middleware('permission:acceptance.import');

        Route::post('/confirm-import', 'confirmImport')
            ->name('confirmImport')
            ->middleware('permission:acceptance.import');

        Route::get('/download-error-report', 'downloadErrorReport')
            ->name('downloadErrorReport')
            ->middleware('permission:acceptance.import');

        Route::get('/export', 'export')
            ->name('export')
            ->middleware('permission:acceptance.export');
    });
