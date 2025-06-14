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

// app/Services/Driver/DriverDataService.php

public function getDriversOverallPerformanceCoaching(): array
{
    $driverQuery = DB::table('drivers')
        ->select([
            'drivers.id',
            DB::raw("CONCAT(drivers.first_name, ' ', drivers.last_name) AS driver_name"),
            // existing safety & penalty aggregates...
            DB::raw("(
                SELECT SUM(sd.driver_score)
                FROM safety_data AS sd
                WHERE sd.tenant_id = drivers.tenant_id
                  AND (sd.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
                       OR sd.user_name = drivers.netradyne_user_name)
            ) AS avg_safety_score"),
            DB::raw("(
                SELECT SUM(rj.penalty)
                FROM rejections AS rj
                WHERE rj.tenant_id = drivers.tenant_id
                  AND rj.driver_controllable = 1
                  AND rj.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
            ) AS sum_rejection_penalties"),
            DB::raw("(
                SELECT SUM(dl.penalty)
                FROM delays AS dl
                WHERE dl.tenant_id = drivers.tenant_id
                  AND dl.driver_controllable = 1
                  AND dl.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
            ) AS sum_delay_penalties"),
            // NEW: total severe alerts
            DB::raw("(
                SELECT 
                    COALESCE(SUM(
                        sd.traffic_light_violation
                        + sd.speeding_violations
                        + sd.following_distance
                        + sd.driver_distraction
                        + sd.sign_violations
                    ), 0)
                FROM safety_data AS sd
                WHERE sd.tenant_id = drivers.tenant_id
                  AND (
                      sd.driver_name = CONCAT(drivers.first_name, ' ', drivers.last_name)
                      OR sd.user_name  = drivers.netradyne_user_name
                  )
            ) AS severe_alerts"),
        ]);


    $this->applyTenantFilter($driverQuery);
    $rows = $driverQuery->get();

    $tenantId = $this->overrideTenantId;

    $totalRej = DB::table('rejections')
        ->where('driver_controllable', true)
        ->where('tenant_id', $tenantId)
        ->sum('penalty');

    $totalDel = DB::table('delays')
        ->where('driver_controllable', true)
        ->where('tenant_id', $tenantId)
        ->sum('penalty');

    $out = [];
    foreach ($rows as $r) {
        $rej = (float) ($r->sum_rejection_penalties ?? 0);
        $del = (float) ($r->sum_delay_penalties ?? 0);
        $saf = (float) ($r->avg_safety_score ?? 0);
        $sev = (int)   ($r->severe_alerts ?? 0);

        // percentage scores
        $accScore = $totalRej > 0
            ? 100.0 - ($rej * 100.0 / $totalRej)
            : 100.0;
        $otScore  = $totalDel > 0
            ? 100.0 - ($del * 100.0 / $totalDel)
            : 100.0;

        $out[] = [
            'driver_name'        => $r->driver_name,
            'acceptance_score'   => round($accScore, 2),
            'on_time_score'      => round($otScore, 2),
            'safety_score'       => round($saf, 2),
            'severe_alerts'      => $sev,
        ];
    }

    // sort by overall if you like...
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
