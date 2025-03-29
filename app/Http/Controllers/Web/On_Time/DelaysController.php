<?php

namespace App\Http\Controllers\Web\On_Time;

use App\Http\Controllers\Controller;
use App\Services\On_Time\DelayCodesService;
use App\Services\On_Time\DelayService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Requests\StoreDelayRequest;
use App\Http\Requests\UpdateDelayRequest;
use App\Http\Requests\StoreDelayCode;

/**
 * Class DelaysController
 *
 * This controller manages delay records and delay code operations.
 * It delegates business logic to the DelayService.
 *
 * Command:
 *   php artisan make:controller Web/DelaysController
 */
class DelaysController extends Controller
{
    protected DelayService $delayService;
    protected DelayCodesService $delayCodesService;

    /**
     * Constructor.
     *
     * @param DelayService $delayService Service for delay operations.
     * @param DelayCodesService $delayCodesService Service for delay code operations.
     */
    public function __construct(DelayService $delayService, DelayCodesService $delayCodesService)
    {
        $this->delayService = $delayService;
        $this->delayCodesService = $delayCodesService;
    }

    /**
     * Display a list of delays.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $data = $this->delayService->getDelaysIndex();
        return Inertia::render('Delays/Index', $data);
    }

    /**
     * Create a new delay record.
     *
     * @param \App\Http\Requests\StoreDelayRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreDelayRequest $request)
    {
        // The request is already validated, so we directly pass the validated data to the service
        $this->delayService->createDelay($request->validated());

        return back();
    }

    /**
     * Update an existing delay record.
     *
     * @param \App\Http\Requests\UpdateDelayRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateDelayRequest $request, $tenantSlug, $id)
    {
        // The request is already validated, so we directly pass the validated data to the service
        $this->delayService->updateDelay($id, $request->validated());

        return back();
    }

    /**
     * Update a delay record as Admin.
     *
     * @param \App\Http\Requests\UpdateDelayRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdmin(UpdateDelayRequest $request, $id)
    {
        // The request is already validated, so we directly pass the validated data to the service
        $this->delayService->updateDelay($id, $request->validated());

        return back();
    }

    /**
     * Delete a delay record.
     *
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($tenantSlug, $id)
    {
        $this->delayService->deleteDelay($id);
        return back();
    }

    /**
     * Delete a delay record as Admin.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAdmin($id)
    {
        $this->delayService->deleteDelay($id);
        return back();
    }

    /**
     * Create a new delay code.
     *
     * @param \App\Http\Requests\StoreDelayCode $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCode(StoreDelayCode $request)
    {
        // The request is already validated, so we directly pass the validated data to the service
        $this->delayCodesService->createDelayCode($request->validated());

        return back();
    }

    /**
     * Delete a delay code.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCode($id)
    {
        $this->delayCodesService->deleteDelayCode($id);
        return back();
    }
}
