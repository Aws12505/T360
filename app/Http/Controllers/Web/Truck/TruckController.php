<?php

namespace App\Http\Controllers\Web\Truck;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTruckRequest;
use App\Http\Requests\UpdateTruckRequest;
use App\Services\Truck\TruckDataService;
use App\Services\Truck\TruckImportExportService;
use Inertia\Inertia;

class TruckController extends Controller
{
    protected TruckDataService $truckDataService;
    protected TruckImportExportService $truckImportExportService;

    public function __construct(TruckDataService $truckDataService, TruckImportExportService $truckImportExportService)
    {
        $this->truckDataService = $truckDataService;
        $this->truckImportExportService = $truckImportExportService;
    }

    /**
     * Display truck entries.
     */
    public function index(Request $request)
    {
        $data = $this->truckDataService->getTruckIndex();
        return Inertia::render('Truck/Truck', $data);
    }

    /**
     * Store a new truck entry.
     */
    public function store(StoreTruckRequest $request)
    {
        $this->truckDataService->createTruck($request->validated());
        return redirect()->back()->with('success', 'Truck created successfully.');
    }

    /**
     * Update an existing truck entry.
     */
    public function update(UpdateTruckRequest $request, $tenantSlug, $id)
    {
        $this->truckDataService->updateTruck($id, $request->validated());
        return redirect()->back()->with('success', 'Truck updated successfully.');
    }

    /**
     * Update a truck entry as Admin.
     */
    public function updateAdmin(UpdateTruckRequest $request, $id)
    {
        $this->truckDataService->updateTruck($id, $request->validated());
        return redirect()->back()->with('success', 'Truck updated successfully.');
    }

    /**
     * Delete a truck entry.
     */
    public function destroy(Request $request, $tenantSlug = null, $id)
    {
        $this->truckDataService->deleteTruck($id);
        return redirect()->back()->with('success', 'Truck deleted successfully.');
    }

    /**
     * Delete a truck entry as Admin.
     */
    public function destroyAdmin(Request $request, $id)
    {
        $this->truckDataService->deleteTruck($id);
        return redirect()->back()->with('success', 'Truck deleted successfully.');
    }

    /**
     * Import truck data from a CSV file.
     */
    public function import(Request $request)
    {
        $this->truckImportExportService->importData($request);
        return redirect()->back()->with('success', 'Truck data imported successfully.');
    }

    /**
     * Export truck data to a CSV file.
     */
    public function export()
    {
        return $this->truckImportExportService->exportData();
    }
}
