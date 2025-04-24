<?php
namespace App\Services\Support;

use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Support\Facades\Auth;

class TicketResponseService
{
    /**
     * Create a response and update seen/status flags.
     */
    public function createResponse(array $data): TicketResponse
    {
        $user = Auth::user();
        $isSuperAdmin = $user->tenant_id === null;

        $resp = TicketResponse::create([
            'ticket_id' => $data['ticket_id'],
            'user_id'   => $user->id,
            'message'   => $data['message'],
            'is_admin'  => $isSuperAdmin,
        ]);

        $ticket = Ticket::findOrFail($data['ticket_id']);

        if ($isSuperAdmin) {
            $ticket->update([
                'status'       => 'in_progress',
                'seen_by_user' => false,
            ]);
        } else {
            $ticket->update(['seen_by_admin' => false]);
        }

        return $resp;
    }
}
