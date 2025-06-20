<?php

namespace App\Http\Controllers\Web\SMSCoaching;

use App\Http\Requests\SMSCoaching\SMSScoresThresholdsRequest;
use App\Services\SMSCoaching\SMSScoresThresholdsService;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class SMSScoresThresholdsController extends Controller
{
    protected $service;

    public function __construct(SMSScoresThresholdsService $service)
    {
        $this->service = $service;
    }

    public function edit()
    {
        $data = $this->service->getForTenant();

        return Inertia::render('settings/SMSCoachingThresholds', [
            ...$data
        ]);
    }

    public function update(SMSScoresThresholdsRequest $request)
    {
        $this->service->storeOrUpdate($request->validated());

        return redirect()->back()->with('success', 'Thresholds updated successfully.');
    }
}
