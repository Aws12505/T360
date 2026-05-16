<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\SMSCoaching\SMSCoachingSettingsController;

Route::controller(SMSCoachingSettingsController::class)
    ->prefix('sms-coaching')
    ->name('sms-coaching-settings.')
    ->group(function () {
        Route::get('/', 'edit')
            ->name('edit')
            ->middleware('permission:sms-coaching-thresholds.view');
        Route::post('/', 'update')
            ->name('update')
            ->middleware('permission:sms-coaching-thresholds.update');
    });
