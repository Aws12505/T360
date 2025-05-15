<?php

namespace App\Services\Acceptance;

use App\Models\Rejection;
use App\Models\RejectionReasonCode;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;
use Carbon\Carbon;
use App\Services\Summaries\RejectionBreakdownService;
/**
 * Class RejectionService
 *
 * Contains business logic for rejection management and reason code operations.
 *
 * Created manually: touch app/Services/RejectionService.php
 */
class RejectionService
{
    protected FilteringService $filteringService;
    protected RejectionBreakdownService $rejectionBreakdownService;

    /**
     * Constructor.
     *
     * @param FilteringService $filteringService Service for filtering and pagination.
     * @param RejectionBreakdownService $rejectionBreakdownService Service for rejection breakdown data.
     */
    public function __construct(
        FilteringService $filteringService,
        RejectionBreakdownService $rejectionBreakdownService
    )
    {
        $this->filteringService = $filteringService;
        $this->rejectionBreakdownService = $rejectionBreakdownService;
    }

    /**
     * Get rejection data for the index view.
     *
     * @return array
     */
    public function getRejectionsIndex(): array
    {
        $user = Auth::user();
        $isSuperAdmin = is_null($user->tenant_id);
        
        // Get filtering parameters
        $dateFilter = $this->filteringService->getDateFilter();
        $perPage = $this->filteringService->getPerPage();
        
        // Build query
        $query = Rejection::with([
            'tenant',
            'reasonCode' => function ($query) {
                $query->withTrashed();
            }
        ]);
        
        // Apply date filtering
        $dateRange = [];
        $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'date', $dateRange);
        $request = request();
if ($request->filled('search')) {
    $search = strtolower($request->input('search'));
    $query->where(function ($q) use ($search) {
        $q->whereRaw('LOWER(driver_name) LIKE ?', ["%{$search}%"]);
    });
}

if ($request->filled('rejectionType')) {
    $query->where('rejection_type', $request->input('rejectionType'));
}

if ($request->filled('reasonCode')) {
    $query->where('reason_code_id', $request->input('reasonCode'));
}

if ($request->filled('rejectionCategory')) {
    $query->where('rejection_category', $request->input('rejectionCategory'));
}

if ($request->filled('disputed')) {
    $query->where('disputed', $request->boolean('disputed'));
}

if ($request->has('driverControllable')) {
    $driverControllable = $request->input('driverControllable');
    if ($driverControllable === 'NA') {
        $query->whereNull('driver_controllable');
    } else if($driverControllable === 'true' || $driverControllable === 'false'){
        $query->where('driver_controllable', $driverControllable === 'true');
    }
}
        // Paginate results
        $rejections = $query->paginate($perPage);
        // Calculate week numbers for display
        $weekNumber = null;
        $startWeekNumber = null;
        $endWeekNumber = null;
        $year = null;
        if (!empty($dateRange) && isset($dateRange['start'])) {
            $startDate = Carbon::parse($dateRange['start']);
            $year = $startDate->year;
            // Removed dd($startDate) debug statement
            
            // compute week numbers (Sunday=first day)
            if (in_array($dateFilter, ['yesterday', 'current-week'])) {
                $weekNumber = $this->weekNumberSundayStart($startDate);
                $startWeekNumber = $endWeekNumber = null;
            } else {
                $weekNumber = null;
                $startWeekNumber = $this->weekNumberSundayStart($startDate);
                $endWeekNumber = isset($dateRange['end']) ? 
                    $this->weekNumberSundayStart(Carbon::parse($dateRange['end'])) : 
                    $startWeekNumber;
            }
        }
        
        // Get rejection breakdown data
        $rejectionBreakdown = $this->rejectionBreakdownService->getRejectionBreakdownDetailsPage(
            $dateRange['start'] ?? null, 
            $dateRange['end'] ?? null
        );
        
        // Get line chart data for acceptance performance trends
        $lineChartData = $this->rejectionBreakdownService->getLineChartData(
            $dateRange['start'] ?? null, 
            $dateRange['end'] ?? null
        );
        $filters = [
            'search' => (string) $request->input('search', ''),
            'rejectionType' => (string) $request->input('rejectionType', ''),
            'reasonCode' => (string) $request->input('reasonCode', ''),
            'rejectionCategory' => (string) $request->input('rejectionCategory', ''),
            'disputed' => (string) $request->input('disputed', ''),
            'driverControllable' => (string) $request->input('driverControllable', ''),
        ];
        
        return [
            'rejections'           => $rejections,
            'tenantSlug'           => $isSuperAdmin ? null : $user->tenant->slug,
            'isSuperAdmin'         => $isSuperAdmin,
            'tenants'              => $isSuperAdmin ? Tenant::all() : [],
            'rejection_reason_codes' => RejectionReasonCode::withTrashed()->get(),
            'dateFilter'           => $dateFilter,
            'dateRange'            => $dateRange,
            'perPage'              => $perPage,
            'weekNumber'           => $weekNumber,
            'startWeekNumber'      => $startWeekNumber,
            'endWeekNumber'        => $endWeekNumber,
            'year'                 => $year,
            'rejection_breakdown'  => $rejectionBreakdown,
            'line_chart_data'      => $lineChartData['chartData'] ?? [],
            'average_acceptance'   => $lineChartData['averageAcceptance'] ?? null,
            'filters' => $filters,
        ];
    }
/**
     * Get the week‐of‐year for a Carbon date, where weeks run Sunday → Saturday.
     *
     * @param  Carbon  $date
     * @return int
     */
    private function weekNumberSundayStart(Carbon $date): int
    {
        // 1..366
        $dayOfYear   = $date->dayOfYear;

        // 0=Sunday, …, 6=Saturday for Jan 1
        $firstDayDow = $date->copy()
                            ->startOfYear()
                            ->dayOfWeek;

        // shift so weeks bound on Sunday, then ceil
        return (int) ceil(($dayOfYear + $firstDayDow) / 7);
    }
    /**
     * Create a new rejection.
     *
     * @param array $data
     * @return void
     */
    public function createRejection(array $data)
    {
        $user = Auth::user();
        $data['tenant_id'] = is_null($user->tenant_id) ? $data['tenant_id'] : $user->tenant_id;
        $data['penalty'] = match ($data['rejection_category']) {
            'more_than_6' => 1,
            'within_6'    => 4,
            'after_start' => 8,
        };
        Rejection::create($data);
    }

    /**
     * Update an existing rejection.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateRejection($id, array $data)
    {
        $user = Auth::user();
        $data['tenant_id'] = is_null($user->tenant_id) ? $data['tenant_id'] : $user->tenant_id;
        $data['penalty'] = match ($data['rejection_category']) {
            'more_than_6' => 1,
            'within_6'    => 4,
            'after_start' => 8,
        };
        $rejection = Rejection::findOrFail($id);
        $rejection->update($data);
    }

    /**
     * Delete a rejection.
     *
     * @param int $id
     * @return void
     */
    public function deleteRejection($id)
    {
        $rejection = Rejection::findOrFail($id);
        $rejection->delete();
    }

    /**
     * Delete multiple rejection records.
     *
     * @param array $ids Array of rejection IDs to delete
     * @return void
     */
    public function deleteMultipleRejections(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete rejections they have access to
        $query = Rejection::whereIn('id', $ids);
        
        // If not a super admin, restrict to tenant's rejections
        $user = Auth::user();
        if (!is_null($user->tenant_id)) {
            $query->where('tenant_id', $user->tenant_id);
        }
        
        $query->delete();
    }
}
