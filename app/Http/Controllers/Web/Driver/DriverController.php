<?php

namespace App\Http\Controllers\Web\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Driver\StoreDriverRequest;
use App\Http\Requests\Driver\UpdateDriverRequest;
use App\Services\Driver\DriverDataService;
use App\Services\Driver\DriverImportExportService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Driver;
class DriverController extends Controller
{
    protected DriverDataService $driverDataService;
    protected DriverImportExportService $driverImportExportService;

    public function __construct(DriverDataService $driverDataService, DriverImportExportService $driverImportExportService)
    {
        $this->driverDataService = $driverDataService;
        $this->driverImportExportService = $driverImportExportService;
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
     * Import driver data from a CSV file.
     */
    public function import(Request $request)
    {
        $this->driverImportExportService->importData($request);
        return redirect()->back()->with('success', 'Driver data imported successfully.');
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

    public function indexProfile()
    {
        $driver = Auth::guard('driver')->user();
        // Get data for the dashboard
        
        $dashboardData = $this->driverDataService->getProfileData($driver);

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

        $profile = $this->driverDataService->getProfileData($driver);

        return Inertia::render('Driver/Show', [
            // matches props in Show.vue
            'driver'     => $profile,
            'tenantSlug' => Auth::user()->tenant_id ? $tenantSlug : null,
            'permissions'=> Auth::user()->getAllPermissions(),
        ]);
    }

    /**
     * Admin: show any driver's profile.
     */
    public function showAdmin(Request $request, int $id)
    {
        $driver  = Driver::findOrFail($id);
        $profile = $this->driverDataService->getProfileData($driver);

        return Inertia::render('Driver/Show', [
            'driver'     => $profile,
            'tenantSlug' => null,
            'permissions'=> Auth::user()->getAllPermissions(),
        ]);
    }
}
