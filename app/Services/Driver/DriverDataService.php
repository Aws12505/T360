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

    public function __construct(FilteringService $filteringService)
    {
        $this->filteringService = $filteringService;
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
        // No date filtering → use earliest to latest possible dates
        $totalRejectionPenaltiesQuery = DB::table('rejections')
            ->where('driver_controllable', true)
            ->selectRaw('SUM(penalty) as total_rejection_penalties');

        $this->applyTenantFilter($totalRejectionPenaltiesQuery);
        $totalRejectionPenalties = $totalRejectionPenaltiesQuery->first()->total_rejection_penalties ?? 0;

        $totalDelayPenaltiesQuery = DB::table('delays')
            ->where('driver_controllable', true)
            ->selectRaw('SUM(penalty) as total_delay_penalties');

        $this->applyTenantFilter($totalDelayPenaltiesQuery);
        $totalDelayPenalties = $totalDelayPenaltiesQuery->first()->total_delay_penalties ?? 0;

        $driversQuery = DB::table('drivers')
            ->select('id', 'first_name', 'last_name', 'netradyne_user_name');

        $this->applyTenantFilter($driversQuery);
        $drivers = $driversQuery->get();

        $driversOverallScores = [];

        foreach ($drivers as $driver) {
            $driverName = $driver->first_name . ' ' . $driver->last_name;
            $netradyneUserName = $driver->netradyne_user_name;

            $safetyScoreQuery = DB::table('safety_data')
                ->where(function($query) use ($driverName, $netradyneUserName) {
                    $query->where('user_name', $netradyneUserName)
                          ->orWhere('driver_name', $driverName);
                })
                ->selectRaw("AVG(driver_score) as safety_score, SUM(minutes_analyzed) as minutes_analyzed");

            $this->applyTenantFilter($safetyScoreQuery);
            $safetyScore = $safetyScoreQuery->first()->safety_score ?? 0;
            $minutesAnalyzed = $safetyScoreQuery->first()->minutes_analyzed ?? 0;

            if ($minutesAnalyzed == 0) {
                continue;
            }

            $rejectionPenaltiesQuery = DB::table('rejections')
                ->where('driver_name', $driverName)
                ->where('driver_controllable', true)
                ->selectRaw('SUM(penalty) as total_rejection_penalties');

            $this->applyTenantFilter($rejectionPenaltiesQuery);
            $rejectionPenalties = $rejectionPenaltiesQuery->first()->total_rejection_penalties ?? 0;

            $delayPenaltiesQuery = DB::table('delays')
                ->where('driver_name', $driverName)
                ->where('driver_controllable', true)
                ->selectRaw('SUM(penalty) as total_delay_penalties');

            $this->applyTenantFilter($delayPenaltiesQuery);
            $delayPenalties = $delayPenaltiesQuery->first()->total_delay_penalties ?? 0;

            $acceptanceScore = 100;
            if ($totalRejectionPenalties > 0) {
                $acceptanceScore = 100 - ($rejectionPenalties * 100 / $totalRejectionPenalties);
            }

            $onTimeScore = 100;
            if ($totalDelayPenalties > 0) {
                $onTimeScore = 100 - ($delayPenalties * 100 / $totalDelayPenalties);
            }

            $safetyScoreNormalized = $safetyScore * 100 / 1050;

            $overallScore = ($acceptanceScore + $onTimeScore + $safetyScoreNormalized) / 3;

            $driversOverallScores[] = [
                'driver_name' => $driverName,
                'acceptance_score' => round($acceptanceScore, 2),
                'on_time_score' => round($onTimeScore, 2),
                'safety_score' => round($safetyScoreNormalized, 2),
                'overall_score' => round($overallScore, 2),
                'raw_safety_score' => round($safetyScore, 2),
                'rejection_penalties' => $rejectionPenalties,
                'delay_penalties' => $delayPenalties,
                'minutes_analyzed' => $minutesAnalyzed,
            ];
        }

        usort($driversOverallScores, function($a, $b) {
            return $b['overall_score'] <=> $a['overall_score'];
        });

        return [
            'drivers' => $driversOverallScores,
        ];
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

    /**
     * Helper to apply tenant filtering — you already have this method
     */
    private function applyTenantFilter($query)
    {
            $query->where('tenant_id', Auth::guard('driver')->user()->tenant_id);
    }
}
