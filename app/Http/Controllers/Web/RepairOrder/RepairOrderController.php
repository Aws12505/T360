<?php

namespace App\Http\Controllers\Web\RepairOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreRepairOrderRequest;
use App\Http\Requests\Maintenance\UpdateRepairOrderRequest;
use App\Services\Maintenance\RepairOrderService;
use App\Services\Maintenance\{RepairOrderImportExportService, ImportValidationService};
use App\Services\Maintenance\AreasOfConcernService;
use App\Services\Maintenance\VendorsService;
use App\Services\Maintenance\WoStatusService;
use App\Http\Requests\Maintenance\StoreAreaOfConcernRequest;
use App\Http\Requests\Maintenance\StoreVendorRequest;
use App\Http\Requests\Maintenance\StoreWoStatusRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class RepairOrderController extends Controller
{
    protected $repairOrderService;
    protected $repairOrderImportExportService;
    protected $areasOfConcernService;
    protected $vendorsService;
    protected $woStatusService;
    protected $importValidationService;

    public function __construct(
        RepairOrderService $repairOrderService,
        RepairOrderImportExportService $repairOrderImportExportService,
        AreasOfConcernService $areasOfConcernService,
        VendorsService $vendorsService,
        WoStatusService $woStatusService,
        ImportValidationService $importValidationService
    ) {
        $this->repairOrderService = $repairOrderService;
        $this->repairOrderImportExportService = $repairOrderImportExportService;
        $this->areasOfConcernService = $areasOfConcernService;
        $this->vendorsService = $vendorsService;
        $this->woStatusService = $woStatusService;
        $this->importValidationService = $importValidationService;
    }

    public function index(Request $request)
    {
        $data = $this->repairOrderService->getIndexData();
        return Inertia::render('AssetMaintenance', $data);
    }

    // public function index2(Request $request)
    // {
    //     $data = $this->repairOrderService->getIndexData();
    //     return Inertia::render('RepairOrders/Index', $data);
    // }

    public function store(StoreRepairOrderRequest $request)
    {
        $this->repairOrderService->createRepairOrder($request->validated());
        return redirect()->back()->with('success', 'Repair Order created successfully.');
    }

    public function updateAdmin(UpdateRepairOrderRequest $request, $repair_order)
    {
        $this->repairOrderService->updateRepairOrder($repair_order, $request->validated());
        return redirect()->back()->with('success', 'Repair Order updated successfully.');
    }

    public function update(UpdateRepairOrderRequest $request, $tenantSlug, $repair_order)
    {
        $this->repairOrderService->updateRepairOrder($repair_order, $request->validated());
        return redirect()->back()->with('success', 'Repair Order updated successfully.');
    }

    public function destroyAdmin($repair_order)
    {
        $this->repairOrderService->deleteRepairOrder($repair_order);
        return redirect()->back()->with('success', 'Repair Order deleted successfully.');
    }

    public function destroy(Request $request, $tenantSlug = null, $id)
    {
        $this->repairOrderService->deleteRepairOrder($id);
        return redirect()->back()->with('success', 'Repair Order deleted successfully.');
    }

    /**
     * Step 1: Validate the import file
     */
public function validateImport(Request $request)
{
    $request->validate([
        'importType' => 'required|in:template,quicksight',
        'file' => 'required|file|mimes:csv,txt|max:10240',

        // If SuperAdmin and importing QuickSight, we need a tenant target
        'tenant_id' => 'nullable|integer|exists:tenants,id',
    ]);

    try {
        $importType = $request->input('importType');

        // SuperAdmin + QuickSight needs tenant_id (because QS export doesn't include tenant_name)
        $user = Auth::user();
        $isSuperAdmin = $user && $user->tenant_id === null;
        if ($isSuperAdmin && $importType === 'quicksight' && !$request->filled('tenant_id')) {
            return back()->with('importValidation', [
                'success' => false,
                'message' => 'Tenant is required for QuickSight import as SuperAdmin.',
            ]);
        }

        $results = $this->importValidationService->validateRepairOrdersCsv(
            $request->file('file'),
            $importType,
            $request->input('tenant_id')
        );

        if (isset($results['header_error'])) {
            return back()->with('importValidation', [
                'success' => false,
                'header_error' => $results['header_error'],
                'results' => $results,
            ]);
        }

        session([
            'import_validation_results' => $results,
            'import_type' => $importType,
            'import_tenant_id' => $request->input('tenant_id'),
        ]);

        if (($results['summary']['invalid'] ?? 0) === 0) {
            $path = $request->file('file')->store('temp-imports');
            session(['import_file_path' => $path]);
        }

        return back()->with('importValidation', [
            'success' => true,
            'results' => $results,
        ]);
    } catch (\Exception $e) {
        return back()->with('importValidation', [
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}


/**
 * Step 2: Confirm and execute the import
 */
public function confirmImport(Request $request)
{
    try {
        $filePath = session('import_file_path');
        $importType = session('import_type', 'template');
        $tenantId = session('import_tenant_id');

        if (!$filePath || !Storage::exists($filePath)) {
            return back()->with('error', 'Import session expired. Please upload the file again.');
        }

        $storedFile = Storage::path($filePath);

        $file = new \Illuminate\Http\UploadedFile(
            $storedFile,
            basename($filePath),
            mime_content_type($storedFile) ?: 'text/csv',
            null,
            true
        );

        $importRequest = new Request();
        $importRequest->files->set('file', $file);
        $importRequest->merge([
            'importType' => $importType,
            'tenant_id'  => $tenantId,
        ]);

        // IMPORTANT: return the actual response from the service
        $response = $this->repairOrderImportExportService->importRepairOrders($importRequest);

        // Cleanup AFTER running import
        Storage::delete($filePath);
        session()->forget(['import_file_path', 'import_validation_results', 'import_type', 'import_tenant_id']);

        return $response;
    } catch (\Exception $e) {
        return back()->with('error', 'Import failed: ' . $e->getMessage());
    }
}

/**
 * Download error report as CSV
 */
public function downloadErrorReport()
{
    try {
        $results = session('import_validation_results');

        if (!$results || empty($results['invalid'])) {
            return back()->with('error', 'No validation errors to download.');
        }

        $filePath = $this->importValidationService->generateErrorReport($results['invalid']);

        return response()->download($filePath)->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to generate error report: ' . $e->getMessage());
    }
}

    public function export()
    {
        return $this->repairOrderImportExportService->exportRepairOrders();
    }

    /**
     * Create a new area of concern.
     */
    public function storeAreaOfConcern(StoreAreaOfConcernRequest $request)
    {
        $areaOfConcern = $this->areasOfConcernService->createAreaOfConcern($request->validated());
        return redirect()->back()->with('success', 'Area of Concern created successfully.');
    }

    /**
     * Delete an area of concern.
     */
    public function destroyAreaOfConcern($id)
    {
        $this->areasOfConcernService->deleteAreaOfConcern($id);
        return redirect()->back()->with('success', 'Area of Concern deleted successfully.');
    }

    /**
     * Restore a soft-deleted area of concern.
     */
    public function restoreAreaOfConcern($id)
    {
        $this->areasOfConcernService->restoreAreaOfConcern($id);
        return redirect()->back()->with('success', 'Area of Concern restored successfully.');
    }

    /**
     * Permanently delete an area of concern.
     */
    public function forceDeleteAreaOfConcern($id)
    {
        $this->areasOfConcernService->forceDeleteAreaOfConcern($id);
        return redirect()->back()->with('success', 'Area of Concern permanently deleted.');
    }

    /**
     * Create a new vendor.
     */
    public function storeVendor(StoreVendorRequest $request)
    {
        $vendor = $this->vendorsService->createVendor($request->validated());
        return redirect()->back()->with('success', 'Vendor created successfully.');
    }

    /**
     * Delete a vendor.
     */
    public function destroyVendor($id)
    {
        $this->vendorsService->deleteVendor($id);
        return redirect()->back()->with('success', 'Vendor deleted successfully.');
    }

    /**
     * Restore a soft-deleted vendor.
     */
    public function restoreVendor($id)
    {
        $this->vendorsService->restoreVendor($id);
        return redirect()->back()->with('success', 'Vendor restored successfully.');
    }

    /**
     * Permanently delete a vendor.
     */
    public function forceDeleteVendor($id)
    {
        $this->vendorsService->forceDeleteVendor($id);
        return redirect()->back()->with('success', 'Vendor permanently deleted.');
    }

    /**
     * Delete multiple repair order entries.
     */
    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->repairOrderService->deleteMultipleRepairOrders($ids, Auth::user()->tenant_id);
        return redirect()->back()->with('success', 'Repair Orders deleted successfully.');
    }

    /**
     * Delete multiple repair order entries as Admin.
     */
    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->repairOrderService->deleteMultipleRepairOrders($ids);
        return redirect()->back()->with('success', 'Repair Orders deleted successfully.');
    }

    /**
     * Create a new work order status.
     */
    public function storeWoStatus(StoreWoStatusRequest $request)
    {
        $woStatus = $this->woStatusService->createWoStatus($request->validated());
        return redirect()->back()->with('success', 'Work Order Status created successfully.');
    }

    /**
     * Delete a work order status.
     */
    public function destroyWoStatus($id)
    {
        $this->woStatusService->deleteWoStatus($id);
        return redirect()->back()->with('success', 'Work Order Status deleted successfully.');
    }

    /**
     * Restore a soft-deleted work order status.
     */
    public function restoreWoStatus($id)
    {
        $this->woStatusService->restoreWoStatus($id);
        return redirect()->back()->with('success', 'Work Order Status restored successfully.');
    }

    /**
     * Permanently delete a work order status.
     */
    public function forceDeleteWoStatus($id)
    {
        $this->woStatusService->forceDeleteWoStatus($id);
        return redirect()->back()->with('success', 'Work Order Status permanently deleted.');
    }
}
