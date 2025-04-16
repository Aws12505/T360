<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\UpdateTenantRequest;
use App\Services\Tenants\TenantService;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class TenantSettingsController extends Controller
{
    protected TenantService $tenantService;

    /**
     * Constructor.
     *
     * @param TenantService $tenantService
     */
    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display the tenant settings page.
     *
     * @param string $tenantSlug
     * @return \Inertia\Response
     */
    public function edit($tenantSlug)
    {
        // Get the current tenant
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        
        // Ensure the user belongs to this tenant
        if (Auth::user()->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized action.');
        }

        // Get the tenant's subscription
        $subscription = Subscription::where('tenant_id', $tenant->id)->latest()->first();

        return Inertia::render('settings/TenantSettings', [
            'tenant' => $tenant,
            'tenantSlug' => $tenantSlug,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Update the tenant settings.
     *
     * @param UpdateTenantRequest $request
     * @param string $tenantSlug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateTenantRequest $request, $tenantSlug)
    {
        // Get the current tenant
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        // Ensure the user belongs to this tenant
        if (Auth::user()->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized action.');
        }
        // Get validated data from the form request
        $validated = $request->validated();
        // Handle file upload separately if present
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image');
        }
        // Store the original and new slug for comparison
        $originalSlug = $tenant->slug;
        $newSlug = $validated['slug'] ?? $originalSlug;
        // Update the tenant
        $updatedTenant = $this->tenantService->updateTenant($tenant->id, $validated);
        
        // If the slug has changed, redirect to the new URL
        if ($originalSlug !== $newSlug) {
            return redirect()->route('tenant.settings.edit', ['tenantSlug' => $newSlug])
                ->with('success', 'Company settings updated successfully. Your company URL has been updated.');
        }
        
        // Otherwise, redirect back to the same page
        return redirect()->back()->with('success', 'Company settings updated successfully.');
    }
}