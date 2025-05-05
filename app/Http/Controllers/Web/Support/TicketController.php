<?php
namespace App\Http\Controllers\Web\Support;

use App\Http\Controllers\Controller;
use App\Services\Support\TicketService;
use App\Services\Support\TicketSubjectsService;
use App\Http\Requests\Support\StoreTicketRequest;
use App\Http\Requests\Support\UpdateTicketStatusRequest;
use App\Http\Requests\Support\StoreTicketSubjectRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $ticketSubjectsService;

    public function __construct(
        protected TicketService $service,
        TicketSubjectsService $ticketSubjectsService
    ) {
        $this->ticketSubjectsService = $ticketSubjectsService;
    }

    /**
     * Both admin & normal use this:
     * - super-admin calls /support
     * - normal calls /{tenantSlug}/support
     */
    public function index(string $tenantSlug = null)
    {
        $data = $this->service->getTicketsIndex();
        return Inertia::render('Support/TicketIndex', [
            ...$data,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => Auth::user()->tenant_id === null,
        ]);
    }

    /**
     * Show for normal user: /{tenantSlug}/support/{ticket}
     */
    public function show(string $tenantSlug, int $ticket)
    {
        $data = $this->service->getTicket($ticket);
        return Inertia::render('Support/TicketShow', [
            ...$data,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => false,
        ]);
    }

    /**
     * Show for super-admin: /support/{ticket}
     */
    public function showAdmin(int $ticket)
    {
        $data = $this->service->getTicket($ticket);
        return Inertia::render('Support/TicketShow', [
            ...$data,
            'tenantSlug' => null,
            'SuperAdmin' => true,
        ]);
    }

    /**
     * Create ticket (admin or normal).
     */
    public function store(StoreTicketRequest $request, string $tenantSlug = null)
    {
        $this->service->createTicket($request->validated());
        return back()->with('success', 'Ticket created successfully.');
    }

    /**
     * Update status (normal users cannot hit this route).
     */
    public function updateStatus(UpdateTicketStatusRequest $request, string $tenantSlug, int $ticket)
    {
        $this->service->updateTicketStatus($ticket, $request->status);
        return back()->with('success', 'Ticket status updated.');
    }

    /**
     * Super-admin status update.
     */
    public function updateStatusAdmin(UpdateTicketStatusRequest $request, int $ticket)
    {
        $this->service->updateTicketStatus($ticket, $request->status);
        return back()->with('success', 'Ticket status updated.');
    }

    /**
     * Delete (normal).
     */
    public function destroy(string $tenantSlug, int $ticket)
    {
        $this->service->deleteTicket($ticket);
        return redirect()->route('support.index', $tenantSlug)
                         ->with('success', 'Ticket deleted.');
    }

    /**
     * Delete (admin).
     */
    public function destroyAdmin(int $ticket)
    {
        $this->service->deleteTicket($ticket);
        return redirect()->route('support.index.admin')
                         ->with('success', 'Ticket deleted.');
    }

    /**
     * Store a new ticket subject.
     */
    public function storeSubject(StoreTicketSubjectRequest $request)
    {
        $this->ticketSubjectsService->createTicketSubject($request->validated());
        return back();
    }

    /**
     * Delete a ticket subject.
     */
    public function destroySubject($id)
    {
        $this->ticketSubjectsService->deleteTicketSubject($id);
        return back();
    }

    /**
     * Restore a soft-deleted ticket subject.
     */
    public function restoreSubject($id)
    {
        $this->ticketSubjectsService->restoreTicketSubject($id);
        return back();
    }

    /**
     * Permanently force delete a ticket subject.
     */
    public function forceDeleteSubject($id)
    {
        $this->ticketSubjectsService->forceDeleteTicketSubject($id);
        return back();
    }

     /**
     * Delete multiple tickets (normal user).
     */
    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->service->deleteMultipleTickets($ids);
        return back();
    }

    /**
     * Delete multiple tickets (super-admin).
     */
    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->service->deleteMultipleTickets($ids);
        return back();
    }
}
