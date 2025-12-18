<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Performance\SummariesController;

Route::get('dashboard', [SummariesController::class, 'getSummaries'])
     ->name('dashboard');
