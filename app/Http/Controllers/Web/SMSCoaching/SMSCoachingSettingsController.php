<?php

namespace App\Http\Controllers\Web\SMSCoaching;

use App\Http\Controllers\Controller;
use App\Models\SmsCoachingTenantSetting;
use App\Services\SMSCoaching\SmsCoachingMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SMSCoachingSettingsController extends Controller
{
    public function edit(string $tenantSlug): Response
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;
        $permissions = $user->getAllPermissions();

        $settingsRows = SmsCoachingTenantSetting::query()
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy('metric_key');

        $metrics = collect(SmsCoachingMetrics::all())
            ->map(fn($meta, $key) => array_merge(['key' => $key], $meta))
            ->values();

        $settings = [];
        foreach (SmsCoachingMetrics::keys() as $key) {
            $settings[$key] = $settingsRows[$key]->enabled ?? true;
        }

        return Inertia::render('settings/SMSCoachingSettings', [
            'tenantSlug' => $tenantSlug,
            'permissions' => $permissions,
            'metrics' => $metrics,
            'settings' => $settings,
        ]);
    }

    public function update(string $tenantSlug, Request $request): RedirectResponse
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['boolean'],
        ]);

        $settings = $data['settings'] ?? [];

        foreach (SmsCoachingMetrics::keys() as $key) {
            $enabled = (bool) ($settings[$key] ?? false);

            SmsCoachingTenantSetting::updateOrCreate([
                'tenant_id' => $tenantId,
                'metric_key' => $key,
            ], [
                'enabled' => $enabled,
            ]);
        }

        return redirect()->back()->with('success', 'SMS coaching settings updated.');
    }
}
