<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PerformanceService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Class SummariesController
 *
 * This controller compiles performance summaries over various time periods.
 * It delegates summary calculations to the PerformanceService.
 *
 * Command:
 *   php artisan make:controller Web/SummariesController
 */
class SummariesController extends Controller
{
    protected PerformanceService $performanceService;

    /**
     * Constructor.
     *
     * @param PerformanceService $performanceService Service for performance summary calculations.
     */
    public function __construct(PerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Get performance summaries.
     *
     * @return \Inertia\Response
     */
    public function getSummaries()
    {
        $data = $this->performanceService->compileSummaries();
        return Inertia::render('Performance/Summary', $data);
    }
}
