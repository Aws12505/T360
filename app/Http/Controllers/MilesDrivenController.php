<?php

namespace App\Http\Controllers;

use App\Http\Requests\MilesDriven\StoreMilesDrivenRequest;
use App\Http\Requests\MilesDriven\UpdateMilesDrivenRequest;
use App\Models\MilesDriven;
use App\Services\MilesDriven\MilesDrivenService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MilesDrivenController extends Controller
{
    /**
     * The miles driven service instance.
     *
     * @var \App\Services\MilesDriven\MilesDrivenService
     */
    protected $milesDrivenService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\MilesDriven\MilesDrivenService $milesDrivenService
     * @return void
     */
    public function __construct(MilesDrivenService $milesDrivenService)
    {
        $this->milesDrivenService = $milesDrivenService;
    }

    /**
     * Display a listing of the miles driven.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $data = $this->milesDrivenService->getMilesDrivenIndex();
        return Inertia::render('MilesDriven/MilesDriven', $data);
    }

    /**
     * Store a newly created miles driven record.
     *
     * @param \App\Http\Requests\MilesDriven\StoreMilesDrivenRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreMilesDrivenRequest $request)
    {
        $this->milesDrivenService->createMilesDriven($request->validated());
        return redirect()->back()->with('success', 'Miles driven record created successfully.');
    }

    /**
     * Update the specified miles driven record.
     *
     * @param \App\Http\Requests\MilesDriven\UpdateMilesDrivenRequest $request
     * @param string $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateMilesDrivenRequest $request, $tenantSlug, $milesDriven)
    {
        $this->milesDrivenService->updateMilesDriven($milesDriven, $request->validated());
        return redirect()->back()->with('success', 'Miles driven record updated successfully.');
    }

    /**
     * Remove the specified miles driven record.
     *
     * @param string $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($tenantSlug, $milesDriven)
    {
        $this->milesDrivenService->deleteMilesDriven($milesDriven);
        return redirect()->back()->with('success', 'Miles driven record deleted successfully.');
    }

    /**
     * Update the specified miles driven record for admin.
     *
     * @param \App\Http\Requests\MilesDriven\UpdateMilesDrivenRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdmin(UpdateMilesDrivenRequest $request, $milesDriven)
    {
        $this->milesDrivenService->updateMilesDriven($milesDriven, $request->validated());
        return redirect()->back()->with('success', 'Miles driven record updated successfully.');
    }

    /**
     * Remove the specified miles driven record for admin.
     *
     * @param \App\Models\MilesDriven $milesDriven
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAdmin( $milesDriven)
    {
        $this->milesDrivenService->deleteMilesDriven($milesDriven);
        return redirect()->back()->with('success', 'Miles driven record deleted successfully.');
    }
}