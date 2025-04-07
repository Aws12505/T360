<?php

namespace App\Services\Summaries;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MilesDriven;
use App\Models\RepairOrder;

class MaintenanceBreakdownService
{
    /**
     * Get maintenance breakdown data for the specified date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getMaintenanceBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        // Get total repair orders count
        $repairOrdersCount = DB::table('repair_orders')
            ->whereBetween('ro_open_date', [$startDate, $endDate]);
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $repairOrdersCount->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $totalRepairOrders = $repairOrdersCount->count();
        
        
        // Get sum of invoice_amount where wo_number isn't null
        $totalInvoiceAmountQuery = DB::table('repair_orders')
            ->whereNotNull('wo_number')
            ->whereBetween('ro_open_date', [$startDate, $endDate]);
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $totalInvoiceAmountQuery->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $totalInvoiceAmount = $totalInvoiceAmountQuery->sum('invoice_amount') ?? 0;
        
        
        // Get sum of invoice_amount where wo_number isn't null and on_qs is true
        $qsInvoiceAmountQuery = DB::table('repair_orders')
            ->where('on_qs', true)
            ->whereBetween('qs_invoice_date', [$startDate, $endDate]);
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $qsInvoiceAmountQuery->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $qsInvoiceAmount = $qsInvoiceAmountQuery->sum('invoice_amount') ?? 0;
        
        // Get total miles driven
        $milesQuery = DB::table('miles_driven')
            ->whereBetween('week_start_date', [$startDate, $endDate]);
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $milesQuery->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $totalMiles = $milesQuery->sum('miles') ?? 0;
        
        // Get areas of concern breakdown
        $areasOfConcernQuery = DB::table('area_of_concerns')
            ->select('area_of_concerns.id', 'area_of_concerns.concern', DB::raw('COUNT(area_of_concern_repair_order.repair_order_id) as count'))
            ->leftJoin('area_of_concern_repair_order', 'area_of_concerns.id', '=', 'area_of_concern_repair_order.area_of_concern_id')
            ->leftJoin('repair_orders', 'area_of_concern_repair_order.repair_order_id', '=', 'repair_orders.id')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('repair_orders.ro_open_date', [$startDate, $endDate])
                      ->orWhereNull('repair_orders.ro_open_date');
            });
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $areasOfConcernQuery->where(function($query) {
                $query->where('repair_orders.tenant_id', Auth::user()->tenant_id)
                      ->orWhereNull('repair_orders.tenant_id');
            });
        }
        
        $areasOfConcern = $areasOfConcernQuery->groupBy('area_of_concerns.id', 'area_of_concerns.concern')
            ->orderBy('count', 'desc')
            ->get();
        
        // Get work orders by truck
        $workOrdersByTruckQuery = DB::table('repair_orders')
            ->select('trucks.truckid', DB::raw('COUNT(repair_orders.id) as work_order_count'))
            ->join('trucks', 'repair_orders.truck_id', '=', 'trucks.id')
            ->whereNotNull('repair_orders.wo_number')
            ->where('repair_orders.wo_status', '!=', 'Canceled')
            ->whereBetween('repair_orders.ro_open_date', [$startDate, $endDate])
            ->groupBy('trucks.truckid')
            ->orderBy('work_order_count', 'desc');
            
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $workOrdersByTruckQuery->where('repair_orders.tenant_id', Auth::user()->tenant_id);
        }
        
        $workOrdersByTruck = $workOrdersByTruckQuery->get();
        
        // Calculate CPM (Cost Per Mile) metrics
        $cpm = $totalMiles > 0 ? $totalInvoiceAmount / $totalMiles : 0;
        $qsCpm = $totalMiles > 0 ? $qsInvoiceAmount / $totalMiles : 0;
        $qsMVtS = $totalMiles > 0 ? ($qsInvoiceAmount / $totalMiles) / 0.135 : 0;
        
        return [
            'total_repair_orders' => $totalRepairOrders,
            'total_invoice_amount' => $totalInvoiceAmount,
            'qs_invoice_amount' => $qsInvoiceAmount,
            'total_miles' => $totalMiles,
            'cpm' => $cpm,
            'qs_cpm' => $qsCpm,
            'qs_MVtS' => $qsMVtS,
            'areas_of_concern' => $areasOfConcern,
            'work_orders_by_truck' => $workOrdersByTruck
        ];
    }
}