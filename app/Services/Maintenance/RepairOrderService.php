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
use App\Models\MilesDriven;
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
        $query = RepairOrder::with([
            'truck',
            'vendor' => function ($query) {
                $query->withTrashed();
            },
            'areasOfConcern' => function ($query) {
                $query->withTrashed()->get();
            },
            'woStatus',
            'tenant'
        ]);

        $request = request();

        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange = [];

        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'ro_open_date', $dateRange);
        }

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

        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));

        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        if (Auth::user()->hasPermissionTo('repair-orders.view')) {
            $defaultComponent = 'repairOrders';
        } else if (Auth::user()->hasPermissionTo('miles-driven.view')) {
            $defaultComponent = 'milesDriven';
        } else {
            $defaultComponent = 'trucks';
        }

        $openedComponent = $request->input('openedComponent', $defaultComponent);
        $repairOrders = $query->latest('ro_open_date')->paginate($perPage);

        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];

        $weekNumber = null;
        $startWeekNumber = null;
        $endWeekNumber = null;
        $year = null;

        // initialize safely
        $startDate = null;
        $endDate = null;

        // prefer explicit custom request dates
        if ($dateFilter === 'custom' && $request->filled('startDate') && $request->filled('endDate')) {
            $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
            $endDate = Carbon::parse($request->input('endDate'))->endOfDay();

            $dateRange = [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ];
        } elseif (!empty($dateRange) && isset($dateRange['start'], $dateRange['end'])) {
            $startDate = Carbon::parse($dateRange['start'])->startOfDay();
            $endDate = Carbon::parse($dateRange['end'])->endOfDay();
        }

        if ($startDate && $endDate) {
            $year = $startDate->year;

            if (in_array($dateFilter, ['yesterday', 'current-week'], true)) {
                $weekNumber = $this->weekNumberSundayStart($startDate);
                $startWeekNumber = null;
                $endWeekNumber = null;
            } else {
                $weekNumber = null;
                $startWeekNumber = $this->weekNumberSundayStart($startDate);
                $endWeekNumber = $this->weekNumberSundayStart($endDate);
            }
        }

        $canceledQSInvoices = $this->maintenanceBreakdownService->getCanceledQSInvoices();
        $outstandingInvoices = $this->maintenanceBreakdownService->getOutstandingInvoices(null, null);

        $areasOfConcern = ($startDate && $endDate)
            ? $this->maintenanceBreakdownService->getAreasOfConcern($startDate, $endDate)
            : collect();

        $workOrdersByTruck = ($startDate && $endDate)
            ? $this->maintenanceBreakdownService->getWorkOrdersByTruck($startDate, $endDate)
            : collect();

        $filters = [
            'search' => (string) $request->input('search', ''),
            'status_id' => (string) $request->input('status_id', ''),
            'vendor_id' => (string) $request->input('vendor_id', ''),
        ];

        $trucks = Truck::with('tenant')->get();
        $milesEntries = MilesDriven::where('tenant_id', Auth::user()->tenant_id)
            ->latest('week_start_date')
            ->get();
        $permissions = Auth::user()->getAllPermissions();

        return [
            'repairOrders' => $repairOrders,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'trucks' => $trucks,
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
            'outstandingInvoices' => $outstandingInvoices,
            'workOrderByAreasOfConcern' => $areasOfConcern,
            'workOrdersByTruck' => $workOrdersByTruck,
            'filters' => $filters,
            'perPage' => $perPage,
            'openedComponent' => $openedComponent,
            'milesEntries' => $milesEntries,
            'permissions' => $permissions,
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
        $dayOfYear = $date->dayOfYear;

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
