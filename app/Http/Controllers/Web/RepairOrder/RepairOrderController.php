<?php

namespace App\Http\Controllers\Web\RepairOrder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreRepairOrderRequest;
use App\Http\Requests\Maintenance\UpdateRepairOrderRequest;
use App\Services\Maintenance\RepairOrderService;
use App\Services\Maintenance\RepairOrderImportExportService;
use App\Services\Maintenance\AreasOfConcernService;
use App\Services\Maintenance\VendorsService;
use App\Services\Maintenance\WoStatusService;
use App\Http\Requests\Maintenance\StoreAreaOfConcernRequest;
use App\Http\Requests\Maintenance\StoreVendorRequest;
use App\Http\Requests\Maintenance\StoreWoStatusRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RepairOrderController extends Controller
{
    protected $repairOrderService;
    protected $repairOrderImportExportService;
    protected $areasOfConcernService;
    protected $vendorsService;
    protected $woStatusService;
    
    public function __construct(
        RepairOrderService $repairOrderService, 
        RepairOrderImportExportService $repairOrderImportExportService,
        AreasOfConcernService $areasOfConcernService,
        VendorsService $vendorsService,
        WoStatusService $woStatusService
    ) {
        $this->repairOrderService = $repairOrderService;
        $this->repairOrderImportExportService = $repairOrderImportExportService;
        $this->areasOfConcernService = $areasOfConcernService;
        $this->vendorsService = $vendorsService;
        $this->woStatusService = $woStatusService;
    }

    public function index(Request $request)
    {
        $data = $this->repairOrderService->getIndexData();
        return Inertia::render('AssetMaintenance', $data);
    }
    public function index2(Request $request)
    {
        $data = $this->repairOrderService->getIndexData();
        return Inertia::render('RepairOrders/Index', $data);
    }
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
    public function update(UpdateRepairOrderRequest $request,$tenantSlug, $repair_order)
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
    public function import(Request $request)
{
    $this->repairOrderImportExportService->importRepairOrders($request);
    return back()->with('success', 'Repair Orders imported/updated.');
}

public function export()
{
    return $this->repairOrderImportExportService->exportRepairOrders();
}


    /**
     * Create a new area of concern.
     *
     * @param \App\Http\Requests\Maintenance\StoreAreaOfConcernRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAreaOfConcern(StoreAreaOfConcernRequest $request)
    {
        $areaOfConcern = $this->areasOfConcernService->createAreaOfConcern($request->validated());
        return redirect()->back()->with('success', 'Area of Concern created successfully.');
    }

    /**
     * Delete an area of concern.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAreaOfConcern($id)
    {
        $this->areasOfConcernService->deleteAreaOfConcern($id);
        return redirect()->back()->with('success', 'Area of Concern deleted successfully.');
    }
    
    /**
     * Restore a soft-deleted area of concern.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreAreaOfConcern($id)
    {
        $this->areasOfConcernService->restoreAreaOfConcern($id);
        return redirect()->back()->with('success', 'Area of Concern restored successfully.');
    }
    
    /**
     * Permanently delete an area of concern.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDeleteAreaOfConcern($id)
    {
        $this->areasOfConcernService->forceDeleteAreaOfConcern($id);
        return redirect()->back()->with('success', 'Area of Concern permanently deleted.');
    }

    /**
     * Create a new vendor.
     *
     * @param \App\Http\Requests\Maintenance\StoreVendorRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeVendor(StoreVendorRequest $request)
    {
        $vendor = $this->vendorsService->createVendor($request->validated());
        return redirect()->back()->with('success', 'Vendor created successfully.');
    }

    /**
     * Delete a vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyVendor($id)
    {
        $this->vendorsService->deleteVendor($id);
        return redirect()->back()->with('success', 'Vendor deleted successfully.');
    }
    
    /**
     * Restore a soft-deleted vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreVendor($id)
    {
        $this->vendorsService->restoreVendor($id);
        return redirect()->back()->with('success', 'Vendor restored successfully.');
    }
    
    /**
     * Permanently delete a vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDeleteVendor($id)
    {
        $this->vendorsService->forceDeleteVendor($id);
        return redirect()->back()->with('success', 'Vendor permanently deleted.');
    }

    /**
     * Delete multiple repair order entries.
     *
     * @param Request $request
     * @param string|null $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulk(Request $request, $tenantSlug = null)
    {
        $ids = $request->input('ids', []);
        $this->repairOrderService->deleteMultipleRepairOrders($ids, Auth::user()->tenant_id);
        return redirect()->back()->with('success', 'Repair Orders deleted successfully.');
    }

    /**
     * Delete multiple repair order entries as Admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBulkAdmin(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->repairOrderService->deleteMultipleRepairOrders($ids);
        return redirect()->back()->with('success', 'Repair Orders deleted successfully.');
    }

    /**
     * Create a new work order status.
     *
     * @param \App\Http\Requests\Maintenance\StoreWoStatusRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeWoStatus(StoreWoStatusRequest $request)
    {
        $woStatus = $this->woStatusService->createWoStatus($request->validated());
        return redirect()->back()->with('success', 'Work Order Status created successfully.');
    }

    /**
     * Delete a work order status.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyWoStatus($id)
    {
        $this->woStatusService->deleteWoStatus($id);
        return redirect()->back()->with('success', 'Work Order Status deleted successfully.');
    }
    
    /**
     * Restore a soft-deleted work order status.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreWoStatus($id)
    {
        $this->woStatusService->restoreWoStatus($id);
        return redirect()->back()->with('success', 'Work Order Status restored successfully.');
    }
    
    /**
     * Permanently delete a work order status.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDeleteWoStatus($id)
    {
        $this->woStatusService->forceDeleteWoStatus($id);
        return redirect()->back()->with('success', 'Work Order Status permanently deleted.');
    }
}
