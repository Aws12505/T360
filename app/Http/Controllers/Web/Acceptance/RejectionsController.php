<?php

namespace App\Http\Controllers\Web\Acceptance;

use App\Http\Controllers\Controller;
use App\Services\Acceptance\RejectionReasonCodesService;
use App\Services\Acceptance\RejectionImportExportService;
use Illuminate\Http\Request;
use App\Http\Requests\Acceptance\StoreRejectionRequest;
use App\Http\Requests\Acceptance\UpdateRejectionRequest;
use App\Services\Acceptance\RejectionService;
use Inertia\Inertia;
use App\Http\Requests\Acceptance\StoreRejectionReasonCode;

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
    protected RejectionImportExportService $rejectionImportExportService;

    /**
     * Constructor.
     *
     * @param RejectionService $rejectionService Service for rejection processing.
     * @param RejectionReasonCodesService $rejectionReasonCodesService
     * @param RejectionImportExportService $rejectionImportExportService
     */
    public function __construct(
        RejectionService $rejectionService, 
        RejectionReasonCodesService $rejectionReasonCodesService,
        RejectionImportExportService $rejectionImportExportService
    )
    {
        $this->rejectionService = $rejectionService;
        $this->rejectionReasonCodesService = $rejectionReasonCodesService;
        $this->rejectionImportExportService = $rejectionImportExportService;
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
 * @param \App\Http\Requests\Acceptance\StoreRejectionReasonCode $request
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

    /**
     * Restore a soft-deleted rejection reason code.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreCode($id)
    {
        $this->rejectionReasonCodesService->restoreReasonCode($id);
        return back();
    }

    /**
     * Permanently force delete a rejection reason code.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDeleteCode($id)
    {
        $this->rejectionReasonCodesService->forceDeleteReasonCode($id);
        return back();
    }

    /**
     * Delete multiple rejection records.
     *
     * @param Request $request
     * @param string|null $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->rejectionService->deleteMultipleRejections($ids);
        return redirect()->back()->with('success', 'Rejections deleted successfully.');
    }

    /**
     * Delete multiple rejection records as Admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->rejectionService->deleteMultipleRejections($ids);
        return redirect()->back()->with('success', 'Rejections deleted successfully.');
    }

    /**
     * Import rejections from CSV file.
     */
    public function import(Request $request, $tenantSlug = null)
    {
        return $this->rejectionImportExportService->importRejections($request);
    }

    /**
     * Import rejections from CSV file for admin.
     */
    public function importAdmin(Request $request)
    {
        return $this->rejectionImportExportService->importRejections($request);
    }

    /**
     * Export rejections to CSV file.
     */
    public function export($tenantSlug = null)
    {
        return $this->rejectionImportExportService->exportRejections();
    }

    /**
     * Export rejections to CSV file for admin.
     */
    public function exportAdmin()
    {
        return $this->rejectionImportExportService->exportRejections();
    }
}
