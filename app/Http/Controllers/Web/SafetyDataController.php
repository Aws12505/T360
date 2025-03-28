<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSafetyDataRequest;
use App\Http\Requests\UpdateSafetyDataRequest;
use App\Services\SafetyDataService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

/**
 * Class SafetyDataController
 *
 * This controller handles safety data CRUD as well as import/export functions.
 * It delegates processing to the SafetyDataService.
 *
 * Command:
 *   php artisan make:controller Web/SafetyDataController
 */
class SafetyDataController extends Controller
{
    protected SafetyDataService $safetyDataService;

    /**
     * Constructor.
     *
     * @param SafetyDataService $safetyDataService Service to handle safety data logic.
     */
    public function __construct(SafetyDataService $safetyDataService)
    {
        $this->safetyDataService = $safetyDataService;
    }

    /**
     * Display safety data entries.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $data = $this->safetyDataService->getSafetyDataIndex();
        return Inertia::render('Safety/Safety', $data);
    }

    /**
     * Store a new safety data entry.
     *
     * @param StoreSafetyDataRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreSafetyDataRequest $request)
    {
        
        $this->safetyDataService->createEntry($request->validated());
        return redirect()->back()->with('success', 'Entry created successfully.');
    }

    /**
     * Update an existing safety data entry.
     *
     * @param UpdateSafetyDataRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateSafetyDataRequest $request, $tenantSlug, $id)
    {
        
        $this->safetyDataService->updateEntry($id, $request->validated());
        return redirect()->back()->with('success', 'Entry updated successfully.');
    }

    /**
     * Update a safety data entry as Admin.
     *
     * @param UpdateSafetyDataRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdmin(UpdateSafetyDataRequest $request, $id)
    {
        
        $this->safetyDataService->updateEntry($id, $request->validated());
        return redirect()->back()->with('success', 'Entry updated successfully.');
    }

    /**
     * Delete a safety data entry.
     *
     * @param Request $request
     * @param string|null $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $tenantSlug = null, $id)
    {
        $this->safetyDataService->deleteEntry($id);
        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }

    /**
     * Delete a safety data entry as Admin.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAdmin(Request $request, $id)
    {
        $this->safetyDataService->deleteEntry($id);
        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }

    /**
     * Import safety data from an Excel file.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $this->safetyDataService->importData($request);
        return redirect()->back()->with('success', 'Safety data imported successfully.');
    }

    /**
     * Export safety data to CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return $this->safetyDataService->exportData();
    }
}
