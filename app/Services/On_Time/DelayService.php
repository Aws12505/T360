<?php

namespace App\Services\On_Time;

use App\Models\Delay;
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
    ) {
        $this->filteringService = $filteringService;
        $this->delayBreakdownService = $delayBreakdownService;
    }

    // ─────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────

    /**
     * Convert hours + minutes into total minutes.
     */
    private function resolveDuration(array $data): int
    {
        $hours   = (int) ($data['delay_duration_hours']   ?? 0);
        $minutes = (int) ($data['delay_duration_minutes'] ?? 0);
        return ($hours * 60) + $minutes;
    }

    /**
     * Derive delay_category from total minutes.
     *
     *   1  –  60 min  → 1_60
     *  61  – 240 min  → 61_240
     * 241  – 600 min  → 241_600
     * 601+       min  → 601_plus
     */
    private function resolveCategory(int $totalMinutes): string
    {
        return match (true) {
            $totalMinutes <= 60  => '1_60',
            $totalMinutes <= 240 => '61_240',
            $totalMinutes <= 600 => '241_600',
            default              => '601_plus',
        };
    }

    /**
     * Derive penalty from delay_category.
     *
     *  1_60     → 1
     *  61_240   → 2
     *  241_600  → 4
     *  601_plus → 8
     */
    private function resolvePenalty(string $category): int
    {
        return match ($category) {
            '1_60'     => 1,
            '61_240'   => 2,
            '241_600'  => 4,
            '601_plus' => 8,
            default    => 0,
        };
    }

    /**
     * Build the computed fields (duration, category, penalty) from raw request data
     * and merge them back, removing the UI-only hour/minute fields.
     */
    private function resolveComputedFields(array $data): array
    {
        $totalMinutes          = $this->resolveDuration($data);
        $category              = $this->resolveCategory($totalMinutes);
        $penalty               = $this->resolvePenalty($category);

        $data['delay_duration'] = $totalMinutes;
        $data['delay_category'] = $category;
        $data['penalty']        = $penalty;

        // Remove the UI-only fields — not stored in DB
        unset($data['delay_duration_hours'], $data['delay_duration_minutes']);

        return $data;
    }

    // ─────────────────────────────────────────────
    //  Index
    // ─────────────────────────────────────────────

    public function getDelaysIndex(): array
    {
        $query = Delay::with('tenant');

        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange  = [];

        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'date', $dateRange);
        }

        $request = request();

        // Search: driver name OR load_id
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(driver_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(load_id) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('delayCategory')) {
            $query->where('delay_category', $request->input('delayCategory'));
        }

        if ($request->filled('delayType')) {
            $query->where('delay_type', $request->input('delayType'));
        }

        if ($request->filled('disputed')) {
            $query->where('disputed', $request->input('disputed'));
        }

        if ($request->has('driverControllable')) {
            $value = $request->input('driverControllable');
            if ($value === 'NA') {
                $query->whereNull('driver_controllable');
            } elseif (in_array($value, ['true', 'false'])) {
                $query->where('driver_controllable', $value === 'true');
            }
        }

        if ($request->has('carrierControllable')) {
            $value = $request->input('carrierControllable');
            if ($value === 'NA') {
                $query->whereNull('carrier_controllable');
            } elseif (in_array($value, ['true', 'false'])) {
                $query->where('carrier_controllable', $value === 'true');
            }
        }

        $reasonFilter = $request->input('delayReasonFilter', 'with_reason');
        if ($reasonFilter === 'with_reason') {
            $query->whereNotNull('delay_reason')->where('delay_reason', '!=', '');
        } elseif ($reasonFilter === 'without_reason') {
            $query->where(function ($q) {
                $q->whereNull('delay_reason')->orWhere('delay_reason', '');
            });
        }

        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));

        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        $delays = $query->latest('date')->paginate($perPage);

        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug   = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants      = $isSuperAdmin ? Tenant::all() : [];

        // Week number calculation
        $weekNumber      = null;
        $startWeekNumber = null;
        $endWeekNumber   = null;
        $year            = null;

        if (!empty($dateRange) && isset($dateRange['start'])) {
            $startDate = Carbon::parse($dateRange['start']);
            $year      = $startDate->year;

            if (in_array($dateFilter, ['yesterday', 'current-week'])) {
                $weekNumber      = $this->weekNumberSundayStart($startDate);
                $startWeekNumber = $endWeekNumber = null;
            } else {
                $weekNumber      = null;
                $startWeekNumber = $this->weekNumberSundayStart($startDate);
                $endWeekNumber   = isset($dateRange['end'])
                    ? $this->weekNumberSundayStart(Carbon::parse($dateRange['end']))
                    : $startWeekNumber;
            }
        }

        $delayBreakdown = $this->delayBreakdownService->getDelayBreakdownDetailsPage(
            $dateRange['start'] ?? null,
            $dateRange['end'] ?? null
        );

        $lineChartData = $this->delayBreakdownService->getLineChartData(
            $dateRange['start'] ?? null,
            $dateRange['end'] ?? null
        );

        $filters = [
            'search'             => (string) $request->input('search', ''),
            'delayType'          => (string) $request->input('delayType', ''),
            'delayCategory'      => (string) $request->input('delayCategory', ''),
            'disputed'           => (string) $request->input('disputed', ''),
            'driverControllable' => (string) $request->input('driverControllable', ''),
            'carrierControllable' => (string) $request->input('carrierControllable', ''),
            'delayReasonFilter'  => (string) $request->input('delayReasonFilter', 'with_reason'),
        ];

        return [
            'delays'           => $delays,
            'tenantSlug'       => $tenantSlug,
            'isSuperAdmin'     => $isSuperAdmin,
            'tenants'          => $tenants,
            'dateRange'        => $dateRange,
            'dateFilter'       => $dateFilter,
            'weekNumber'       => $weekNumber,
            'startWeekNumber'  => $startWeekNumber,
            'endWeekNumber'    => $endWeekNumber,
            'year'             => $year,
            'delay_breakdown'  => $delayBreakdown,
            'line_chart_data'  => $lineChartData['chartData'] ?? [],
            'average_ontime'   => $lineChartData['averageOnTime'] ?? null,
            'filters'          => $filters,
            'permissions'      => Auth::user()->getAllPermissions(),
        ];
    }

    // ─────────────────────────────────────────────
    //  CRUD
    // ─────────────────────────────────────────────

    public function createDelay(array $data): void
    {
        $user              = Auth::user();
        $data['tenant_id'] = is_null($user->tenant_id) ? $data['tenant_id'] : $user->tenant_id;
        $data              = $this->resolveComputedFields($data);

        Delay::create($data);
    }

    public function updateDelay(int $id, array $data): void
    {
        $user              = Auth::user();
        $data['tenant_id'] = is_null($user->tenant_id) ? $data['tenant_id'] : $user->tenant_id;
        $data              = $this->resolveComputedFields($data);

        Delay::findOrFail($id)->update($data);
    }

    public function deleteDelay(int $id): void
    {
        Delay::findOrFail($id)->delete();
    }

    public function deleteMultipleDelays(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $query = Delay::whereIn('id', $ids);

        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        $query->delete();
    }

    // ─────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────

    private function weekNumberSundayStart(Carbon $date): int
    {
        $dayOfYear   = $date->dayOfYear;
        $firstDayDow = $date->copy()->startOfYear()->dayOfWeek;
        return (int) ceil(($dayOfYear + $firstDayDow) / 7);
    }
}
