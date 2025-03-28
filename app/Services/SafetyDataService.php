<?php

namespace App\Services;

use App\Models\SafetyData;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SafetyDataImport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

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

    /**
     * Import safety data from an Excel file.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function importData($request)
    {
        $tenantId = Auth::user()->tenant_id ?? $request->input('tenant_id');
        $request->merge(['tenant_id' => $tenantId]);
        $request->validate([
            'csv_file'  => 'required|file|mimes:xlsx,xls',
            'date'      => 'required|date',
            'tenant_id' => 'required|exists:tenants,id',
        ]);
        Excel::import(new SafetyDataImport($request->date, $request->tenant_id), $request->file('csv_file'));
    }

    /**
     * Export safety data to a CSV file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportData()
    {
        $entries = SafetyData::with('tenant')->get();
        if ($entries->isEmpty()) {
            return back()->with('error', 'No safety data to export.');
        }
        $fileName = 'safety_data_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        $file = fopen($filePath, 'w');

        // Define headers (update as needed)
        $headers = [
            'tenant_name',
            'driver_name',
            'user_name',
            'group',
            'group_hierarchy',
            'minutes_analyzed',
            'green_minutes_percent',
            'overspeeding_percent',
            'driver_score',
            'total_events_avg_fd_impact',
            'average_following_distance_sec',
            'average_following_distance_gz_impact',
            'total_events',
            'high_g',
            'low_impact',
            'driver_initiated',
            'potential_collision',
            'sign_violations',
            'sign_violations_gz_impact',
            'traffic_light_violation',
            'traffic_light_violation_gz_impact',
            'u_turn',
            'u_turn_gz_impact',
            'hard_braking',
            'hard_braking_gz_impact',
            'hard_turn',
            'hard_turn_gz_impact',
            'hard_acceleration',
            'hard_acceleration_gz_impact',
            'driver_distraction',
            'driver_distraction_gz_impact',
            'following_distance',
            'following_distance_gz_impact',
            'speeding_violations',
            'speeding_violations_gz_impact',
            'seatbelt_compliance',
            'camera_obstruction',
            'driver_drowsiness',
            'weaving',
            'weaving_gz_impact',
            'collision_warning',
            'collision_warning_gz_impact',
            'requested_video',
            'backing',
            'roadside_parking',
            'driver_distracted_hard_brake',
            'following_distance_hard_brake',
            'driver_distracted_following_distance',
            'driver_star',
            'driver_star_gz_impact',
            'vehicle_type',
            'safety_normalisation_factor',
            'date',
        ];
        fputcsv($file, $headers);
        foreach ($entries as $entry) {
            fputcsv($file, [
                $entry->tenant->name ?? '—',
                $entry->driver_name,
                $entry->user_name,
                $entry->group,
                $entry->group_hierarchy,
                $entry->minutes_analyzed,
                $entry->green_minutes_percent,
                $entry->overspeeding_percent,
                $entry->driver_score,
                $entry->total_events_avg_fd_impact,
                $entry->average_following_distance_sec,
                $entry->average_following_distance_gz_impact,
                $entry->total_events,
                $entry->high_g,
                $entry->low_impact,
                $entry->driver_initiated,
                $entry->potential_collision,
                $entry->sign_violations,
                $entry->sign_violations_gz_impact,
                $entry->traffic_light_violation,
                $entry->traffic_light_violation_gz_impact,
                $entry->u_turn,
                $entry->u_turn_gz_impact,
                $entry->hard_braking,
                $entry->hard_braking_gz_impact,
                $entry->hard_turn,
                $entry->hard_turn_gz_impact,
                $entry->hard_acceleration,
                $entry->hard_acceleration_gz_impact,
                $entry->driver_distraction,
                $entry->driver_distraction_gz_impact,
                $entry->following_distance,
                $entry->following_distance_gz_impact,
                $entry->speeding_violations,
                $entry->speeding_violations_gz_impact,
                $entry->seatbelt_compliance,
                $entry->camera_obstruction,
                $entry->driver_drowsiness,
                $entry->weaving,
                $entry->weaving_gz_impact,
                $entry->collision_warning,
                $entry->collision_warning_gz_impact,
                $entry->requested_video,
                $entry->backing,
                $entry->roadside_parking,
                $entry->driver_distracted_hard_brake,
                $entry->following_distance_hard_brake,
                $entry->driver_distracted_following_distance,
                $entry->driver_star,
                $entry->driver_star_gz_impact,
                $entry->vehicle_type,
                $entry->safety_normalisation_factor,
                Carbon::parse($entry->date)->format('Y-m-d'),
            ]);
        }
        fclose($file);
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}
