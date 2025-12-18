<?php

namespace App\Http\Controllers\Web\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Driver\StoreDriverRequest;
use App\Http\Requests\Driver\UpdateDriverRequest;
use App\Services\Driver\DriverDataService;
use App\Services\Driver\DriverImportExportService;
use App\Services\Driver\DriverImportValidationService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Driver;

class DriverController extends Controller
{
    protected DriverDataService $driverDataService;
    protected DriverImportExportService $driverImportExportService;
    protected DriverImportValidationService $driverImportValidationService;

    public function __construct(
        DriverDataService $driverDataService,
        DriverImportExportService $driverImportExportService,
        DriverImportValidationService $driverImportValidationService
    ) {
        $this->driverDataService = $driverDataService;
        $this->driverImportExportService = $driverImportExportService;
        $this->driverImportValidationService = $driverImportValidationService;
    }

    /**
     * Display driver entries.
     */
    public function index(Request $request)
    {
        $data = $this->driverDataService->getDriverIndex();
        return Inertia::render('Driver/Driver', $data);
    }

    /**
     * Store a new driver entry.
     */
    public function store(StoreDriverRequest $request)
    {
        $this->driverDataService->createDriver($request->validated());
        return redirect()->back()->with('success', 'Driver created successfully.');
    }

    /**
     * Update an existing driver entry.
     */
    public function update(UpdateDriverRequest $request, $tenantSlug, $id)
    {
        $this->driverDataService->updateDriver($id, $request->validated());
        return redirect()->back()->with('success', 'Driver updated successfully.');
    }

    /**
     * Update a driver entry as Admin.
     */
    public function updateAdmin(UpdateDriverRequest $request, $id)
    {
        $this->driverDataService->updateDriver($id, $request->validated());
        return redirect()->back()->with('success', 'Driver updated successfully.');
    }

    /**
     * Delete a driver entry.
     */
    public function destroy(Request $request, $tenantSlug = null, $id)
    {
        $this->driverDataService->deleteDriver($id);
        return redirect()->back()->with('success', 'Driver deleted successfully.');
    }

    /**
     * Delete a driver entry as Admin.
     */
    public function destroyAdmin(Request $request, $id)
    {
        $this->driverDataService->deleteDriver($id);
        return redirect()->back()->with('success', 'Driver deleted successfully.');
    }

    /**
     * Step 1: Validate CSV file (NO import yet)
     */
    public function validateImport(Request $request, $tenantSlug = null)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            $results = $this->driverImportValidationService
                ->validateDriversCsv($request->file('file'));

            // Header error
            if (isset($results['header_error'])) {
                session()->forget('driver_import_validation_results');
                session()->forget('driver_import_file_path');

                return back()->with('importValidation', [
                    'success' => false,
                    'header_error' => $results['header_error'],
                    'results' => $results,
                ]);
            }

            session(['driver_import_validation_results' => $results]);

            // Store file temporarily ONLY if fully valid
            if (($results['summary']['invalid'] ?? 0) === 0) {
                $path = $request->file('file')->store('temp-imports');
                session(['driver_import_file_path' => $path]);
            } else {
                session()->forget('driver_import_file_path');
            }

            return back()->with('importValidation', [
                'success' => true,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            session()->forget('driver_import_validation_results');
            session()->forget('driver_import_file_path');

            return back()->with('importValidation', [
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Step 2: Confirm import (only after successful validation)
     */
    public function confirmImport(Request $request, $tenantSlug = null)
    {
        try {
            $filePath = session('driver_import_file_path');

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

            $importRequest = new Request();
            $importRequest->files->set('csv_file', $file);

            // reuse your existing import service (expects csv_file)
            $this->driverImportExportService->importData($importRequest);

            Storage::delete($filePath);
            session()->forget(['driver_import_file_path', 'driver_import_validation_results']);

            return back()->with('success', 'Drivers imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download error report CSV
     */
    public function downloadErrorReport(Request $request, $tenantSlug = null)
    {
        try {
            $results = session('driver_import_validation_results');

            if (!$results || empty($results['invalid'])) {
                return back()->with('error', 'No validation errors to download.');
            }

            $filePath = $this->driverImportValidationService
                ->generateErrorReport($results['invalid']);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate error report: ' . $e->getMessage());
        }
    }

    /**
     * Export driver data to a CSV file.
     */
    public function export()
    {
        return $this->driverImportExportService->exportData();
    }

    /**
     * Delete multiple driver entries.
     *
     * @param Request $request
     * @param string|null $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->driverDataService->deleteMultipleDrivers($ids);
        return redirect()->back()->with('success', 'Drivers deleted successfully.');
    }
    
    /**
     * Delete multiple driver entries as Admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->driverDataService->deleteMultipleDrivers($ids);
        return redirect()->back()->with('success', 'Drivers deleted successfully.');
    }

    public function indexProfile(Request $request)
    {
        $driver = Auth::guard('driver')->user();
        // Get data for the dashboard
        $dateFilter = $request->input('dateFilter', 'full');
        $dashboardData = $this->driverDataService->getProfileData($driver,$dateFilter);

        // Return Inertia page
        return Inertia::render('Driver/DriverProfile', [
            'driverData' => $dashboardData,
        ]);
    }

     /**
     * Tenant: show a single driver's profile.
     */
    public function show(Request $request, string $tenantSlug, int $id)
    {
        $driver = Driver::with('tenant')->findOrFail($id);

        // enforce tenant boundary
        if (Auth::user()->tenant_id && Auth::user()->tenant->slug !== $tenantSlug) {
            abort(403);
        }
$dateFilter = $request->input('dateFilter', 'full');
        // Get data for the dashboard
        $profile = $this->driverDataService->getProfileData($driver, $dateFilter);
        return Inertia::render('Driver/Show', [
            // matches props in Show.vue
            'driver'     => $profile,
            'tenantSlug' => Auth::user()->tenant_id ? $tenantSlug : null,
            'permissions'=> Auth::user()->getAllPermissions(),
            'driverID' => $id,
        ]);
    }

    /**
     * Admin: show any driver's profile.
     */
    public function showAdmin(Request $request, int $id)
    {
        $driver  = Driver::findOrFail($id);
        $dateFilter = $request->input('dateFilter', 'full');
        $profile = $this->driverDataService->getProfileData($driver,$dateFilter);
        return Inertia::render('Driver/Show', [
            'driver'     => $profile,
            'tenantSlug' => null,
            'permissions'=> Auth::user()->getAllPermissions(),
            'driverID' => $id,
        ]);
    }
}
