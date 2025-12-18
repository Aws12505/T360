<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\On_Time\DelaysController;

Route::controller(DelaysController::class)
    ->prefix('ontime')
    ->name('ontime.')
    ->group(function () {

        Route::get('/', 'index')->name('index')->middleware('permission:delays.view');
        Route::post('/', 'store')->name('store')->middleware('permission:delays.create');
        Route::put('{delay}', 'update')->name('update')->middleware('permission:delays.update');
        Route::delete('{delay}', 'destroy')->name('destroy')->middleware('permission:delays.delete');
        Route::delete('-bulk', 'destroyBulk')->name('destroyBulk')->middleware('permission:delays.delete');

        // ✅ NEW: validation + confirm import + error report (Performance-style)
        Route::post('/validate-import', 'validateImport')
            ->name('validateImport')
            ->middleware('permission:delays.import');

        Route::post('/confirm-import', 'confirmImport')
            ->name('confirmImport')
            ->middleware('permission:delays.import');

        Route::get('/download-error-report', 'downloadErrorReport')
            ->name('downloadErrorReport')
            ->middleware('permission:delays.import');

        Route::get('/export', 'export')->name('export')->middleware('permission:delays.export');
    });
