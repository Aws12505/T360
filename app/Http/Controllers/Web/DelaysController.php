<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DelayService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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

    /**
     * Constructor.
     *
     * @param DelayService $delayService Service for delay operations.
     */
    public function __construct(DelayService $delayService)
    {
        $this->delayService = $delayService;
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
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        
        $this->delayService->createDelay($request->validate([
            'date' => 'required|date',
            'driver_name' => 'required|string',
            'delay_type' => 'required|in:origin,destination',
            'delay_category' => 'required|in:1_120,121_600,601_plus',
            'delay_code_id' => 'required|exists:delay_codes,id',
            'disputed' => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
            'tenant_id' => 'sometimes|required|exists:tenants,id',
        ]));
        return back();
    }

    /**
     * Update an existing delay record.
     *
     * @param Request $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $tenantSlug, $id)
    {
        
        $this->delayService->updateDelay($id, $request->validate([
            'date' => 'required|date',
            'driver_name' => 'required|string',
            'delay_type' => 'required|in:origin,destination',
            'delay_category' => 'required|in:1_120,121_600,601_plus',
            'delay_code_id' => 'required|exists:delay_codes,id',
            'disputed' => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
            'tenant_id' => 'sometimes|required|exists:tenants,id',
        ]));
        return back();
    }

    /**
     * Update a delay record as Admin.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdmin(Request $request, $id)
    {
        
        $this->delayService->updateDelay($id, $request->validate([
            'date' => 'required|date',
            'driver_name' => 'required|string',
            'delay_type' => 'required|in:origin,destination',
            'delay_category' => 'required|in:1_120,121_600,601_plus',
            'delay_code_id' => 'required|exists:delay_codes,id',
            'disputed' => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
            'tenant_id' => 'sometimes|required|exists:tenants,id',
        ]));
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
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCode(Request $request)
    {
        $this->delayService->createDelayCode($request->validate([
            'code' => 'required|string|unique:delay_codes,code',
        ]));
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
        $this->delayService->deleteDelayCode($id);
        return back();
    }
}
