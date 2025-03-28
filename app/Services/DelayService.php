<?php

namespace App\Services;

use App\Models\Delay;
use App\Models\DelayCode;
use Illuminate\Support\Facades\Auth;

/**
 * Class DelayService
 *
 * Contains business logic for managing delay records and delay codes.
 *
 * Created manually: touch app/Services/DelayService.php
 */
class DelayService
{
    /**
     * Get delay data for the index view.
     *
     * @return array
     */
    public function getDelaysIndex(): array
    {
        $delays = Delay::with(['tenant', 'delayCode'])->get();
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? \App\Models\Tenant::all() : [];
        $delayCodes = DelayCode::all();
        return [
            'delays'     => $delays,
            'tenantSlug' => $tenantSlug,
            'isSuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
            'delay_codes' => $delayCodes,
        ];
    }

    /**
     * Create a new delay record.
     *
     * @param array $data
     * @return void
     */
    public function createDelay(array $data)
    {
        $user = Auth::user();
        $data['tenant_id'] = is_null($user->tenant_id) ? $data['tenant_id'] : $user->tenant_id;
        $data['penalty'] = match ($data['delay_category']) {
            '1_120'   => 1,
            '121_600' => 2,
            '601_plus'=> 4,
        };
        Delay::create($data);
    }

    /**
     * Update an existing delay record.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateDelay($id, array $data)
    {
        $user = Auth::user();
        $data['tenant_id'] = is_null($user->tenant_id) ? $data['tenant_id'] : $user->tenant_id;
        $data['penalty'] = match ($data['delay_category']) {
            '1_120'   => 1,
            '121_600' => 2,
            '601_plus'=> 4,
        };
        $delay = Delay::findOrFail($id);
        $delay->update($data);
    }

    /**
     * Delete a delay record.
     *
     * @param int $id
     * @return void
     */
    public function deleteDelay($id)
    {
        $delay = Delay::findOrFail($id);
        $delay->delete();
    }

    /**
     * Create a new delay code.
     *
     * @param array $data
     * @return void
     */
    public function createDelayCode(array $data)
    {
        DelayCode::create($data);
    }

    /**
     * Delete a delay code.
     *
     * @param int $id
     * @return void
     */
    public function deleteDelayCode($id)
    {
        $code = DelayCode::findOrFail($id);
        $code->delete();
    }
}
