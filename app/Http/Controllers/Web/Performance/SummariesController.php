<?php

namespace App\Http\Controllers\Web\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Performance\SummariesService;
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
    protected SummariesService $summariesService;

    /**
     * Constructor.
     *
     * @param SummariesService $summariesService Service for performance summary calculations.
     */
    public function __construct(SummariesService $summariesService)
    {
        $this->summariesService = $summariesService;
    }

    /**
     * Get performance summaries.
     *
     * @return \Inertia\Response
     */
    public function getSummaries()
    {
        $data = $this->summariesService->compileSummaries();
        return Inertia::render('Performance/Summary', $data);
    }
}
