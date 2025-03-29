<?php

namespace App\Services\Tenants;

use App\Models\Tenant;
class TenantService
{
        /**
     * Create a new tenant.
     *
     * @param array $data
     * @return \App\Models\Tenant
     */
    public function createTenant(array $data)
    {
        $name = trim($data['name']);
        $words = preg_split('/\s+/', $name);
        $slug = count($words) === 1 ? strtolower($words[0]) : strtolower(implode('', array_map(fn($w) => substr($w, 0, 1), $words)));
        $originalSlug = $slug;
        $count = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug . $count;
            $count++;
        }
        $uniqueCompanyName = $name;
        $companyCount = 1;
        while (Tenant::where('name', $uniqueCompanyName)->exists()) {
            $uniqueCompanyName = $name . ' ' . $companyCount;
            $companyCount++;
        }
        return Tenant::create(['name' => $uniqueCompanyName, 'slug' => $slug]);
    }

    /**
     * Update an existing tenant.
     *
     * @param int $tenantId
     * @param array $data
     * @return \App\Models\Tenant
     */
    public function updateTenant($tenantId, array $data)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update($data);
        return $tenant;
    }

    /**
     * Delete a tenant.
     *
     * @param int $tenantId
     * @return void
     */
    public function deleteTenant($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->delete();
    }
}