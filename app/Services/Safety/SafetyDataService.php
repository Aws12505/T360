<?php

namespace App\Services\Safety;

use App\Models\SafetyData;
use App\Services\Filtering\FilteringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\Tenant;
/**
 * Class SafetyDataService
 *
 * Contains business logic for safety data operations, including CRUD, import, and export.
 *
 * Created manually: touch app/Services/SafetyDataService.php
 */
class SafetyDataService
{
    protected $filteringService;

    public function __construct(FilteringService $filteringService)
    {
        $this->filteringService = $filteringService;
    }

    /**
     * Get safety data entries for the index view.
     *
     * @return array
     */
    public function getSafetyDataIndex(): array
    {
        $query = SafetyData::with('tenant');
        
        // Apply date filtering if requested
        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange = [];
        
        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'date', $dateRange);
        }
        
        // Get per page value
        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));
        
        // Apply tenant filter for non-admin users
        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $entries = $query->latest('date')->paginate($perPage);
        
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        
        return [
            'entries'    => $entries,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
            'dateRange'  => $dateRange,
            'dateFilter' => $dateFilter,
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

    /**
     * Delete multiple safety data entries.
     *
     * @param array $ids
     * @return void
     */
    public function deleteMultipleEntries(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete entries they have access to
        $query = SafetyData::whereIn('id', $ids);
        
        // If not a super admin, restrict to tenant's entries
        $user = Auth::user();
        if (!is_null($user->tenant_id)) {
            $query->where('tenant_id', $user->tenant_id);
        }
        
        $query->delete();
    }
}
