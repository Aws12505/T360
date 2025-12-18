<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\SMSCoaching\SMSScoresThresholdsController;
use App\Http\Controllers\Web\SMSCoaching\SMSCoachingTemplatesController;

// SMS Thresholds
Route::controller(SMSScoresThresholdsController::class)
     ->prefix('sms-thresholds')
     ->name('thresholds.')
     ->group(function () {
    Route::get('/', 'edit')
         ->name('edit')
         ->middleware('permission:sms-coaching-thresholds.view');
    Route::post('/', 'update')
         ->name('update')
         ->middleware('permission:sms-coaching-thresholds.update');
});

// SMS Coaching Templates
Route::controller(SMSCoachingTemplatesController::class)
     ->prefix('sms-coaching-templates')
     ->name('sms-coaching-templates.')
     ->group(function () {
    
    Route::get('/', 'index')
         ->name('index')
         ->middleware('permission:sms-coaching-templates.view');
    
    Route::get('create', 'create')
         ->name('create')
         ->middleware('permission:sms-coaching-templates.create');
    
    Route::post('/', 'store')
         ->name('store')
         ->middleware('permission:sms-coaching-templates.create');
    
    Route::get('{id}', 'show')
         ->whereNumber('id')
         ->name('show')
         ->middleware('permission:sms-coaching-templates.view');
    
    Route::get('{id}/edit', 'edit')
         ->whereNumber('id')
         ->name('edit')
         ->middleware('permission:sms-coaching-templates.update');
    
    Route::match(['put','patch'], '{id}', 'update')
         ->whereNumber('id')
         ->name('update')
         ->middleware('permission:sms-coaching-templates.update');
    
    Route::delete('{id}', 'destroy')
         ->whereNumber('id')
         ->name('destroy')
         ->middleware('permission:sms-coaching-templates.delete');
});
