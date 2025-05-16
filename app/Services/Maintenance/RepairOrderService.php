<?php

namespace App\Services\Maintenance;

use App\Models\RepairOrder;
use App\Models\Truck;
use App\Models\Vendor;
use App\Models\AreaOfConcern;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\WoStatus;
use Carbon\Carbon;
use App\Services\Summaries\MaintenanceBreakdownService;
//
/**
 * Class RepairOrderService
 *
 * Contains business logic for repair order operations, including CRUD, import, and export.
 */
class RepairOrderService
{
    protected $filteringService;
    protected $maintenanceBreakdownService;

    public function __construct(FilteringService $filteringService, MaintenanceBreakdownService $maintenanceBreakdownService)
    {
        $this->filteringService = $filteringService;
        $this->maintenanceBreakdownService = $maintenanceBreakdownService;
    }

    /**
     * Get canceled QS invoices that need attention
     * 
     * @return array
     */
    public function getCanceledQSInvoices(): array
    {
        return $this->maintenanceBreakdownService->getCanceledQSInvoices();
    }

    /**
     * Get repair order entries for the index view.
     *
     * @return array
     */
    public function getIndexData(): array
    {
        // Properly load the areasOfConcern relationship with withPivot to get all pivot data
        $query = RepairOrder::with(['truck', 'vendor' => function ($query) {
            $query->withTrashed();
        }, 'areasOfConcern' => function ($query) {
            $query->withTrashed()->get();
        }, 'woStatus', 'tenant']);
        
        // Apply date filtering if requested
        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange = [];
        
        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'ro_open_date', $dateRange);
        }
        $request = request();
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(ro_number) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(wo_number) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(invoice) LIKE ?', ["%{$search}%"]);
            });
        }
        if ($request->filled('status_id')) {
            $query->where('wo_status_id', $request->input('status_id'));
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->input('vendor_id'));
        }
        // Get per page value
        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));
        
        // Apply tenant filter for non-admin users
        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $repairOrders = $query->latest('ro_open_date')->paginate($perPage);
        
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        // Calculate week numbers for display
$weekNumber = null;
$startWeekNumber = null;
$endWeekNumber = null;
$year = null;
if (!empty($dateRange) && isset($dateRange['start'])) {
    $startDate = Carbon::parse($dateRange['start']);
    $year = $startDate->year;
$endDate = Carbon::parse($dateRange['end']);
    // compute week numbers (Sunday=first day)
    if (in_array($dateFilter, ['yesterday', 'current-week'])) {
        $weekNumber = $this->weekNumberSundayStart($startDate);
        $startWeekNumber = $endWeekNumber = null;
    } else {
        $weekNumber = null;
        $startWeekNumber = $this->weekNumberSundayStart($startDate);
        $endWeekNumber = isset($dateRange['end']) ? 
            $this->weekNumberSundayStart($endDate) : 
            $startWeekNumber;
    }
}
        // Get canceled QS invoices that need attention
        $canceledQSInvoices = $this->maintenanceBreakdownService->getCanceledQSInvoices();
        
        // Get outstanding invoices
        $minInvoiceAmount = Request::input('minInvoiceAmount');
        $outstandingDate = Request::input('outstandingDate');
        $outstandingInvoices = $this->maintenanceBreakdownService->getOutstandingInvoices($minInvoiceAmount, $outstandingDate);
        $areasOfConcern = $this->maintenanceBreakdownService->getAreasOfConcern($startDate, $endDate);
        $workOrdersByTruck = $this->maintenanceBreakdownService->getWorkOrdersByTruck($startDate, $endDate);
        $filters = [
            'search' => (string) $request->input('search', ''),
            'status_id' => (string) $request->input('status_id', ''),
            'vendor_id' => (string) $request->input('vendor_id', ''),
        ];
        $trucks = Truck::with('tenant')->get();
        return [
            'repairOrders' => $repairOrders,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'trucks' => Truck::get(),
            'vendors' => Vendor::withTrashed()->get(),
            'areasOfConcern' => AreaOfConcern::withTrashed()->get(),
            'woStatuses' => WoStatus::withTrashed()->get(),
            'dateRange' => $dateRange,
            'dateFilter' => $dateFilter,
            'weekNumber' => $weekNumber,
            'startWeekNumber' => $startWeekNumber,
            'endWeekNumber' => $endWeekNumber,
            'year' => $year,
            'canceledQSInvoices' => $canceledQSInvoices,
            'initialMinInvoiceAmount' => $minInvoiceAmount,
            'initialOutstandingDate' => $outstandingDate,
            'outstandingInvoices' => $outstandingInvoices,
            'workOrderByAreasOfConcern' => $areasOfConcern,
            'workOrdersByTruck' => $workOrdersByTruck,
            'filters' => $filters,
            'perPage' => $perPage,
            'entries'     => $trucks,
        ];
    }
/**
     * Get the week‐of‐year for a Carbon date, where weeks run Sunday → Saturday.
     *
     * @param  Carbon  $date
     * @return int
     */
    private function weekNumberSundayStart(Carbon $date): int
    {
        // 1..366
        $dayOfYear   = $date->dayOfYear;

        // 0=Sunday, …, 6=Saturday for Jan 1
        $firstDayDow = $date->copy()
                            ->startOfYear()
                            ->dayOfWeek;
        // shift so weeks bound on Sunday, then ceil
        return (int) ceil(($dayOfYear + $firstDayDow) / 7);
    }
    /**
     * Create a new repair order entry.
     *
     * @param array $data
     * @return RepairOrder
     */
    public function createRepairOrder(array $data)
    {
        $repairOrder = RepairOrder::create($data);
        
        // Properly handle the many-to-many relationship with the pivot table
        if (isset($data['area_of_concerns']) && is_array($data['area_of_concerns'])) {
            $repairOrder->areasOfConcern()->sync($data['area_of_concerns']);
        }
        
        return $repairOrder;
    }

    /**
     * Update an existing repair order entry.
     *
     * @param int $id
     * @param array $data
     * @return RepairOrder
     */
    public function updateRepairOrder($id, array $data)
    {
        $repairOrder = RepairOrder::findOrFail($id);
        $repairOrder->update($data);
        
        // Properly handle the many-to-many relationship with the pivot table
        if (isset($data['area_of_concerns']) && is_array($data['area_of_concerns'])) {
            $repairOrder->areasOfConcern()->sync($data['area_of_concerns']);
        }
        
        return $repairOrder;
    }

    /**
     * Delete a repair order entry.
     *
     * @param int $id
     * @return bool
     */
    public function deleteRepairOrder($id)
    {
        $repairOrder = RepairOrder::findOrFail($id);
        // This will automatically handle the pivot table deletion due to cascade
        $repairOrder->delete();
        return true;
    }

    /**
     * Delete multiple repair order entries.
     *
     * @param array $ids Array of repair order IDs to delete
     * @param int|null $tenantId
     * @return void
     */
    public function deleteMultipleRepairOrders(array $ids, $tenantId = null)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete repair orders they have access to
        $query = RepairOrder::whereIn('id', $ids);
        
        // If not a super admin, restrict to tenant's repair orders
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        $query->delete();
    }
}
