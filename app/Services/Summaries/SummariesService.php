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

    public function __construct(
        PerformanceDataService $performanceDataService,
        SafetyDataService $safetyDataService,
        DelayBreakdownService $delayBreakdownService,
        RejectionBreakdownService $rejectionBreakdownService
    ) {
        $this->performanceDataService    = $performanceDataService;
        $this->safetyDataService         = $safetyDataService;
        $this->delayBreakdownService     = $delayBreakdownService;
        $this->rejectionBreakdownService = $rejectionBreakdownService;
    }

    public function compileSummaries(): array
    {
        $today            = Carbon::now();
        $currentWeekStart = $today->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd   = $today->copy()->endOfWeek(Carbon::SATURDAY);
        $rollingStart     = $currentWeekStart->copy()->subWeeks(5);
        $rollingEnd       = $currentWeekEnd;

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
                )
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
                )
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
                )
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
        ];
    }
}
