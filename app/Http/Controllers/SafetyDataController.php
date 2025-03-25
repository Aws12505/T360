<?php

namespace App\Http\Controllers;

use App\Models\SafetyData;
use App\Models\Tenant;
use App\Imports\SafetyDataImport;
//use App\Exports\SafetyDataExport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;

class SafetyDataController extends Controller
{
    public function index(Request $request)
    {
        $entries = SafetyData::with('tenant')->get();
        if(is_null(Auth::user()->tenant_id)){
            $isSuperAdmin = true;}
            else {$isSuperAdmin=false;}
            $tenants = [];
                if ($isSuperAdmin) {
                    $tenants = Tenant::all();
                }
                if ($isSuperAdmin) {
                    $tenantSlug = null;
                } else {
                    $tenantSlug = Auth::user()->tenant->slug;
                }
        return Inertia::render('Safety/Safety', [
            'entries' => $entries,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request)
    {

        $tenantId = Auth::user()->tenant_id ?? $request->input('tenant_id');
        $request->merge(['tenant_id' => $tenantId]);
        $data = $this->validateData($request);
        SafetyData::create(
            $data
        );

        return redirect()->back()->with('success', 'Entry created successfully.');
    }

    public function update(Request $request, $tenantSlug ,$id)
    {
        $data = $this->validateData($request);
        $date['tenant_id']= Auth::user()->tenant_id;
        $entry = SafetyData::findOrFail($id);
        $entry->update($data);

        return redirect()->back()->with('success', 'Entry updated successfully.');
    }
    public function updateAdmin(Request $request, $id)
    {
        $data = $this->validateData($request);

        $entry = SafetyData::findOrFail($id);
        $entry->update($data);

        return redirect()->back()->with('success', 'Entry updated successfully.');
    }
    public function destroy(Request $request, $tenantSlug = null, $id)
    {
        $entry = SafetyData::findOrFail($id);
        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }
    public function destroyAdmin(Request $request, $id)
    {
        $entry = SafetyData::findOrFail($id);
        $entry->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }
    public function import(Request $request)
{
    $tenantId = Auth::user()->tenant_id ?? $request->input('tenant_id');
    $request->merge(['tenant_id' => $tenantId]);

    // Validate the request
    $request->validate([
        'csv_file' => 'required|file|mimes:xlsx,xls',
        'date' => 'required|date',
        'tenant_id' => 'required|exists:tenants,id',
    ]);

    Excel::import(new SafetyDataImport($request->date,$request->tenant_id), $request->file('csv_file'));

    return redirect()->back()->with('success', 'Safety data imported successfully.');
}

public function export()
{
    $query = Auth::user()->tenant_id === null
        ? SafetyData::with('tenant')
        : SafetyData::where('tenant_id', Auth::user()->tenant_id)->with('tenant');

    $entries = $query->get();

    if ($entries->isEmpty()) {
        return back()->with('error', 'No safety data to export.');
    }

    $fileName = 'safety_data_' . Str::random(8) . '.csv';
    $filePath = public_path($fileName);
    $file = fopen($filePath, 'w');

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
        'sign_violations',
        'sign_violations_gz_impact',
        'traffic_light_violation',
        'traffic_light_violation_gz_impact',
        'driver_distraction',
        'driver_distraction_gz_impact',
        'following_distance',
        'following_distance_gz_impact',
        'speeding_violations',
        'speeding_violations_gz_impact',
        'driver_drowsiness',
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
            $entry->sign_violations,
            $entry->sign_violations_gz_impact,
            $entry->traffic_light_violation,
            $entry->traffic_light_violation_gz_impact,
            $entry->driver_distraction,
            $entry->driver_distraction_gz_impact,
            $entry->following_distance,
            $entry->following_distance_gz_impact,
            $entry->speeding_violations,
            $entry->speeding_violations_gz_impact,
            $entry->driver_drowsiness,
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


    private function validateData(Request $request)
    {
        return $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'driver_name' => ['nullable', 'string'],
            'user_name' => ['required', 'string'],
            'group' => ['nullable', 'string'],
            'group_hierarchy' => ['nullable', 'string'],
            'minutes_analyzed' => ['nullable', 'numeric'],
            'green_minutes_percent' => ['nullable', 'numeric'],
            'overspeeding_percent' => ['nullable', 'numeric'],
            'driver_score' => ['nullable', 'numeric'],
            'total_events' => ['nullable', 'numeric'],
            'average_following_distance' => ['nullable', 'numeric'],
            'sign_violations' => ['nullable', 'integer'],
            'traffic_light_violation' => ['nullable', 'integer'],
            'driver_distraction' => ['nullable', 'integer'],
            'following_distance' => ['nullable', 'integer'],
            'speeding_violations' => ['nullable', 'integer'],
            'driver_drowsiness' => ['nullable', 'integer'],
            'driver_star' => ['nullable', 'integer'],
            'vehicle_type' => ['nullable', 'string'],
            'safety_normalisation_factor' => ['nullable', 'numeric'],
            'date' => ['required', 'date'],
        ]);
    }
}
