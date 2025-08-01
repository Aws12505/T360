<?php

namespace App\Services\Summaries;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TenantMetricsRule;
use App\Models\WoStatus;

class MaintenanceBreakdownService
{

    protected ?int $email_tenant_id;

public function __construct(?int $email_tenant_id = null) {
    $this->email_tenant_id = $email_tenant_id;
}
    /**
     * Get total repair orders count for the date range
     */
    public function getTotalRepairOrders(Carbon $startDate, Carbon $endDate): int
    {
        $query = DB::table('repair_orders')
            ->whereBetween('ro_open_date', [$startDate, $endDate])
            ->leftJoin('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->where(function($query) {
                $query->where('wo_statuses.name', '!=', 'Canceled');
            })
            ->whereRaw('LOWER(wo_number) != ?', ['not expected']);
            
        $this->applyTenantFilter($query);

        return $query->count();
    }

    /**
     * Get total invoice amount for the date range
     */
    public function getTotalInvoiceAmount(Carbon $startDate, Carbon $endDate): float
    {
        $query = DB::table('repair_orders')
            ->whereBetween('ro_open_date', [$startDate, $endDate])
            ->leftJoin('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->where(function($query) {
                $query->where('wo_statuses.name', '!=', 'Canceled');
            })->whereRaw('LOWER(wo_number) != ?', ['not expected']);
            
        $this->applyTenantFilter($query);
        
        return $query->sum('invoice_amount') ?? 0;
    }

    /**
     * Get QS invoice amount for the date range
     */
    public function getQSInvoiceAmount(Carbon $startDate, Carbon $endDate): float
    {
        $query = DB::table('repair_orders')
            ->where('on_qs', 'yes')
            ->whereBetween('qs_invoice_date', [$startDate, $endDate])
            ->leftJoin('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->whereRaw('LOWER(wo_number) != ?', ['not expected']);
            
        $this->applyTenantFilter($query);
        
        return $query->sum('invoice_amount') ?? 0;
    }

    /**
     * Get count of missing invoices
     */
    public function getMissingInvoicesCount(): int
    {
        $query = DB::table('repair_orders')
            ->where('on_qs', 'no')
            ->where('invoice_received', false)
            ->leftJoin('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->where(function($query) {
                $query->where('wo_statuses.name', '!=', 'Canceled');
            })->whereRaw('LOWER(wo_number) != ?', ['not expected']);
            
        $this->applyTenantFilter($query);
        
        return $query->count();
    }

    /**
     * Get total miles driven for the date range
     */
    public function getTotalMiles(Carbon $startDate, Carbon $endDate,$dateFilter = null): float
    {
        
    // Skip calculation for yesterday timeframe
    if ($dateFilter === 'yesterday') {
        return 0;
    }

    // Ensure dates are Carbon instances
    if (!($startDate instanceof Carbon)) {
        $startDate = Carbon::parse($startDate);
    }

    if (!($endDate instanceof Carbon)) {
        $endDate = Carbon::parse($endDate);
    }

    // Query to get sum of miles driven within the date range
    $query = DB::table('miles_driven')
        ->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('week_start_date', [$startDate, $endDate])
              ->orWhereBetween('week_end_date', [$startDate, $endDate]);
        })
        ->selectRaw('SUM(miles) as total_miles');

    // Apply tenant filter if user is authenticated
    $this->applyTenantFilter($query);

    // Get the result safely
    $result = $query->first();

    return $result ? (float) $result->total_miles : 0;
    }

    /**
     * Get areas of concern breakdown
     */
    public function getAreasOfConcern(Carbon $startDate, Carbon $endDate)
    {
        $query = DB::table('area_of_concerns')
            ->select('area_of_concerns.id', 'area_of_concerns.concern', DB::raw('COUNT(area_of_concern_repair_order.repair_order_id) as count'))
            ->leftJoin('area_of_concern_repair_order', 'area_of_concerns.id', '=', 'area_of_concern_repair_order.area_of_concern_id')
            ->leftJoin('repair_orders', 'area_of_concern_repair_order.repair_order_id', '=', 'repair_orders.id')
            ->leftJoin('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('repair_orders.ro_open_date', [$startDate, $endDate])
                      ->orWhereNull('repair_orders.ro_open_date');
            })
            ->where(function($query) {
                $query->where('wo_statuses.name', '!=', 'Canceled');
            })->whereRaw('LOWER(repair_orders.wo_number) != ?', ['not expected']);
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where(function($query) {
                $query->where('repair_orders.tenant_id', Auth::user()->tenant_id)
                      ->orWhereNull('repair_orders.tenant_id');
            });
        }
        
        return $query->groupBy('area_of_concerns.id', 'area_of_concerns.concern')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get work orders by truck
     */
    public function getWorkOrdersByTruck(Carbon $startDate, Carbon $endDate)
    {
        $query = DB::table('repair_orders')
            ->select('trucks.truckid', DB::raw('COUNT(repair_orders.id) as work_order_count'))
            ->join('trucks', 'repair_orders.truck_id', '=', 'trucks.id')
            ->leftJoin('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->where(function($query) {
                $query->where('wo_statuses.name', '!=', 'Canceled');
            })
            ->whereBetween('repair_orders.ro_open_date', [$startDate, $endDate])
            ->whereRaw('LOWER(wo_number) != ?', ['not expected'])
            ->groupBy('trucks.truckid')
            ->orderBy('work_order_count', 'desc');
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('repair_orders.tenant_id', Auth::user()->tenant_id);
        }
        
        return $query->get();
    }

    /**
     * Calculate QS MVtS value
     */
    public function calculateQSMVtS(float $qsInvoiceAmount, float $totalMiles, int $tenantId): float
{
    if ($totalMiles <= 0) {
        return 0.0;
    }

    // Try to fetch this tenant’s rule
    $rule = TenantMetricsRule::where('tenant_id', $tenantId)->first();

    // Use the divisor from the rule if present, otherwise default to 0.135
    $mvtsDivisor = $rule->mvts_divisor ?? 0.135;
    // Calculate and return
    return ($qsInvoiceAmount / $totalMiles) / $mvtsDivisor;
}

    /**
     * Calculate CPM metrics
     */
    public function calculateCPMMetrics(float $totalInvoiceAmount, float $qsInvoiceAmount, float $totalMiles): array
    {
        $cpm = $totalMiles > 0 ? $totalInvoiceAmount / $totalMiles : 0;
        $qsCpm = $totalMiles > 0 ? $qsInvoiceAmount / $totalMiles : 0;

        return [
            'cpm' => $cpm,
            'qs_cpm' => $qsCpm
        ];
    }

    
    public function getOutstandingInvoices( $minInvoiceAmount = null, $outstandingDate = null): ?array
    {
        // If both required parameters are null, return null
        if ($minInvoiceAmount === null && $outstandingDate === null) {
            $minInvoiceAmount = 0;
            $outstandingDate = 1990-01-01;
        }

        $query = DB::table('repair_orders')
            ->select('repair_orders.ro_number', 'repair_orders.invoice_amount', 'vendors.vendor_name as vendor_name', 'repair_orders.ro_open_date as qs_invoice_date',
                    DB::raw('YEAR(repair_orders.ro_open_date) as year'), 
                    DB::raw('WEEK(repair_orders.ro_open_date) as week_number'))
            ->join('vendors', 'repair_orders.vendor_id', '=', 'vendors.id')
            ->where('repair_orders.on_qs', 'no')->whereRaw('LOWER(wo_number) != ?', ['not expected']);

        // Apply invoice amount filter if provided
        if ($minInvoiceAmount !== null) {
            $query->where('repair_orders.invoice_amount', '>=', $minInvoiceAmount);
        }

        // Apply outstanding date filter if provided
        if ($outstandingDate !== null) {
            $query->where('repair_orders.ro_close_date', '>', $outstandingDate);
        }

        // Apply tenant filter if authenticated
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('repair_orders.tenant_id', Auth::user()->tenant_id);
        }

        // Get the results
        $results = $query->get();

        // Calculate week number using the same logic as in MilesDriven.vue
        $formattedResults = $results->map(function($item) {
            // Convert the ro_open_date to a JavaScript-compatible date calculation
            $roOpenDate = new Carbon($item->year . '-01-01');
            $roOpenDate->addWeeks($item->week_number - 1);
            
            // Calculate the week number using the same logic as in MilesDriven.vue
            $date = $roOpenDate->toDateString();
            $tmp = new Carbon($date);
            $tmp->addDays(4 - ($tmp->dayOfWeek ?: 7));
            $year = $tmp->year;
            $weekNumber = ceil((($tmp->timestamp - Carbon::create($year, 1, 1)->timestamp) / 86400 + 1) / 7) + 1;

            return [
                'ro_number' => $item->ro_number,
                'invoice_amount' => $item->invoice_amount,
                'vendor_name' => $item->vendor_name,
                'year' => $year,
                'week_number' => $weekNumber,
                'qs_invoice_date' => $item->qs_invoice_date,
            ];
        });

        return $formattedResults->toArray();
    }

    /**
     * Get invoices that have WO status as canceled and on_qs is true
     * These need attention as they represent a problematic state
     */
    public function getCanceledQSInvoices(): array
    {
        $query = DB::table('repair_orders')
            ->select('repair_orders.ro_number', 'repair_orders.invoice_amount', 'vendors.vendor_name as vendor_name', 
                    'repair_orders.ro_open_date', 'repair_orders.ro_close_date',
                    DB::raw('YEAR(repair_orders.ro_open_date) as year'), 
                    DB::raw('WEEK(repair_orders.ro_open_date) as week_number'))
            ->join('vendors', 'repair_orders.vendor_id', '=', 'vendors.id')
            ->join('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->where('wo_statuses.name', '=', 'Canceled')
            ->where('repair_orders.on_qs', 'yes');
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('repair_orders.tenant_id', Auth::user()->tenant_id);
        }
        
        // Get the results
        $results = $query->get();
        
        // Format the results similar to outstanding invoices
        $formattedResults = $results->map(function($item) {
            // Convert the ro_open_date to a JavaScript-compatible date calculation
            $roOpenDate = new Carbon($item->year . '-01-01');
            $roOpenDate->addWeeks($item->week_number - 1);
            
            // Calculate the week number using the same logic as in MilesDriven.vue
            $date = $roOpenDate->toDateString();
            $tmp = new Carbon($date);
            $tmp->addDays(4 - ($tmp->dayOfWeek ?: 7));
            $year = $tmp->year;
            $weekNumber = ceil((($tmp->timestamp - Carbon::create($year, 1, 1)->timestamp) / 86400 + 1) / 7) + 1;

            return [
                'ro_number' => $item->ro_number,
                'invoice_amount' => $item->invoice_amount,
                'vendor_name' => $item->vendor_name,
                'ro_open_date' => $item->ro_open_date,
                'ro_close_date' => $item->ro_close_date,
                'year' => $year,
                'week_number' => $weekNumber
            ];
        });

        return $formattedResults->toArray();
    }

    /**
     * Get invoices that have "not expected" as wo_number and on_qs is yes
     * These need attention as they represent a problematic state
     */
    public function getNotExpectedQSInvoices(): array
    {
        $query = DB::table('repair_orders')
            ->select('repair_orders.ro_number', 'repair_orders.invoice_amount', 'vendors.vendor_name as vendor_name', 
                    'repair_orders.ro_open_date', 'repair_orders.ro_close_date',
                    DB::raw('YEAR(repair_orders.ro_open_date) as year'), 
                    DB::raw('WEEK(repair_orders.ro_open_date) as week_number'))
            ->join('vendors', 'repair_orders.vendor_id', '=', 'vendors.id')
            ->whereRaw('LOWER(repair_orders.wo_number) = ?', ['not expected'])
            ->where('repair_orders.on_qs', 'yes');
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('repair_orders.tenant_id', Auth::user()->tenant_id);
        }
        
        // Get the results
        $results = $query->get();
        
        // Format the results similar to outstanding invoices
        $formattedResults = $results->map(function($item) {
            // Convert the ro_open_date to a JavaScript-compatible date calculation
            $roOpenDate = new Carbon($item->year . '-01-01');
            $roOpenDate->addWeeks($item->week_number - 1);
            
            // Calculate the week number using the same logic as in MilesDriven.vue
            $date = $roOpenDate->toDateString();
            $tmp = new Carbon($date);
            $tmp->addDays(4 - ($tmp->dayOfWeek ?: 7));
            $year = $tmp->year;
            $weekNumber = ceil((($tmp->timestamp - Carbon::create($year, 1, 1)->timestamp) / 86400 + 1) / 7) + 1;

            return [
                'ro_number' => $item->ro_number,
                'invoice_amount' => $item->invoice_amount,
                'vendor_name' => $item->vendor_name,
                'ro_open_date' => $item->ro_open_date,
                'ro_close_date' => $item->ro_close_date,
                'year' => $year,
                'week_number' => $weekNumber
            ];
        });

        return $formattedResults->toArray();
    }

    /**
     * Apply tenant filter to query if user is authenticated
     */
    public function applyTenantFilter($query)
    {
        if ($this->email_tenant_id !== null) {
            $query->where('tenant_id', $this->email_tenant_id);
            return;
        }
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
    }
    /**
     * Get maintenance breakdown data for the specified date range.
     */
    public function getMaintenanceBreakdown(Carbon $startDate, Carbon $endDate, $minInvoiceAmount = null,  $outstandingDate = null): array
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