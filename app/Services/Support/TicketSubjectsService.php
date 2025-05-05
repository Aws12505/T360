<?php

namespace App\Services\Support;

use App\Models\TicketSubject;

class TicketSubjectsService
{
    /**
     * Create a new ticket subject.
     *
     * @param array $data
     * @return void
     */
    public function createTicketSubject(array $data)
    {
        TicketSubject::create($data);
    }

    /**
     * Soft delete a ticket subject.
     *
     * @param int $id
     * @return void
     */
    public function deleteTicketSubject($id)
    {
        $code = TicketSubject::findOrFail($id);
        $code->delete();
    }

    /**
     * Restore a soft-deleted ticket subject.
     *
     * @param int $id
     * @return void
     */
    public function restoreTicketSubject($id)
    {
        $code = TicketSubject::withTrashed()->findOrFail($id);
        $code->restore();
    }

    /**
     * Permanently force delete a ticket subject.
     *
     * @param int $id
     * @return void
     */
    public function forceDeleteTicketSubject($id)
    {
        $code = TicketSubject::withTrashed()->findOrFail($id);
        $code->forceDelete();
    }
}