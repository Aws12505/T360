<?php

namespace App\Services\Driver;

use App\Models\Driver;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\Summaries\SummariesService;

class DriverDataService
{
    protected $filteringService;
/** @var int|null Tenant override (e.g. from CLI) */
protected ?int $overrideTenantId;

    public function __construct(FilteringService $filteringService, ?int $overrideTenantId = null)
    {
        $this->filteringService = $filteringService;
        $this->overrideTenantId   = $overrideTenantId;
    }

    /**
     * Get driver entries for the index view.
     */
    public function getDriverIndex()
    {
        $query = Driver::query()->with('tenant');
        
        // Apply date filtering if requested

        

        
        // Get per page value
        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));
        
        // Apply tenant filter for non-admin users
        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
        $drivers = $query->paginate($perPage);
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        $permissions = Auth::user()->getAllPermissions();

        return [
            'entries'    => $drivers,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
            'permissions'=> $permissions,

        ];
    }

    /**
     * Create a new driver entry.
     */
    public function createDriver(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        if (request()->hasFile('image')) {
            $path = request()->file('image')->store('drivers', 'public');
            $data['image'] = $path;
        }
        Driver::create($data);
    }

    /**
     * Update an existing driver entry.
     */
    public function updateDriver($id, array $data)
    {
        $driver = Driver::findOrFail($id);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // If password is not present, remove it so it doesn't overwrite with null
            unset($data['password']);
        }

        if (request()->hasFile('image')) {
            // Delete old image if exists
            if ($driver->image && Storage::disk('public')->exists($driver->image)) {
                Storage::disk('public')->delete($driver->image);
            }
    
            // Store new image
            $path = request()->file('image')->store('drivers', 'public');
            $data['image'] = $path;
        }
        $driver->update($data);
    }

    /**
     * Delete a driver entry.
     */
    public function deleteDriver($id)
    {
        $driver = Driver::findOrFail($id);
        if ($driver->image && Storage::disk('public')->exists($driver->image)) {
            Storage::disk('public')->delete($driver->image);
        }
        $driver->delete();
    }

    /**
     * Delete multiple driver entries.
     *
     * @param array $ids Array of driver IDs to delete
     * @return void
     */
    public function deleteMultipleDrivers(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete drivers they have access to
        $query = Driver::whereIn('id', $ids);
        
        // If not a super admin, restrict to tenant's drivers
        $user = Auth::user();
        if (!is_null($user->tenant_id)) {
            $query->where('tenant_id', $user->tenant_id);
        }
        $drivers = $query->get();

    foreach ($drivers as $driver) {
        // Delete image if exists
        if ($driver->image && Storage::disk('public')->exists($driver->image)) {
            Storage::disk('public')->delete($driver->image);
        }

        $driver->delete();
    }
}

public function getProfileData(Driver $driver, $dateFilter): array
{
    $now = Carbon::now();
        $isSunday = $now->dayOfWeek === 0; // 0 = Sunday in Carbon

        switch ($dateFilter) {
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                break;

            case 'current-week':
                $startDate = $now->copy()->startOfDay()->modify('last sunday');
                if ($isSunday) {
                    $startDate->subWeek();
                }
                $endDate = $startDate->copy()->addDays(6)->endOfDay(); // Saturday
                break;

            case '6w':
                $startDate = $now->copy()->modify('last sunday');
                if ($isSunday) {
                    $startDate->subWeek();
                }
                $startDate->subWeeks(5)->startOfDay();
                $endDate = $now->copy()->modify('this saturday');
                if ($isSunday) {
                    $endDate->subWeek();
                }
                $endDate->endOfDay();
                break;

            case 'quarterly':
                $startDate = $now->copy()->subMonths(3)->modify('last sunday');
                if ($isSunday) {
                    $startDate->subWeek();
                }
                $startDate->startOfDay();
                $endDate = $now->copy()->modify('this saturday');
                if ($isSunday) {
                    $endDate->subWeek();
                }
                $endDate->endOfDay();
                break;

            case 'full':
                $startDate = Carbon::parse('2023-01-01')->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            default:
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
        }
    // Step 1: Fetch all drivers scores with date filtering
    $allDriversScores = $this->getDriversOverallPerformance($startDate,$endDate);

    // Step 2: Find this driver's score entry
    $driverName = $driver->first_name . ' ' . $driver->last_name;
    $driverScoreEntry = collect($allDriversScores['drivers'])
        ->firstWhere('driver_name', $driverName);

    // Step 3: Compute rank (index in sorted list + 1)
    $rank = 0;
    foreach ($allDriversScores['drivers'] as $index => $entry) {
        if ($entry['driver_name'] === $driverName) {
            $rank = $index + 1;
            break;
        }
    }

    // Step 4: Fetch infractions with date filtering
    $infractions = $this->getDriverInfractions($driver, $startDate, $endDate);

    // Step 5: Assemble final data
    return [
        // Basic profile
        'name' => $driverName,
        'image' => $driver->image ? asset('storage/' . $driver->image) : null,
        'phone' => $driver->mobile_phone,
        'email' => $driver->email,
        'hireDate' => $driver->hiring_date,
        'tenant' => $driver->tenant->name ?? null,

        // Performance
        'rank' => $rank,
        'acceptance_score' => $driverScoreEntry['acceptance_score'] ?? 0,
        'on_time_score' => $driverScoreEntry['on_time_score'] ?? 0,
        'greenZoneScore' => $driverScoreEntry['safety_score'] ?? 0,
        'overall_score' => $driverScoreEntry['overall_score'] ?? 0,
        'last_updated' => $driverScoreEntry['last_updated'] ?? null,
        
        // Safety infractions
        'infractions' => $infractions,

        // Operational
        'minutesAnalyzed' => $driverScoreEntry['minutes_analyzed'] ?? 0,
        'trips' => 0, // For now as you requested
        'dateFilter' => $dateFilter,
    ];
}

    /**
     * Clone of your getDriversOverallPerformance — but without date filtering
     */
    private function getDriversOverallPerformance($startDate, $endDate): array
    {
        $driversQuery = DB::table('drivers')
            ->select([
                'drivers.id',
                DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name) AS driver_name"),
                'drivers.netradyne_user_name',
            ])
            // Safety Score
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from('safety_data')
                    ->selectRaw('AVG(driver_score)')
                    ->whereColumn('tenant_id', 'drivers.tenant_id')
                    ->where(function ($q) {
                        $q->whereColumn('driver_name', DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"))
                          ->orWhereColumn('user_name', 'drivers.netradyne_user_name');
                    })
                    ->whereBetween('date', [$startDate, $endDate]);
            }, 'avg_safety_score')
    
            // Minutes Analyzed
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from('safety_data')
                    ->selectRaw('SUM(minutes_analyzed)')
                    ->whereColumn('tenant_id', 'drivers.tenant_id')
                    ->where(function ($q) {
                        $q->whereColumn('driver_name', DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"))
                          ->orWhereColumn('user_name', 'drivers.netradyne_user_name');
                    })
                    ->whereBetween('date', [$startDate, $endDate]);
            }, 'minutes_analyzed')
    
            // Last Updated (Safety)
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from('safety_data')
                    ->selectRaw('MAX(updated_at)')
                    ->whereColumn('tenant_id', 'drivers.tenant_id')
                    ->where(function ($q) {
                        $q->whereColumn('driver_name', DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"))
                          ->orWhereColumn('user_name', 'drivers.netradyne_user_name');
                    })
                    ->whereBetween('updated_at', [$startDate, $endDate]);
            }, 'last_updated_safety')
    
            // Rejection Penalties
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from('rejections')
                    ->selectRaw('SUM(penalty)')
                    ->where('driver_controllable', true)
                    ->whereColumn('tenant_id', 'drivers.tenant_id')
                    ->whereColumn('driver_name', DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"))
                    ->whereBetween('date', [$startDate, $endDate]);
            }, 'sum_rejection_penalties')
    
            // Last Updated (Rejection)
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from('rejections')
                    ->selectRaw('MAX(updated_at)')
                    ->where('driver_controllable', true)
                    ->whereColumn('tenant_id', 'drivers.tenant_id')
                    ->whereColumn('driver_name', DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"))
                    ->whereBetween('updated_at', [$startDate, $endDate]);
            }, 'last_updated_rejection')
    
            // Delay Penalties
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from('delays')
                    ->selectRaw('SUM(penalty)')
                    ->where('driver_controllable', true)
                    ->whereColumn('tenant_id', 'drivers.tenant_id')
                    ->whereColumn('driver_name', DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"))
                    ->whereBetween('date', [$startDate, $endDate]);
            }, 'sum_delay_penalties')
    
            // Last Updated (Delay)
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from('delays')
                    ->selectRaw('MAX(updated_at)')
                    ->where('driver_controllable', true)
                    ->whereColumn('tenant_id', 'drivers.tenant_id')
                    ->whereColumn('driver_name', DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name)"))
                    ->whereBetween('updated_at', [$startDate, $endDate]);
            }, 'last_updated_delay');
    
        $this->applyTenantFilter($driversQuery);
        $driversWithAggregates = $driversQuery->get();
    
        // Global penalty totals
        $totalRejectionPenalties = DB::table('rejections')
            ->where('driver_controllable', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->tap(fn($q) => $this->applyTenantFilter($q))
            ->sum('penalty');
    
        $totalDelayPenalties = DB::table('delays')
            ->where('driver_controllable', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->tap(fn($q) => $this->applyTenantFilter($q))
            ->sum('penalty');
    
        // Global last_updated
        $globalLastUpdated = collect([
            DB::table('safety_data')->whereBetween('date', [$startDate, $endDate])->tap(fn($q) => $this->applyTenantFilter($q))->max('updated_at'),
            DB::table('rejections')->where('driver_controllable', true)->whereBetween('updated_at', [$startDate, $endDate])->tap(fn($q) => $this->applyTenantFilter($q))->max('updated_at'),
            DB::table('delays')->where('driver_controllable', true)->whereBetween('updated_at', [$startDate, $endDate])->tap(fn($q) => $this->applyTenantFilter($q))->max('updated_at'),
        ])->filter()->max();
    
        // Scoring calculations
        $driversOverallScores = [];
    
        foreach ($driversWithAggregates as $row) {
            $minutesAnalyzed = (int) ($row->minutes_analyzed ?? 0);
            if ($minutesAnalyzed === 0) continue;
    
            $safetyScoreRaw = (float) ($row->avg_safety_score ?? 0);
            $safetyScoreNormalized = $safetyScoreRaw * 100 / 1050;
    
            $rejectionPenalties = (float) ($row->sum_rejection_penalties ?? 0);
            $delayPenalties = (float) ($row->sum_delay_penalties ?? 0);
    
            $acceptanceScore = $totalRejectionPenalties > 0
                ? 100.0 - ($rejectionPenalties * 100 / $totalRejectionPenalties)
                : 100.0;
    
            $onTimeScore = $totalDelayPenalties > 0
                ? 100.0 - ($delayPenalties * 100 / $totalDelayPenalties)
                : 100.0;
    
            $overallScore = ($acceptanceScore + $onTimeScore + $safetyScoreNormalized) / 3.0;
    
            $lastUpdated = collect([
                $row->last_updated_safety,
                $row->last_updated_rejection,
                $row->last_updated_delay,
            ])->filter()->max();
    
            $driversOverallScores[] = [
                'driver_name'         => $row->driver_name,
                'acceptance_score'    => round($acceptanceScore, 2),
                'on_time_score'       => round($onTimeScore, 2),
                'safety_score'        => round($safetyScoreRaw, 2),
                'overall_score'       => round($overallScore, 2),
                'raw_safety_score'    => round($safetyScoreRaw, 2),
                'rejection_penalties' => $rejectionPenalties,
                'delay_penalties'     => $delayPenalties,
                'minutes_analyzed'    => $minutesAnalyzed,
                'last_updated'        => $lastUpdated ?? $globalLastUpdated,
            ];
        }
    
        usort($driversOverallScores, fn($a, $b) => $b['overall_score'] <=> $a['overall_score']);
    
        return ['drivers' => $driversOverallScores];
    }
    
/**
 * Return each driver’s performance metrics and phone within the given date range,
 * but only include those with a nonzero average greenzone (safety) score.
 *
 * Metrics:
 *  - acceptance_score : percentage out of 100 (100 = perfect acceptance)
 *  - on_time_score    : percentage out of 100 (100 = perfect timeliness)
 *  - safety_score     : average “greenzone” score (from safety_data → driver_score), rounded to 2 decimals
 *  - severe_alerts    : total count of severe alerts (sum of violations)
 *
 * @param  Carbon  $startDate  Inclusive start date of the window
 * @param  Carbon  $endDate    Inclusive end date of the window
 * @return array{drivers: array<int, array{
 *     driver_name: string,
 *     mobile_phone: string|null,
 *     acceptance_score: float,
 *     on_time_score: float,
 *     safety_score: float,
 *     severe_alerts: int
 * }>}
 */
public function getDriversOverallPerformanceCoaching(Carbon $startDate, Carbon $endDate): array
{
    $tid  = $this->overrideTenantId;
    $from = $startDate->toDateString();
    $to   = $endDate->toDateString();

    // 1) Sum total penalties for acceptance & on-time across all drivers
    $totalRej = DB::table('rejections')
        ->where('tenant_id', $tid)
        ->where('driver_controllable', true)
        ->whereBetween('date', [$from, $to])
        ->sum('penalty');

    $totalDel = DB::table('delays')
        ->where('tenant_id', $tid)
        ->where('driver_controllable', true)
        ->whereBetween('date', [$from, $to])
        ->sum('penalty');

    // 2) Build driver‐level aggregates with three LEFT JOIN subqueries
    $rows = DB::table('drivers')
        ->where('drivers.tenant_id', $tid)

        // rejection penalties per driver
        ->leftJoin(DB::raw("
            (
              SELECT driver_name, SUM(penalty) AS rejection_penalty
              FROM rejections
              WHERE tenant_id = {$tid}
                AND driver_controllable = 1
                AND date BETWEEN '{$from}' AND '{$to}'
              GROUP BY driver_name
            ) rej
        "), DB::raw("CONCAT(drivers.first_name,' ',drivers.last_name)"), '=', 'rej.driver_name')

        // delay penalties per driver
        ->leftJoin(DB::raw("
            (
              SELECT driver_name, SUM(penalty) AS delay_penalty
              FROM delays
              WHERE tenant_id = {$tid}
                AND driver_controllable = 1
                AND date BETWEEN '{$from}' AND '{$to}'
              GROUP BY driver_name
            ) del
        "), DB::raw("CONCAT(drivers.first_name,' ',drivers.last_name)"), '=', 'del.driver_name')

        // safety score (average) and severe alerts (sum) per driver
        ->leftJoin(DB::raw("
            (
              SELECT
                sd.driver_name AS name,
                sd.user_name   AS uname,
                ROUND(AVG(sd.driver_score), 2) AS safety_score,
                COALESCE(SUM(
                    sd.traffic_light_violation
                  + sd.speeding_violations
                  + sd.following_distance
                  + sd.driver_distraction
                  + sd.sign_violations
                  + sd.roadside_parking
                ), 0) AS severe_alerts
              FROM safety_data sd
              WHERE sd.tenant_id = {$tid}
                AND sd.date BETWEEN '{$from}' AND '{$to}'
              GROUP BY sd.driver_name, sd.user_name
            ) safe
        "), function($join) {
            $join->on(DB::raw("CONCAT(drivers.first_name,' ',drivers.last_name)"), '=', 'safe.name')
                 ->orOn('drivers.netradyne_user_name', '=', 'safe.uname');
        })

        ->select([
            DB::raw("CONCAT(drivers.first_name,' ',drivers.last_name) AS driver_name"),
            'drivers.mobile_phone',
            DB::raw("COALESCE(rej.rejection_penalty, 0) AS sum_rejection_penalties"),
            DB::raw("COALESCE(del.delay_penalty,     0) AS sum_delay_penalties"),
            DB::raw("COALESCE(safe.safety_score,    0) AS safety_score"),
            DB::raw("COALESCE(safe.severe_alerts,   0) AS severe_alerts"),
        ])
        ->get()
        // 3) Filter out any driver whose average safety_score is zero or null
        ->filter(fn($r) => $r->safety_score > 0);

    // 4) Calculate percentage scores and assemble output
    $out = [];
    foreach ($rows as $r) {
        $accPct = $totalRej > 0
            ? 100.0 - ($r->sum_rejection_penalties * 100.0 / $totalRej)
            : 100.0;

        $otPct  = $totalDel > 0
            ? 100.0 - ($r->sum_delay_penalties      * 100.0 / $totalDel)
            : 100.0;

        $out[] = [
            'driver_name'      => $r->driver_name,
            'mobile_phone'     => $r->mobile_phone,
            'acceptance_score' => round($accPct, 2),
            'on_time_score'    => round($otPct,   2),
            'safety_score'     => (float) $r->safety_score,
            'severe_alerts'    => (int)   $r->severe_alerts,
        ];
    }

    return ['drivers' => $out];
}

    /**
     * Get infractions — no date filtering
     */
    private function getDriverInfractions(Driver $driver,$startDate,$endDate): array
    {
        $driverName = $driver->first_name . ' ' . $driver->last_name;
        $netradyneUserName = $driver->netradyne_user_name;

        $query = DB::table('safety_data')
            ->selectRaw("
                SUM(traffic_light_violation) AS traffic_light_violation,
                SUM(speeding_violations) AS speeding_violations,
                SUM(following_distance) AS following_distance,
                SUM(driver_distraction) AS driver_distraction,
                SUM(sign_violations) AS sign_violations,
                SUM(roadside_parking) AS roadside_parking
            ")
            ->where(function($query) use ($driverName, $netradyneUserName) {
                $query->where('user_name', $netradyneUserName)
                      ->orWhere('driver_name', $driverName);
            });

        $this->applyTenantFilter($query);
        $query->whereBetween('date', [$startDate, $endDate]);
        $result = $query->first();

        return [
            'distraction' => (int) ($result->driver_distraction ?? 0),
            'speeding' => (int) ($result->speeding_violations ?? 0),
            'sign' => (int) ($result->sign_violations ?? 0),
            'light' => (int) ($result->traffic_light_violation ?? 0),
            'following' => (int) ($result->following_distance ?? 0),
            'roadside_parking' => (int) ($result->roadside_parking ?? 0),
        ];
    }
    public function forTenant(int $tenantId): self
    {
        $this->overrideTenantId = $tenantId;
        return $this;
    }
    /**
     * Helper to apply tenant filtering — you already have this method
     */
    protected function applyTenantFilter($query): void
    {
        if ($this->overrideTenantId !== null) {
            $query->where('tenant_id', $this->overrideTenantId);
        } elseif (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
    }
}
