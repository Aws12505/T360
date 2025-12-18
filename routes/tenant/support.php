<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Support\TicketController;
use App\Http\Controllers\Web\Support\FeedbackController;
use App\Http\Controllers\Web\Support\TicketResponseController;

// Feedback
Route::controller(FeedbackController::class)
     ->prefix('feedback')
     ->name('support.feedback.')
     ->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('{feedback}', 'show')->name('show');
    Route::delete('{feedback}', 'destroy')->name('destroy');
    Route::delete('-bulk', 'destroyBulk')->name('destroyBulk');
});

// Support Tickets
Route::controller(TicketController::class)
     ->prefix('support')
     ->name('support.')
     ->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('{ticket}', 'show')->name('show');
    Route::post('/', 'store')->name('store');
    Route::put('{ticket}/status', 'updateStatus')->name('update.status');
    Route::delete('{ticket}', 'destroy')->name('destroy');
    Route::delete('-bulk', 'destroyBulk')->name('destroyBulk');
});

// Ticket Responses
Route::post('support/responses', [TicketResponseController::class, 'store'])
     ->name('support.responses.store');
