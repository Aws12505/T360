<?php

namespace App\Http\Controllers\Web\On_Time;

use App\Http\Controllers\Controller;
use App\Services\On_Time\DelayCodesService;
use App\Services\On_Time\DelayService;
use App\Services\On_Time\DelayImportExportService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Requests\On_Time\StoreDelayRequest;
use App\Http\Requests\On_Time\UpdateDelayRequest;
use App\Http\Requests\On_Time\StoreDelayCode;
use Illuminate\Http\Request;

class DelaysController extends Controller
{
    protected DelayService $delayService;
    protected DelayCodesService $delayCodesService;
    protected DelayImportExportService $delayImportExportService;

    public function __construct(
        DelayService $delayService, 
        DelayCodesService $delayCodesService,
        DelayImportExportService $delayImportExportService
    )
    {
        $this->delayService = $delayService;
        $this->delayCodesService = $delayCodesService;
        $this->delayImportExportService = $delayImportExportService;
    }

    // Delay records actions

    public function index()
    {
        $data = $this->delayService->getDelaysIndex();
        return Inertia::render('Delays/Index', $data);
    }

    public function store(StoreDelayRequest $request)
    {
        $this->delayService->createDelay($request->validated());
        return back();
    }

    public function update(UpdateDelayRequest $request, $tenantSlug, $id)
    {
        $this->delayService->updateDelay($id, $request->validated());
        return back();
    }

    public function updateAdmin(UpdateDelayRequest $request, $id)
    {
        $this->delayService->updateDelay($id, $request->validated());
        return back();
    }

    public function destroy($tenantSlug, $id)
    {
        $this->delayService->deleteDelay($id);
        return back();
    }

    public function destroyAdmin($id)
    {
        $this->delayService->deleteDelay($id);
        return back();
    }

    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->delayService->deleteMultipleDelays($ids);
        return back();
    }

    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->delayService->deleteMultipleDelays($ids);
        return back();
    }

    // Delay code actions

    public function storeCode(StoreDelayCode $request)
    {
        $this->delayCodesService->createDelayCode($request->validated());
        return back();
    }

    public function destroyCode($id)
    {
        $this->delayCodesService->deleteDelayCode($id);
        return back();
    }

    // New endpoints for SuperAdmin

    /**
     * Restore a soft-deleted delay code.
     */
    public function restoreCode($id)
    {
        $this->delayCodesService->restoreDelayCode($id);
        return back();
    }

    /**
     * Permanently force delete a delay code.
     */
    public function forceDeleteCode($id)
    {
        $this->delayCodesService->forceDeleteDelayCode($id);
        return back();
    }

    /**
     * Import delays from CSV file.
     */
    public function import(Request $request, $tenantSlug = null)
    {
        return $this->delayImportExportService->importDelays($request);
    }

    /**
     * Import delays from CSV file for admin.
     */
    public function importAdmin(Request $request)
    {
        return $this->delayImportExportService->importDelays($request);
    }

    /**
     * Export delays to CSV file.
     */
    public function export($tenantSlug = null)
    {
        try {
            return $this->delayImportExportService->exportDelays();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export delays to CSV file for admin.
     */
    public function exportAdmin()
    {
        try {
            return $this->delayImportExportService->exportDelays();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
