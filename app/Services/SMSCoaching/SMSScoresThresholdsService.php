<?php

namespace App\Services\SMSCoaching;

use App\Models\SMSScoresThresholds;
use Illuminate\Support\Facades\Auth;

class SMSScoresThresholdsService
{
    public function storeOrUpdate(array $data): SMSScoresThresholds
    {
        $tenantId = Auth::user()->tenant_id;

        // Assume only one per tenant; update or create
        return SMSScoresThresholds::updateOrCreate(
            ['tenant_id' => $tenantId],
            $data
        );
    }

    public function getForTenant()
    {
        $thresholds = SMSScoresThresholds::first();
        $tenantSlug = Auth::user()->tenant->slug;
        $permissions = Auth::user()->getAllPermissions();

        return [
            'thresholds' => $thresholds,
            'tenantSlug' => $tenantSlug,
            'permissions' => $permissions,
        ]; 
    }
}
