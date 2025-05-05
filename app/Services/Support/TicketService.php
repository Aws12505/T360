<?php
namespace App\Services\Support;

use App\Models\Ticket;
use App\Models\Tenant;
use App\Models\TicketSubject;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class TicketService
{
    /**
     * Fetch paginated tickets for index.
     */
    public function getTicketsIndex(): array
    {
        $user = Auth::user();
        $query = Ticket::with(['user', 'responses']);

        // Normal user: only their own tickets
        if ($user->tenant_id !== null) {
            $query->where('user_id', $user->id);
        }
        // Super-admin sees all

        // Status filter
        if ($status = Request::input('status')) {
            $query->where('status', $status);
        }

        // Unseen filter
        if (Request::input('unseen') === 'true') {
            $query->where(
                $user->tenant_id === null
                    ? 'seen_by_admin'
                    : 'seen_by_user',
                false
            );
        }

        // Search by subject/message
        if ($q = Request::input('search')) {
            $query->where(fn($qf) =>
                $qf->where('subject', 'like', "%{$q}%")
                   ->orWhere('message', 'like', "%{$q}%")
            );
        }

        $tickets = $query
            ->orderBy(
                Request::input('sort_field', 'created_at'),
                Request::input('sort_direction', 'desc')
            )
            ->paginate(Request::input('per_page', 10))
            ->withQueryString();

        // Retrieve ALL ticket subjects including soft-deleted ones, for listing in the table and
        // the "Manage Ticket Subjects" section, similar to how delay codes are handled
        $ticketSubjects = TicketSubject::withTrashed()->get();

        return [
            'tickets' => $tickets,
            'filters' => [
                'search'         => Request::input('search'),
                'status'         => Request::input('status'),
                'unseen'         => Request::input('unseen'),
                'per_page'       => $tickets->perPage(),
                'sort_field'     => Request::input('sort_field'),
                'sort_direction' => Request::input('sort_direction'),
            ],
            'ticket_subjects' => $ticketSubjects,
        ];
    }

    /**
     * Fetch one ticket for show.
     */
    public function getTicket(int $id): array
    {
        $user = Auth::user();
        $query = Ticket::with([
            'user', 
            'responses.user' => function($query) {
                // Disable tenant scope for the user relationship
                $query->withoutGlobalScope(TenantScope::class);
            }
        ]);

        // Normal user => only own
        if ($user->tenant_id !== null) {
            $query->where('user_id', $user->id);
        }

        $ticket = $query->findOrFail($id);

        // Mark seen
        if ($user->tenant_id === null) {
            $ticket->update(['seen_by_admin' => true]);
        } else {
            $ticket->update(['seen_by_user'  => true]);
        }

        foreach ($ticket->responses as $resp) {
            if (!$resp->seen_at && $resp->user_id !== $user->id) {
                $resp->update(['seen_at' => now()]);
            }
        }

        return ['ticket' => $ticket];
    }

    /**
     * Create a new ticket.
     */
    public function createTicket(array $data): Ticket
    {
        $user = Auth::user();
        return Ticket::create([
            'tenant_id'     => $user->tenant_id,
            'user_id'       => $user->id,
            'subject'       => $data['subject'],
            'message'       => $data['message'],
            'status'        => 'open',
            'seen_by_admin' => false,
            'seen_by_user'  => true,
        ]);
    }

    /**
     * Update a ticket's status (super-admin only).
     */
    public function updateTicketStatus(int $id, string $status): Ticket
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => $status]);
        return $ticket;
    }

    /**
     * Delete a ticket.
     */
    public function deleteTicket(int $id): bool
    {
        return Ticket::findOrFail($id)->delete();
    }
    /**
     * Delete multiple tickets.
     *
     * @param array $ids
     * @return void
     */
    public function deleteMultipleTickets(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete tickets they have access to
        $query = Ticket::whereIn('id', $ids);
        
        // If not a super admin, restrict to user's own tickets
        $user = Auth::user();
        if (!is_null($user->tenant_id)) {
            $query->where('user_id', $user->id);
        }
        
        $query->delete();
    }
}
