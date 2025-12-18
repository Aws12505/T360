<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserManagement\UserController;
use App\Http\Controllers\Web\UserManagement\ImpersonationController;

// Users & Roles view
Route::get('users-roles', [UserController::class, 'index'])
     ->name('users.roles.index')
     ->middleware('permission:users.view|roles.view');

// Users
Route::post('users', [UserController::class, 'storeUser'])
     ->name('users.store')
     ->middleware('permission:users.create');
Route::put('users/{user}', [UserController::class, 'updateUser'])
     ->name('users.update')
     ->middleware('permission:users.update');
Route::delete('users/{user}', [UserController::class, 'destroyUser'])
     ->name('users.destroy')
     ->middleware('permission:users.delete');

// Roles
Route::post('roles', [UserController::class, 'storeRole'])
     ->name('roles.store')
     ->middleware('permission:roles.create');
Route::put('roles/{role}', [UserController::class, 'updateRole'])
     ->name('roles.update')
     ->middleware('permission:roles.update');
Route::delete('roles/{role}', [UserController::class, 'destroyRole'])
     ->name('roles.destroy')
     ->middleware('permission:roles.delete');

// Impersonation
Route::get('stopimpersonate', [ImpersonationController::class, 'stopImpersonation'])
     ->name('impersonate.stop');
