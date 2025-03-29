<?php

namespace App\Http\Controllers\Web\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Performance\PerformanceMetricRuleService;
use Inertia\Inertia;

/**
 * Class PerformanceMetricRuleController
 *
 * This controller allows admin users to view and update global performance metric rules.
 * It uses the PerformanceService for business logic.
 *
 * Command:
 *   php artisan make:controller Web/PerformanceMetricRuleController
 */
class PerformanceMetricRuleController extends Controller
{
    protected PerformanceMetricRuleService $performanceMetricRuleService;

    /**
     * Constructor.
     *
     * @param PerformanceMetricRuleService $performanceMetricRuleService Service for performance rules.
     */
    public function __construct(PerformanceMetricRuleService $performanceMetricRuleService)
    {
        $this->performanceMetricRuleService = $performanceMetricRuleService;
    }

    /**
     * Show the global performance metrics.
     *
     * @return \Inertia\Response
     */
    public function editGlobal()
    {
        $metrics = $this->performanceMetricRuleService->getGlobalMetrics();
        return Inertia::render('PerformanceRules/Admin', ['metrics' => $metrics]);
    }

    /**
     * Update global performance metrics.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGlobal(Request $request)
    {
        $data = $request->validate($this->performanceMetricRuleService->getMetricValidationRules());
        $this->performanceMetricRuleService->updateGlobalMetrics($data);
        return back()->with('success', 'Global performance metrics updated.');
    }
}
