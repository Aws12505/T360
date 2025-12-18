<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserManagement\UserController;
use App\Http\Controllers\Web\UserManagement\ImpersonationController;

Route::get('/users-roles', [UserController::class, 'index'])
     ->name('admin.users.roles.index');

// Users
Route::controller(UserController::class)->group(function () {
    Route::post('/users', 'storeUser')->name('admin.users.store');
    Route::put('/users/{user}', 'updateUserAdmin')->name('admin.users.update');
    Route::delete('/users/{user}', 'destroyUserAdmin')->name('admin.users.destroy');
    
    // Roles
    Route::post('/roles', 'storeRole')->name('admin.roles.store');
    Route::put('/roles/{role}', 'updateRoleAdmin')->name('admin.roles.update');
    Route::delete('/roles/{role}', 'destroyRoleAdmin')->name('admin.roles.destroy');
    
    // Tenants
    Route::post('/tenants', 'storeTenant')->name('admin.tenants.store');
    Route::put('/tenants/{tenant}', 'updateTenant')->name('admin.tenants.update');
    Route::delete('/tenants/{tenant}', 'destroyTenant')->name('admin.tenants.destroy');
});

// Impersonation
Route::get('/impersonate/{id}', [ImpersonationController::class, 'impersonate'])
     ->name('impersonate.start');
