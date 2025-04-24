<?php
namespace App\Http\Controllers\Web\Support;

use App\Http\Controllers\Controller;
use App\Services\Support\TicketService;
use App\Http\Requests\Support\StoreTicketRequest;
use App\Http\Requests\Support\UpdateTicketStatusRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function __construct(protected TicketService $service) {}

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
}
