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

public function getProfileData(Driver $driver): array
    {
        // Step 1: Fetch all drivers scores (no date filtering)
        $allDriversScores = $this->getDriversOverallPerformance();

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

        // Step 4: Fetch infractions (no date filtering)
        $infractions = $this->getDriverInfractions($driver);

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
            'last_updated' => $driverScoreEntry['last_updated']??null,
            // Safety infractions
            'infractions' => $infractions,

            // Operational
            'minutesAnalyzed' => $driverScoreEntry['minutes_analyzed'] ?? 0,
            'trips' => 0, // For now as you requested
        ];
    }

    /**
     * Clone of your getDriversOverallPerformance — but without date filtering
     */
    private function getDriversOverallPerformance(): array
{
    // Fetch per‐driver aggregates in one query
    $driverQuery = DB::table('drivers')
        ->select([
            'drivers.id',
            DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name) AS driver_name"),
            DB::raw("drivers.netradyne_user_name"),
            DB::raw("(
                SELECT AVG(sd.driver_score)
                FROM safety_data AS sd
                WHERE sd.tenant_id = drivers.tenant_id
                  AND (
                    sd.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
                    OR sd.user_name   = drivers.netradyne_user_name
                  )
            ) AS avg_safety_score"),
            DB::raw("(
                SELECT SUM(sd.minutes_analyzed)
                FROM safety_data AS sd
                WHERE sd.tenant_id = drivers.tenant_id
                  AND (
                    sd.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
                    OR sd.user_name   = drivers.netradyne_user_name
                  )
            ) AS minutes_analyzed"),
            DB::raw("(
                SELECT MAX(sd.updated_at)
                FROM safety_data AS sd
                WHERE sd.tenant_id = drivers.tenant_id
                  AND (
                    sd.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
                    OR sd.user_name   = drivers.netradyne_user_name
                  )
            ) AS last_updated_safety"),
            DB::raw("(
                SELECT SUM(rj.penalty)
                FROM rejections AS rj
                WHERE rj.tenant_id = drivers.tenant_id
                  AND rj.driver_controllable = 1
                  AND rj.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
            ) AS sum_rejection_penalties"),
            DB::raw("(
                SELECT MAX(rj.updated_at)
                FROM rejections AS rj
                WHERE rj.tenant_id = drivers.tenant_id
                  AND rj.driver_controllable = 1
                  AND rj.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
            ) AS last_updated_rejection"),
            DB::raw("(
                SELECT SUM(dl.penalty)
                FROM delays AS dl
                WHERE dl.tenant_id = drivers.tenant_id
                  AND dl.driver_controllable = 1
                  AND dl.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
            ) AS sum_delay_penalties"),
            DB::raw("(
                SELECT MAX(dl.updated_at)
                FROM delays AS dl
                WHERE dl.tenant_id = drivers.tenant_id
                  AND dl.driver_controllable = 1
                  AND dl.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
            ) AS last_updated_delay"),
        ]);

    $this->applyTenantFilter($driverQuery);
    $driversWithAggregates = $driverQuery->get();

    // Compute grand totals for penalty normalization
    $totalRejectionPenaltiesRow = DB::table('rejections')
        ->where('driver_controllable', true);
    $this->applyTenantFilter($totalRejectionPenaltiesRow);
    $totalRejectionPenalties = $totalRejectionPenaltiesRow
        ->selectRaw('SUM(penalty) as total_rejection_penalties')
        ->first()
        ->total_rejection_penalties ?? 0;

    $totalDelayPenaltiesRow = DB::table('delays')
        ->where('driver_controllable', true);
    $this->applyTenantFilter($totalDelayPenaltiesRow);
    $totalDelayPenalties = $totalDelayPenaltiesRow
        ->selectRaw('SUM(penalty) as total_delay_penalties')
        ->first()
        ->total_delay_penalties ?? 0;

    // Compute global last_updated across all three tables
    $latestSafety = DB::table('safety_data');
    $this->applyTenantFilter($latestSafety);
    $latestSafety = $latestSafety->selectRaw('MAX(updated_at) as latest')->first()->latest;

    $latestDelay = DB::table('delays')
        ->where('driver_controllable', true);
    $this->applyTenantFilter($latestDelay);
    $latestDelay = $latestDelay->selectRaw('MAX(updated_at) as latest')->first()->latest;

    $latestRejection = DB::table('rejections')
        ->where('driver_controllable', true);
    $this->applyTenantFilter($latestRejection);
    $latestRejection = $latestRejection->selectRaw('MAX(updated_at) as latest')->first()->latest;

    $globalLastUpdated = null;
    foreach ([$latestSafety, $latestDelay, $latestRejection] as $ts) {
        if ($ts !== null) {
            $globalLastUpdated = $globalLastUpdated === null
                ? $ts
                : max($globalLastUpdated, $ts);
        }
    }

    $driversOverallScores = [];
    foreach ($driversWithAggregates as $row) {
        $driverName       = $row->driver_name;
        $safetyScoreRaw   = (float) ($row->avg_safety_score ?? 0);
        $minutesAnalyzed  = (int)   ($row->minutes_analyzed ?? 0);

        if ($minutesAnalyzed === 0) {
            continue;
        }

        $safetyScoreNormalized = $safetyScoreRaw * 100.0 / 1050.0;
        $rejectionPenalties    = (float) ($row->sum_rejection_penalties ?? 0);
        $delayPenalties        = (float) ($row->sum_delay_penalties ?? 0);

        $acceptanceScore = 100.0;
        if ($totalRejectionPenalties > 0) {
            $acceptanceScore = 100.0 - ($rejectionPenalties * 100.0 / $totalRejectionPenalties);
        }

        $onTimeScore = 100.0;
        if ($totalDelayPenalties > 0) {
            $onTimeScore = 100.0 - ($delayPenalties * 100.0 / $totalDelayPenalties);
        }

        $overallScore = ($acceptanceScore + $onTimeScore + $safetyScoreNormalized) / 3.0;

        $candidates = [];
        if ($row->last_updated_safety   !== null) $candidates[] = $row->last_updated_safety;
        if ($row->last_updated_rejection !== null) $candidates[] = $row->last_updated_rejection;
        if ($row->last_updated_delay     !== null) $candidates[] = $row->last_updated_delay;

        $lastUpdatedDriver = null;
        if (! empty($candidates)) {
            $lastUpdatedDriver = max($candidates);
        }

        $driversOverallScores[] = [
            'driver_name'         => $driverName,
            'acceptance_score'    => round($acceptanceScore, 2),
            'on_time_score'       => round($onTimeScore, 2),
            'safety_score'        => round($safetyScoreRaw, 2),
            'overall_score'       => round($overallScore, 2),
            'raw_safety_score'    => round($safetyScoreRaw, 2),
            'rejection_penalties' => $rejectionPenalties,
            'delay_penalties'     => $delayPenalties,
            'minutes_analyzed'    => $minutesAnalyzed,
            'last_updated'        => $globalLastUpdated,
        ];
    }

    usort($driversOverallScores, function($a, $b) {
        return $b['overall_score'] <=> $a['overall_score'];
    });

    return [
        'drivers'      => $driversOverallScores,
    ];
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
    private function getDriverInfractions(Driver $driver): array
    {
        $driverName = $driver->first_name . ' ' . $driver->last_name;
        $netradyneUserName = $driver->netradyne_user_name;

        $query = DB::table('safety_data')
            ->selectRaw("
                SUM(traffic_light_violation) AS traffic_light_violation,
                SUM(speeding_violations) AS speeding_violations,
                SUM(following_distance) AS following_distance,
                SUM(driver_distraction) AS driver_distraction,
                SUM(sign_violations) AS sign_violations
            ")
            ->where(function($query) use ($driverName, $netradyneUserName) {
                $query->where('user_name', $netradyneUserName)
                      ->orWhere('driver_name', $driverName);
            });

        $this->applyTenantFilter($query);

        $result = $query->first();

        return [
            'speeding' => (int) ($result->speeding_violations ?? 0),
            'light' => (int) ($result->traffic_light_violation ?? 0),
            'sign' => (int) ($result->sign_violations ?? 0),
            'following' => (int) ($result->following_distance ?? 0),
            'distraction' => (int) ($result->driver_distraction ?? 0),
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
