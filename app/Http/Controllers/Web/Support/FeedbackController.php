<?php

namespace App\Http\Controllers\Web\Support;

use App\Http\Controllers\Controller;
use App\Services\Support\FeedbackService;
use App\Http\Requests\Support\StoreFeedbackRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct(protected FeedbackService $service) {}

    public function index(string $tenantSlug = null)
    {
        $data = $this->service->getIndex();
        return Inertia::render('Support/FeedbackIndex', [
            ...$data,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => Auth::user()->tenant_id === null,
        ]);
    }

    public function show(string $tenantSlug = null, int $feedback)
    {
        $data = $this->service->getOne($feedback);
        return Inertia::render('Support/FeedbackShow', [
            ...$data,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => Auth::user()->tenant_id === null,
        ]);
    }
/**
     * Show for super-admin: /support/{ticket}
     */
    public function showAdmin(int $feedback)
    {
        $data = $this->service->getOne($feedback);
        return Inertia::render('Support/FeedbackShow', [
            ...$data,
            'tenantSlug' => null,
            'SuperAdmin' => true,
        ]);
    }
    public function store(StoreFeedbackRequest $request, string $tenantSlug = null)
    {
        $this->service->create($request->validated());
        return back()->with('success', 'Feedback submitted successfully.');
    }

    public function destroy(string $tenantSlug = null, int $feedback)
    {
        $this->service->delete($feedback);
        return back()->with('success', 'Feedback deleted successfully.');
    }
/**
     * Delete (admin).
     */
    public function destroyAdmin(int $feedback)
    {
        $this->service->delete($feedback);
        return back()->with('success', 'Ticket deleted.');
    }
    public function destroyBulk(Request $request, string $tenantSlug = null)
    {
        $this->service->deleteMultiple($request->input('ids', []));
        return back()->with('success', 'Feedback deleted successfully.');
    }
    /**
     * Delete multiple tickets (super-admin).
     */
    public function destroyBulkAdmin(Request $request)
    {
        $this->service->deleteMultiple($request->input('ids', []));
        return back()->with('success', 'Feedback deleted successfully.');
    }
}
