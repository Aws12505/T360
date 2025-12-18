<?php

namespace App\Http\Controllers\Web\Truck;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Trucks\StoreTruckRequest;
use App\Http\Requests\Trucks\UpdateTruckRequest;
use App\Services\Truck\TruckDataService;
use App\Services\Truck\TruckImportExportService;
use Inertia\Inertia;
use App\Services\Truck\TruckImportValidationService;
use Illuminate\Support\Facades\Storage;

class TruckController extends Controller
{
    protected TruckDataService $truckDataService;
    protected TruckImportExportService $truckImportExportService;
    protected TruckImportValidationService $truckImportValidationService;

    public function __construct(TruckDataService $truckDataService, TruckImportExportService $truckImportExportService, TruckImportValidationService $truckImportValidationService)
    {
        $this->truckDataService = $truckDataService;
        $this->truckImportExportService = $truckImportExportService;
        $this->truckImportValidationService = $truckImportValidationService;
    }

    /**
     * Display truck entries.
     */
    public function index(Request $request)
    {
        $data = $this->truckDataService->getTruckIndex();
        return Inertia::render('AssetManagement', $data);
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

    public function validateImport(Request $request, $tenantSlug = null)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt|max:10240',
    ]);

    try {
        $results = $this->truckImportValidationService->validateTrucksCsv($request->file('file'));

        if (isset($results['header_error'])) {
            session()->forget('truck_import_validation_results');
            session()->forget('truck_import_file_path');

            return back()->with('importValidation', [
                'success' => false,
                'header_error' => $results['header_error'],
                'results' => $results,
            ]);
        }

        session(['truck_import_validation_results' => $results]);

        // only store file if there are zero invalid rows
        if (($results['summary']['invalid'] ?? 0) === 0) {
            $path = $request->file('file')->store('temp-imports');
            session(['truck_import_file_path' => $path]);
        } else {
            session()->forget('truck_import_file_path');
        }

        return back()->with('importValidation', [
            'success' => true,
            'results' => $results,
        ]);
    } catch (\Exception $e) {
        session()->forget('truck_import_validation_results');
        session()->forget('truck_import_file_path');

        return back()->with('importValidation', [
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}

public function confirmImport(Request $request, $tenantSlug = null)
{
    try {
        $filePath = session('truck_import_file_path');

        if (!$filePath || !Storage::exists($filePath)) {
            return back()->with('error', 'Import session expired. Please upload the file again.');
        }

        $storedFile = Storage::path($filePath);

        $file = new \Illuminate\Http\UploadedFile(
            $storedFile,
            basename($filePath),
            mime_content_type($storedFile),
            null,
            true
        );

        // TruckImportExportService expects csv_file
        $importRequest = new Request();
        $importRequest->files->set('csv_file', $file);

        $this->truckImportExportService->importData($importRequest);

        Storage::delete($filePath);
        session()->forget(['truck_import_file_path', 'truck_import_validation_results']);

        return back()->with('success', 'Truck data imported successfully.');
    } catch (\Exception $e) {
        return back()->with('error', 'Import failed: ' . $e->getMessage());
    }
}

public function downloadErrorReport(Request $request, $tenantSlug = null)
{
    try {
        $results = session('truck_import_validation_results');

        if (!$results || empty($results['invalid'])) {
            return back()->with('error', 'No validation errors to download.');
        }

        $filePath = $this->truckImportValidationService->generateErrorReport($results['invalid']);

        return response()->download($filePath)->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to generate error report: ' . $e->getMessage());
    }
}


    /**
     * Export truck data to a CSV file.
     */
    public function export()
    {
        return $this->truckImportExportService->exportData();
    }

    /**
 * Delete multiple truck entries.
 */
public function destroyBulk(Request $request, $tenantSlug = null)
{
    $ids = $request->input('ids', []);
    $this->truckDataService->deleteMultipleTrucks($ids);
    return redirect()->back()->with('success', 'Trucks deleted successfully.');
}

/**
 * Delete multiple truck entries as Admin.
 */
public function destroyBulkAdmin(Request $request)
{
    $ids = $request->input('ids', []);
    $this->truckDataService->deleteMultipleTrucks($ids);
    return redirect()->back()->with('success', 'Trucks deleted successfully.');
}
}
