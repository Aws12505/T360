<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\TenantSettingsController;

Route::get('settings/tenant', [TenantSettingsController::class, 'edit'])
     ->name('tenant.settings.edit')
     ->middleware('permission:tenant-settings.view');
Route::post('settings/tenant', [TenantSettingsController::class, 'update'])
     ->name('tenant.settings.update')
     ->middleware('permission:tenant-settings.update');
