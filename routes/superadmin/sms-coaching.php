<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\SMSCoaching\SMSCoachingAdminController;

Route::controller(SMSCoachingAdminController::class)
    ->prefix('sms-coaching')
    ->name('admin.sms-coaching.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/messages/global', 'storeGlobalMessages')->name('messages.global');
        Route::post('/thresholds/global', 'storeGlobalThresholds')->name('thresholds.global');
        Route::post('/messages/overrides', 'storeMessageOverrides')->name('messages.overrides');
        Route::delete('/messages/overrides/{message}', 'deleteMessageOverride')->name('messages.overrides.delete');
        Route::post('/thresholds/overrides', 'storeThresholdOverrides')->name('thresholds.overrides');
        Route::delete('/thresholds/overrides/{threshold}', 'deleteThresholdOverride')->name('thresholds.overrides.delete');
    });
