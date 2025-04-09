<?php

namespace App\Services\Summaries;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class SummariesService
{
    protected PerformanceDataService $performanceDataService;
    protected SafetyDataService $safetyDataService;
    protected DelayBreakdownService $delayBreakdownService;
    protected RejectionBreakdownService $rejectionBreakdownService;
    protected MaintenanceBreakdownService $maintenanceBreakdownService;

    public function __construct(
        PerformanceDataService $performanceDataService,
        SafetyDataService $safetyDataService,
        DelayBreakdownService $delayBreakdownService,
        RejectionBreakdownService $rejectionBreakdownService,
        MaintenanceBreakdownService $maintenanceBreakdownService
    ) {
        $this->performanceDataService    = $performanceDataService;
        $this->safetyDataService         = $safetyDataService;
        $this->delayBreakdownService     = $delayBreakdownService;
        $this->rejectionBreakdownService = $rejectionBreakdownService;
        $this->maintenanceBreakdownService = $maintenanceBreakdownService;
    }

    public function compileSummaries(): array
    {
        $today            = Carbon::now();
        $currentWeekStart = $today->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd   = $today->copy()->endOfWeek(Carbon::SATURDAY);
        $rollingStart     = $currentWeekStart->copy()->subWeeks(5);
        $rollingEnd       = $currentWeekEnd;
       // $rollingStart     = Carbon::parse('2025-02-16')->startOfDay();
        //$rollingEnd       = Carbon::parse('2025-03-29')->endOfDay();

        $summaries = [
            'yesterday' => [
                'performance' => $this->performanceDataService->getPerformanceData(
                    Carbon::yesterday()->startOfDay(),
                    Carbon::yesterday()->endOfDay(),
                    'yesterday'
                ),
                'safety'      => $this->safetyDataService->getSafetyData(
                    Carbon::yesterday()->startOfDay(),
                    Carbon::yesterday()->endOfDay()
                ),
                'date_range'  => [
                    'start' => Carbon::yesterday()->startOfDay()->format('Y-m-d'),
                    'end'   => Carbon::yesterday()->endOfDay()->format('Y-m-d')
                ]
            ],
            'current_week' => [
                'performance' => $this->performanceDataService->getPerformanceData(
                    $currentWeekStart->copy()->startOfDay(),
                    $currentWeekEnd->copy()->endOfDay(),
                    'current_week'
                ),
                'safety'      => $this->safetyDataService->getSafetyData(
                    $currentWeekStart->copy()->startOfDay(),
                    $currentWeekEnd->copy()->endOfDay()
                ),
                'date_range'  => [
                    'start' => $currentWeekStart->copy()->format('Y-m-d'),
                    'end'   => $currentWeekEnd->copy()->format('Y-m-d')
                ]
            ],
            'rolling_6_weeks' => [
                'performance' => $this->performanceDataService->getPerformanceData(
                    $rollingStart->copy()->startOfDay(),
                    $rollingEnd->copy()->endOfDay(),
                    'rolling_6_weeks'
                ),
                'safety'      => $this->safetyDataService->getSafetyData(
                    $rollingStart->copy()->startOfDay(),
                    $rollingEnd->copy()->endOfDay()
                ),
                'date_range'  => [
                    'start' => $rollingStart->copy()->format('Y-m-d'),
                    'end'   => $rollingEnd->copy()->format('Y-m-d')
                ]
            ],
            'quarterly' => [
                'performance' => $this->performanceDataService->getPerformanceData(
                    $today->copy()->subMonths(3)->startOfDay(),
                    $today->copy()->endOfDay(),
                    'quarterly'
                ),
                'safety'      => $this->safetyDataService->getSafetyData(
                    $today->copy()->subMonths(3)->startOfDay(),
                    $today->copy()->endOfDay()
                )
            ],
        ];

        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug   = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants      = $isSuperAdmin ? Tenant::all() : [];

        return [
            'summaries'           => $summaries,
            'tenantSlug'          => $tenantSlug,
            'SuperAdmin'          => $isSuperAdmin,
            'tenants'             => $tenants,
            'delayBreakdowns'     => [
                'yesterday'       => $this->delayBreakdownService->getDelayBreakdown(
                    Carbon::yesterday()->startOfDay(),
                    Carbon::yesterday()->endOfDay()
                ),
                'current_week'    => $this->delayBreakdownService->getDelayBreakdown(
                    $currentWeekStart->copy()->startOfDay(),
                    $currentWeekEnd->copy()->endOfDay()
                ),
                'rolling_6_weeks' => $this->delayBreakdownService->getDelayBreakdown(
                    $rollingStart->copy()->startOfDay(),
                    $rollingEnd->copy()->endOfDay()
                ),
                'quarterly'       => $this->delayBreakdownService->getDelayBreakdown(
                    $today->copy()->subMonths(3)->startOfDay(),
                    $today->copy()->endOfDay()
                ),
            ],
            'rejectionBreakdowns' => [
                'yesterday'       => $this->rejectionBreakdownService->getRejectionBreakdown(
                    Carbon::yesterday()->startOfDay(),
                    Carbon::yesterday()->endOfDay()
                ),
                'current_week'    => $this->rejectionBreakdownService->getRejectionBreakdown(
                    $currentWeekStart->copy()->startOfDay(),
                    $currentWeekEnd->copy()->endOfDay()
                ),
                'rolling_6_weeks' => $this->rejectionBreakdownService->getRejectionBreakdown(
                    $rollingStart->copy()->startOfDay(),
                    $rollingEnd->copy()->endOfDay()
                ),
                'quarterly'       => $this->rejectionBreakdownService->getRejectionBreakdown(
                    $today->copy()->subMonths(3)->startOfDay(),
                    $today->copy()->endOfDay()
                ),
            ],
            'maintenanceBreakdowns' => [
                'yesterday'       => $this->maintenanceBreakdownService->getMaintenanceBreakdown(
                    Carbon::yesterday()->startOfDay(),
                    Carbon::yesterday()->endOfDay()
                ),
                'current_week'    => $this->maintenanceBreakdownService->getMaintenanceBreakdown(
                    $currentWeekStart->copy()->startOfDay(),
                    $currentWeekEnd->copy()->endOfDay()
                ),
                'rolling_6_weeks' => $this->maintenanceBreakdownService->getMaintenanceBreakdown(
                    $rollingStart->copy()->startOfDay(),
                    $rollingEnd->copy()->endOfDay()
                ),
                'quarterly'       => $this->maintenanceBreakdownService->getMaintenanceBreakdown(
                    $today->copy()->subMonths(3)->startOfDay(),
                    $today->copy()->endOfDay()
                ),
            ],
        ];
    }
}
