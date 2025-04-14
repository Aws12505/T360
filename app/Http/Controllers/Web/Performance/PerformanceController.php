<?php

namespace App\Http\Controllers\Web\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Performance\StorePerformanceRequest;
use App\Http\Requests\Performance\UpdatePerformanceRequest;
use App\Services\Performance\PerformanceImportExportService;
use App\Services\Performance\PerformanceService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

/**
 * Class PerformanceController
 *
 * This controller handles Performance records including CRUD, import/export,
 * and rating calculations. It delegates business logic to PerformanceService.
 *
 * Command:
 *   php artisan make:controller Web/PerformanceController
 */
class PerformanceController extends Controller
{
    protected PerformanceService $PerformanceService;
    protected PerformanceImportExportService $PerformanceImportExportService;
    /**
     * Constructor.
     *
     * @param PerformanceService $PerformanceService Service for Performance operations.
     * @param PerformanceImportExportService $PerformanceImportExportService
     */
    public function __construct(PerformanceService $PerformanceService, PerformanceImportExportService $PerformanceImportExportService)
    {
        $this->PerformanceService = $PerformanceService;
        $this->PerformanceImportExportService = $PerformanceImportExportService;
    }

    /**
     * Display the Performance records.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $data = $this->PerformanceService->getPerformanceIndex();
        return Inertia::render('Performance/Index', $data);
    }

    /**
     * Store a new Performance record.
     *
     * @param StorePerformanceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePerformanceRequest $request)
    {
        $this->PerformanceService->storePerformance($request->validated());
        return redirect()->back()->with('success', 'Performance saved successfully.');
    }

    /**
     * Update an existing Performance record.
     *
     * @param UpdatePerformanceRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePerformanceRequest $request, $tenantSlug, $id)
    {
        
        $this->PerformanceService->updatePerformance($id, $request->validated());
        return redirect()->back()->with('success', 'Performance updated successfully.');
    }

    /**
     * Update a Performance record as Admin.
     *
     * @param UpdatePerformanceRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminUpdate(UpdatePerformanceRequest $request, $id)
    {
        
        $this->PerformanceService->updatePerformance($id, $request->validated());
        return redirect()->back()->with('success', 'Performance updated by admin successfully.');
    }

    /**
     * Delete a Performance record.
     *
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($tenantSlug, $id)
    {
        $this->PerformanceService->deletePerformance($id, Auth::user()->tenant_id);
        return redirect()->back()->with('success', 'Performance deleted successfully.');
    }

    /**
     * Delete a Performance record as Admin.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminDestroy($id)
    {
        $this->PerformanceService->deletePerformance($id);
        return redirect()->back()->with('success', 'Performance deleted by admin.');
    }

    /**
     * Import Performance records.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $this->PerformanceImportExportService->importPerformances($request);
        return back()->with('success', 'Performances imported/updated.');
    }

    /**
     * Export Performance records.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return $this->PerformanceImportExportService->exportPerformances();
    }

    /**
     * Delete multiple performance entries.
     *
     * @param Request $request
     * @param string|null $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->PerformanceService->deleteMultiplePerformances($ids, Auth::user()->tenant_id);
        return redirect()->back()->with('success', 'Performance records deleted successfully.');
    }

    /**
     * Delete multiple performance entries as Admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->PerformanceService->deleteMultiplePerformances($ids);
        return redirect()->back()->with('success', 'Performance records deleted successfully.');
    }
}
