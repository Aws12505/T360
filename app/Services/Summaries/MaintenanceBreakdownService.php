<?php

namespace App\Services\Summaries;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TenantMetricsRule;
use App\Models\RepairOrder;

class MaintenanceBreakdownService
{
    protected ?int $email_tenant_id;

    public function __construct(?int $email_tenant_id = null)
    {
        $this->email_tenant_id = $email_tenant_id;
    }

    /**
     * Reusable WO filter (FIXES YOUR BUG GLOBALLY)
     */
    private function applyValidWorkOrderFilters($query)
    {
        return $query
            ->leftJoin('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->where(function ($q) {
                $q->where('wo_statuses.name', '!=', 'Canceled')
                    ->orWhereNull('repair_orders.wo_status_id');
            })
            ->where(function ($q) {
                $q->whereRaw('LOWER(repair_orders.wo_number) != ?', ['not expected'])
                    ->orWhereNull('repair_orders.wo_number');
            });
    }

    public function getTotalRepairOrders(Carbon $startDate, Carbon $endDate): int
    {
        $query = DB::table('repair_orders')
            ->whereBetween('qs_invoice_date', [$startDate, $endDate]);

        $this->applyValidWorkOrderFilters($query);
        $this->applyTenantFilter($query);

        return $query->count();
    }

    public function getTotalInvoiceAmount(Carbon $startDate, Carbon $endDate): float
    {
        $query = DB::table('repair_orders')
            ->whereBetween('qs_invoice_date', [$startDate, $endDate]);

        $this->applyValidWorkOrderFilters($query);
        $this->applyTenantFilter($query);

        return $query->sum('invoice_amount') ?? 0;
    }

    public function getQSInvoiceAmount(Carbon $startDate, Carbon $endDate): float
    {
        $query = DB::table('repair_orders')
            ->where('on_qs', 'yes')
            ->whereBetween('qs_invoice_date', [$startDate, $endDate]);

        $this->applyValidWorkOrderFilters($query);
        $this->applyTenantFilter($query);

        return $query->sum('invoice_amount') ?? 0;
    }

    public function getMissingInvoicesCount(): int
    {
        $query = DB::table('repair_orders')
            ->where('on_qs', 'no')
            ->where('invoice_received', false);

        $this->applyValidWorkOrderFilters($query);
        $this->applyTenantFilter($query);

        return $query->count();
    }

    public function getTotalMiles(Carbon $startDate, Carbon $endDate, $dateFilter = null): float
    {
        if ($dateFilter === 'yesterday') {
            return 0;
        }

        $query = DB::table('miles_driven')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('week_start_date', [$startDate, $endDate])
                    ->orWhereBetween('week_end_date', [$startDate, $endDate]);
            })
            ->selectRaw('SUM(miles) as total_miles');

        $this->applyTenantFilter($query);

        $result = $query->first();

        return $result ? (float) $result->total_miles : 0;
    }

    public function getAreasOfConcern(Carbon $startDate, Carbon $endDate)
    {
        $query = DB::table('area_of_concerns')
            ->select(
                'area_of_concerns.id',
                'area_of_concerns.concern',
                DB::raw('COUNT(area_of_concern_repair_order.repair_order_id) as count')
            )
            ->leftJoin('area_of_concern_repair_order', 'area_of_concerns.id', '=', 'area_of_concern_repair_order.area_of_concern_id')
            ->leftJoin('repair_orders', 'area_of_concern_repair_order.repair_order_id', '=', 'repair_orders.id')
            ->whereBetween('repair_orders.qs_invoice_date', [$startDate, $endDate]);

        $this->applyValidWorkOrderFilters($query);

        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('repair_orders.tenant_id', Auth::user()->tenant_id);
        }

        return $query
            ->groupBy('area_of_concerns.id', 'area_of_concerns.concern')
            ->orderBy('count', 'desc')
            ->get();
    }

    public function getWorkOrdersByTruck(Carbon $startDate, Carbon $endDate)
    {
        $query = DB::table('repair_orders')
            ->select('trucks.truckid', DB::raw('COUNT(repair_orders.id) as work_order_count'))
            ->join('trucks', 'repair_orders.truck_id', '=', 'trucks.id')
            ->whereBetween('repair_orders.qs_invoice_date', [$startDate, $endDate]);

        $this->applyValidWorkOrderFilters($query);

        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('repair_orders.tenant_id', Auth::user()->tenant_id);
        }

        return $query
            ->groupBy('trucks.truckid')
            ->orderByDesc('work_order_count')
            ->get();
    }

    public function calculateQSMVtS(float $qsInvoiceAmount, float $totalMiles, int $tenantId): float
    {
        if ($totalMiles <= 0)
            return 0.0;

        $rule = TenantMetricsRule::where('tenant_id', $tenantId)->first();
        $divisor = $rule->mvts_divisor ?? 0.135;

        return ($qsInvoiceAmount / $totalMiles) / $divisor;
    }

    public function calculateCPMMetrics(float $totalInvoiceAmount, float $qsInvoiceAmount, float $totalMiles): array
    {
        return [
            'cpm' => $totalMiles > 0 ? $totalInvoiceAmount / $totalMiles : 0,
            'qs_cpm' => $totalMiles > 0 ? $qsInvoiceAmount / $totalMiles : 0
        ];
    }

    public function getOutstandingInvoices($minInvoiceAmount = null, $outstandingDate = null): ?array
    {
        $query = DB::table('repair_orders')
            ->join('vendors', 'repair_orders.vendor_id', '=', 'vendors.id')
            ->select(
                'repair_orders.ro_number',
                'repair_orders.invoice_amount',
                'vendors.vendor_name',
                'repair_orders.ro_open_date'
            )
            ->where('repair_orders.on_qs', 'no');

        $this->applyValidWorkOrderFilters($query);

        if ($minInvoiceAmount !== null) {
            $query->where('repair_orders.invoice_amount', '>=', $minInvoiceAmount);
        }

        if ($outstandingDate !== null) {
            $query->where('repair_orders.ro_close_date', '>', $outstandingDate);
        }

        $this->applyTenantFilter($query);

        return $query->get()->toArray();
    }

    public function getNotExpectedQSInvoices(): array
    {
        $query = DB::table('repair_orders')
            ->join('vendors', 'repair_orders.vendor_id', '=', 'vendors.id')
            ->where('repair_orders.on_qs', 'yes')
            ->where(function ($q) {
                $q->whereRaw('LOWER(repair_orders.wo_number) = ?', ['not expected'])
                    ->orWhereNull('repair_orders.wo_number');
            });

        $this->applyTenantFilter($query);

        return $query->get()->toArray();
    }
    public function getCanceledQSInvoices(): array
    {
        $query = RepairOrder::query()
            ->with([
                'truck',
                'vendor' => function ($q) {
                    $q->withTrashed();
                },
                'areasOfConcern' => function ($q) {
                    $q->withTrashed();
                },
                'woStatus',
                'tenant',
            ])
            ->whereHas('woStatus', function ($q) {
                $q->where('name', 'Canceled');
            })
            ->where('on_qs', 'yes');

        // Tenant filtering (if user belongs to a tenant)
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        $results = $query
            ->orderByDesc('ro_open_date')
            ->get();

        $formattedResults = $results->map(function (RepairOrder $ro) {
            // Same "MilesDriven.vue" week calculation logic you referenced
            // Use ro_open_date directly (more accurate than DB WEEK/YEAR)
            $date = $ro->ro_open_date ? Carbon::parse($ro->ro_open_date)->toDateString() : null;

            $year = null;
            $weekNumber = null;

            if ($date) {
                $tmp = Carbon::parse($date);
                $tmp->addDays(4 - ($tmp->dayOfWeek ?: 7));
                $year = $tmp->year;

                $weekNumber = (int) (ceil(
                    ((($tmp->timestamp - Carbon::create($year, 1, 1)->timestamp) / 86400) + 1) / 7
                ) + 1);
            }

            // Convert model to array INCLUDING loaded relations
            $row = $ro->toArray();

            // Add "flat" helpers your dialog/table uses
            $row['vendor_name'] = $ro->vendor?->vendor_name;
            $row['wo_status_name'] = $ro->woStatus?->name;
            $row['truck_number'] = $ro->truck?->truckid;

            // Keep computed week/year like before
            $row['year'] = $year;
            $row['week_number'] = $weekNumber;

            return $row;
        });

        return $formattedResults->toArray();
    }
    public function applyTenantFilter($query)
    {
        if ($this->email_tenant_id !== null) {
            return $query->where('tenant_id', $this->email_tenant_id);
        }

        if (Auth::check() && Auth::user()->tenant_id !== null) {
            return $query->where('tenant_id', Auth::user()->tenant_id);
        }

        return $query;
    }

    public function getMaintenanceBreakdown(Carbon $startDate, Carbon $endDate, $minInvoiceAmount = null, $outstandingDate = null): array
    {
        $totalRepairOrders = $this->getTotalRepairOrders($startDate, $endDate);
        $totalInvoiceAmount = $this->getTotalInvoiceAmount($startDate, $endDate);
        $qsInvoiceAmount = $this->getQSInvoiceAmount($startDate, $endDate);
        $missingInvoicesCount = $this->getMissingInvoicesCount();
        $totalMiles = $this->getTotalMiles($startDate, $endDate);
        $areasOfConcern = $this->getAreasOfConcern($startDate, $endDate);
        $workOrdersByTruck = $this->getWorkOrdersByTruck($startDate, $endDate);
        $cpmMetrics = $this->calculateCPMMetrics($totalInvoiceAmount, $qsInvoiceAmount, $totalMiles);
        $qsMVtS = $this->calculateQSMVtS($qsInvoiceAmount, $totalMiles, Auth::user()->tenant_id);

        // Get outstanding invoices data
        $outstandingInvoices = $this->getOutstandingInvoices($minInvoiceAmount, $outstandingDate);

        // Get canceled QS invoices that need attention
        $canceledQSInvoices = $this->getCanceledQSInvoices();

        // Get "not expected" QS invoices that need attention
        $notExpectedQSInvoices = $this->getNotExpectedQSInvoices();

        // Combine canceled and "not expected" QS invoices
        $combinedQSInvoices = array_merge($canceledQSInvoices, $notExpectedQSInvoices);

        return [
            'total_repair_orders' => $totalRepairOrders,
            'total_invoice_amount' => $totalInvoiceAmount,
            'qs_invoice_amount' => $qsInvoiceAmount,
            'missing_invoices_count' => $missingInvoicesCount,
            'total_miles' => $totalMiles,
            'cpm' => $cpmMetrics['cpm'],
            'qs_cpm' => $cpmMetrics['qs_cpm'],
            'qs_MVtS' => $qsMVtS,
            'areas_of_concern' => $areasOfConcern,
            'work_orders_by_truck' => $workOrdersByTruck,
            'outstanding_invoices' => $outstandingInvoices,
            'canceled_qs_invoices' => $combinedQSInvoices,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}