<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

Route::get('/dashboard', function () {
    $permissions = Auth::user()->getAllPermissions();
    return Inertia::render('Dashboard',[
        'permissions' => $permissions,
    ]);
})->name('admin.dashboard');
