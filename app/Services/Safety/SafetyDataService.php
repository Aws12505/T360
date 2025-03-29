<?php

namespace App\Services\Safety;

use App\Models\SafetyData;
use Illuminate\Support\Facades\Auth;

/**
 * Class SafetyDataService
 *
 * Contains business logic for safety data operations, including CRUD, import, and export.
 *
 * Created manually: touch app/Services/SafetyDataService.php
 */
class SafetyDataService
{
    /**
     * Get safety data entries for the index view.
     *
     * @return array
     */
    public function getSafetyDataIndex(): array
    {
        $entries = SafetyData::with('tenant')->get();
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? \App\Models\Tenant::all() : [];
        return [
            'entries'    => $entries,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
        ];
    }

    /**
     * Create a new safety data entry.
     *
     * @param array $data
     * @return void
     */
    public function createEntry(array $data)
    {
        SafetyData::create($data);
    }

    /**
     * Update an existing safety data entry.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateEntry($id, array $data)
    {
        $entry = SafetyData::findOrFail($id);
        $entry->update($data);
    }

    /**
     * Delete a safety data entry.
     *
     * @param int $id
     * @return void
     */
    public function deleteEntry($id)
    {
        $entry = SafetyData::findOrFail($id);
        $entry->delete();
    }
}
