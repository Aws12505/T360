<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StorePerformanceRequest;
use App\Http\Requests\UpdatePerformanceRequest;
use App\Services\PerformanceService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

/**
 * Class PerformanceController
 *
 * This controller handles performance records including CRUD, import/export,
 * and rating calculations. It delegates business logic to PerformanceService.
 *
 * Command:
 *   php artisan make:controller Web/PerformanceController
 */
class PerformanceController extends Controller
{
    protected PerformanceService $performanceService;

    /**
     * Constructor.
     *
     * @param PerformanceService $performanceService Service for performance operations.
     */
    public function __construct(PerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Display the performance records.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $data = $this->performanceService->getPerformanceIndex();
        return Inertia::render('Performance/Index', $data);
    }

    /**
     * Store a new performance record.
     *
     * @param StorePerformanceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePerformanceRequest $request)
    {
        $this->performanceService->storePerformance($request->validated());
        return redirect()->back()->with('success', 'Performance saved successfully.');
    }

    /**
     * Update an existing performance record.
     *
     * @param UpdatePerformanceRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePerformanceRequest $request, $tenantSlug, $id)
    {
        
        $this->performanceService->updatePerformance($id, $request->validated());
        return redirect()->back()->with('success', 'Performance updated successfully.');
    }

    /**
     * Update a performance record as Admin.
     *
     * @param UpdatePerformanceRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminUpdate(UpdatePerformanceRequest $request, $id)
    {
        
        $this->performanceService->updatePerformance($id, $request->validated());
        return redirect()->back()->with('success', 'Performance updated by admin successfully.');
    }

    /**
     * Delete a performance record.
     *
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($tenantSlug, $id)
    {
        $this->performanceService->deletePerformance($id, Auth::user()->tenant_id);
        return redirect()->back()->with('success', 'Performance deleted successfully.');
    }

    /**
     * Delete a performance record as Admin.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminDestroy($id)
    {
        $this->performanceService->deletePerformance($id);
        return redirect()->back()->with('success', 'Performance deleted by admin.');
    }

    /**
     * Import performance records.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $this->performanceService->importPerformances($request);
        return back()->with('success', 'Performances imported/updated.');
    }

    /**
     * Export performance records.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return $this->performanceService->exportPerformances();
    }
}
