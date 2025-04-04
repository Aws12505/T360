<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepairOrderRequest;
use App\Http\Requests\UpdateRepairOrderRequest;
use App\Services\RepairOrder\RepairOrderService;
use App\Services\RepairOrder\RepairOrderImportExportService;
use App\Services\AreasOfConcernService;
use App\Services\VendorsService;
use App\Http\Requests\StoreAreaOfConcernRequest;
use App\Http\Requests\StoreVendorRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RepairOrderController extends Controller
{
    protected $repairOrderService;
    protected $repairOrderImportExportService;
    protected $areasOfConcernService;
    protected $vendorsService;
    
    public function __construct(
        RepairOrderService $repairOrderService, 
        RepairOrderImportExportService $repairOrderImportExportService,
        AreasOfConcernService $areasOfConcernService,
        VendorsService $vendorsService
    ) {
        $this->repairOrderService = $repairOrderService;
        $this->repairOrderImportExportService = $repairOrderImportExportService;
        $this->areasOfConcernService = $areasOfConcernService;
        $this->vendorsService = $vendorsService;
    }

    public function index(Request $request)
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
     * @param \App\Http\Requests\StoreAreaOfConcernRequest $request
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
     * Create a new vendor.
     *
     * @param \App\Http\Requests\StoreVendorRequest $request
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
}
