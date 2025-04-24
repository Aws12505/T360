<?php
namespace App\Http\Controllers\Web\Support;

use App\Http\Controllers\Controller;
use App\Services\Support\TicketResponseService;
use App\Http\Requests\Support\StoreTicketResponseRequest;
use Illuminate\Http\RedirectResponse;

class TicketResponseController extends Controller
{
    public function __construct(protected TicketResponseService $service) {}

    /**
     * Both admin & normal use this:
     * - superAdmin: POST /support/responses
     * - normal:     POST /{tenantSlug}/support/responses
     */
    public function store(StoreTicketResponseRequest $request, string $tenantSlug = null): RedirectResponse
    {
        $this->service->createResponse($request->validated());
        return back()->with('success', 'Response added successfully.');
    }
}
