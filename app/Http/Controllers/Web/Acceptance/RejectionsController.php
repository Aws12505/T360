<?php

namespace App\Http\Controllers\Web\Acceptance;

use App\Http\Controllers\Controller;
use App\Services\Acceptance\RejectionReasonCodesService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRejectionRequest;
use App\Http\Requests\UpdateRejectionRequest;
use App\Services\RejectionService;
use Inertia\Inertia;
use App\Http\Requests\StoreRejectionReasonCode;

/**
 * Class RejectionsController
 *
 * This controller manages rejection entries and rejection reason codes.
 * It delegates logic to the RejectionService.
 *
 * Command:
 *   php artisan make:controller Web/RejectionsController
 */
class RejectionsController extends Controller
{
    protected RejectionService $rejectionService;
    protected RejectionReasonCodesService $rejectionReasonCodesService;

    /**
     * Constructor.
     *
     * @param RejectionService $rejectionService Service for rejection processing.
     * @param RejectionReasonCodesService $rejectionReasonCodesService
     */
    public function __construct(RejectionService $rejectionService, RejectionReasonCodesService $rejectionReasonCodesService)
    {
        $this->rejectionService = $rejectionService;
        $this->rejectionReasonCodesService = $rejectionReasonCodesService;
    }

    /**
     * Display a list of rejections.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $data = $this->rejectionService->getRejectionsIndex();
        return Inertia::render('Rejections/Index', $data);
    }

    /**
     * Store a new rejection.
     *
     * @param StoreRejectionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRejectionRequest $request)
    {
        
        $this->rejectionService->createRejection($request->validated());
        return back();
    }

    /**
     * Update an existing rejection.
     *
     * @param UpdateRejectionRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRejectionRequest $request, $tenantSlug, $id)
    {
        
        $this->rejectionService->updateRejection($id, $request->validated());
        return back();
    }

    /**
     * Update a rejection as Admin.
     *
     * @param UpdateRejectionRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdmin(UpdateRejectionRequest $request, $id)
    {
        
        $this->rejectionService->updateRejection($id, $request->validated());
        return back();
    }

    /**
     * Delete a rejection.
     *
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($tenantSlug, $id)
    {
        $this->rejectionService->deleteRejection($id);
        return back();
    }

    /**
     * Delete a rejection as Admin.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAdmin($id)
    {
        $this->rejectionService->deleteRejection($id);
        return back();
    }

    /**
 * Create a new rejection reason code.
 *
 * @param \App\Http\Requests\StoreRejectionReasonCode $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function storeCode(StoreRejectionReasonCode $request)
{
    // The incoming request will be automatically validated by the StoreRejectionReasonCode request class
    // No need to manually validate, just access the validated data
    $this->rejectionReasonCodesService->createReasonCode($request->validated());

    // Redirect back after successfully creating the reason code
    return back();
}

    /**
     * Delete a rejection reason code.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCode($id)
    {
        $this->rejectionReasonCodesService->deleteReasonCode($id);
        return back();
    }
}
