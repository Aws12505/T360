<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PerformanceService;
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
    protected PerformanceService $performanceService;

    /**
     * Constructor.
     *
     * @param PerformanceService $performanceService Service for performance rules.
     */
    public function __construct(PerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Show the global performance metrics.
     *
     * @return \Inertia\Response
     */
    public function editGlobal()
    {
        $metrics = $this->performanceService->getGlobalMetrics();
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
        $data = $request->validate($this->performanceService->getMetricValidationRules());
        $this->performanceService->updateGlobalMetrics($data);
        return back()->with('success', 'Global performance metrics updated.');
    }
}
