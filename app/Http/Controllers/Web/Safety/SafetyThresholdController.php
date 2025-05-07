<?php

namespace App\Http\Controllers\Web\Safety;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Safety\SafetyThresholdService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
/**
 * Class SafetyThresholdController
 *
 * This controller allows admin users to view and update safety thresholds.
 * It uses the SafetyThresholdService for business logic.
 *
 * Command:
 *   php artisan make:controller Web/Safety/SafetyThresholdController
 */
class SafetyThresholdController extends Controller
{
    protected SafetyThresholdService $safetyThresholdService;

    /**
     * Constructor.
     *
     * @param SafetyThresholdService $safetyThresholdService Service for safety thresholds.
     */
    public function __construct(SafetyThresholdService $safetyThresholdService)
    {
        $this->safetyThresholdService = $safetyThresholdService;
    }

    /**
     * Show the safety thresholds.
     *
     * @return \Inertia\Response
     */
    public function edit()
    {
        $thresholds = $this->safetyThresholdService->getAllThresholds();
        $metrics = $this->safetyThresholdService->getAvailableSafetyMetrics();
        $tenantSlug = Auth::user()->tenant->slug;
        return Inertia::render('SafetyThresholds/Admin', [
            'thresholds' => $thresholds,
            'metrics' => $metrics,
            'tenantSlug' => $tenantSlug,
        ]);
    }

    /**
     * Update safety thresholds.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'metric_name' => 'required|string',
            'good_threshold' => 'nullable|numeric',
            'bad_threshold' => 'nullable|numeric',
            'good_enabled' => 'boolean',
            'bad_enabled' => 'boolean',
        ]);
        
        $this->safetyThresholdService->createOrUpdateThreshold($data);
        return back()->with('success', 'Safety threshold updated successfully.');
    }

    /**
     * Delete a safety threshold.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->safetyThresholdService->deleteThreshold($id);
        return back()->with('success', 'Safety threshold deleted successfully.');
    }
}