<?php

namespace App\Services\On_Time;

use App\Models\Delay;
use App\Models\DelayCode;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use App\Services\Summaries\DelayBreakdownService;

class DelayService
{
    protected $filteringService;
    protected $delayBreakdownService;

    public function __construct(
        FilteringService $filteringService,
        DelayBreakdownService $delayBreakdownService
    )
    {
        $this->filteringService = $filteringService;
        $this->delayBreakdownService = $delayBreakdownService;
    }

    /**
     * Get delay data for the index view.
     *
     * Note: The delayCode relationship is loaded withTrashed
     * so that even soft‑deleted delay codes appear with delays.
     * Also, all delay codes are retrieved with trashed for display in the manage section.
     *
     * @return array
     */
    public function getDelaysIndex(): array
    {
        // Retrieve delays along with their tenant and delay code (including soft-deleted codes)
        $query = Delay::with([
            'tenant',
            'delayCode' => function ($query) {
                $query->withTrashed();
            }
        ]);

        // Apply date filtering if requested
        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange = [];

        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'date', $dateRange);
        }
        $request = request();

if ($request->filled('search')) {
    $search = strtolower($request->input('search'));
    $query->where(function ($q) use ($search) {
        $q->whereRaw('LOWER(driver_name) LIKE ?', ["%{$search}%"]);
    });
}

if ($request->filled('delayCode')) {
    $query->where('delay_code_id', $request->input('delayCode'));
}

if ($request->filled('delayCategory')) {
    $query->where('delay_category', $request->input('delayCategory'));
}

if ($request->filled('delayType')) {
    $query->where('delay_type', $request->input('delayType'));
}

if ($request->filled('disputed')) {
    $query->where('disputed', $request->boolean('disputed'));
}

if ($request->has('controllable')) {
    $controllable = $request->input('controllable');
    if ($controllable === 'NA') {
        $query->whereNull('driver_controllable');
    } elseif ($controllable === 'true' || $controllable === 'false') {
        $query->where('driver_controllable', $controllable === 'true');
    }
}

        // Get the per page value from request (default 10)
        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));

        // Apply tenant filter for non‑admin users
        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        $delays = $query->latest('date')->paginate($perPage);

        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];

        // Retrieve ALL delay codes including soft-deleted ones, for listing in the table and
        // the "Manage Delay Codes" section.
        $delayCodes = DelayCode::withTrashed()->get();
        
        // Calculate week numbers for display
        $weekNumber = null;
        $startWeekNumber = null;
        $endWeekNumber = null;
        $year = null;
        if (!empty($dateRange) && isset($dateRange['start'])) {
            $startDate = Carbon::parse($dateRange['start']);
            $year = $startDate->year;

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
        
        // Get delay breakdown data
        $delayBreakdown = $this->delayBreakdownService->getDelayBreakdownDetailsPage(
            $dateRange['start'] ?? null, 
            $dateRange['end'] ?? null
        );
        
        // Get line chart data for on-time performance trends
        $lineChartData = $this->delayBreakdownService->getLineChartData(
            $dateRange['start'] ?? null, 
            $dateRange['end'] ?? null
        );
        $filters = [
            'search' => (string) $request->input('search', ''),
            'delayType' => (string) $request->input('delayType', ''),
            'delayCode' => (string) $request->input('delayCode', ''),
            'delayCategory' => (string) $request->input('delayCategory', ''),
            'disputed' => (string) $request->input('disputed', ''),
            'driverControllable' => (string) $request->input('driverControllable', ''),
        ];
        return [
            'delays'      => $delays,
            'tenantSlug'  => $tenantSlug,
            'isSuperAdmin'=> $isSuperAdmin,
            'tenants'     => $tenants,
            'delay_codes' => $delayCodes,
            'dateRange'   => $dateRange,
            'dateFilter'  => $dateFilter,
            'weekNumber' => $weekNumber,
            'startWeekNumber' => $startWeekNumber,
            'endWeekNumber' => $endWeekNumber,
            'year' => $year,
            'delay_breakdown' => $delayBreakdown,
            'line_chart_data'      => $lineChartData['chartData'] ?? [],
            'average_ontime'   => $lineChartData['averageOnTime'] ?? null,
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
            '1_120'    => 1,
            '121_600'  => 2,
            '601_plus' => 4,
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
            '1_120'    => 1,
            '121_600'  => 2,
            '601_plus' => 4,
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
     * Delete multiple delay records.
     *
     * @param array $ids
     * @return void
     */
    public function deleteMultipleDelays(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete delays they have access to
        $query = Delay::whereIn('id', $ids);
        
        // If not a super admin, restrict to tenant's delays
        $user = Auth::user();
        if (!is_null($user->tenant_id)) {
            $query->where('tenant_id', $user->tenant_id);
        }
        
        $query->delete();
    }
}
