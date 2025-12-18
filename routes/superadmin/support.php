<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Support\TicketController;
use App\Http\Controllers\Web\Support\TicketResponseController;
use App\Http\Controllers\Web\Support\FeedbackController;
use App\Http\Controllers\Web\Support\FeedbackSubjectsController;

// Feedback
Route::controller(FeedbackController::class)
     ->prefix('feedback')
     ->group(function () {
    Route::get('/', 'index')->name('support.feedback.index.admin');
    Route::post('/', 'store')->name('support.feedback.store.admin');
    Route::delete('-bulk', 'destroyBulkAdmin')->name('support.feedback.destroyBulk.admin');
    Route::get('{feedback}', 'showAdmin')->name('support.feedback.show.admin');
    Route::delete('{feedback}', 'destroyAdmin')->name('support.feedback.destroy.admin');
});

// Feedback categories
Route::controller(FeedbackSubjectsController::class)
     ->prefix('feedback-categories')
     ->group(function () {
    Route::post('/', 'store')->name('feedback.categories.store.admin');
    Route::delete('{id}', 'destroy')->name('feedback.categories.destroy.admin');
    Route::post('{id}/restore', 'restore')->name('feedback.categories.restore.admin');
    Route::delete('{id}/force-delete', 'forceDelete')->name('feedback.categories.forceDelete.admin');
});

// Support tickets
Route::controller(TicketController::class)
     ->prefix('support')
     ->group(function () {
    Route::get('/', 'index')->name('support.index.admin');
    Route::get('{ticket}', 'showAdmin')->name('support.show.admin');
    Route::post('/', 'store')->name('support.store.admin');
    Route::put('{ticket}/status', 'updateStatusAdmin')->name('support.update.status.admin');
    Route::delete('{ticket}', 'destroyAdmin')->name('support.destroy.admin');
    Route::delete('-bulk', 'destroyBulkAdmin')->name('support.destroyBulk.admin');
});

// Ticket responses
Route::post('/support/responses', [TicketResponseController::class, 'store'])
     ->name('support.responses.store.admin');

// Ticket subjects
Route::controller(TicketController::class)
     ->prefix('ticket-subjects')
     ->group(function () {
    Route::post('/', 'storeSubject')->name('ticket_subjects.store.admin');
    Route::delete('{id}', 'destroySubject')->name('ticket_subjects.destroy.admin');
    Route::post('{id}/restore', 'restoreSubject')->name('ticket_subjects.restore.admin');
    Route::delete('{id}/force', 'forceDeleteSubject')->name('ticket_subjects.forceDelete.admin');
});
