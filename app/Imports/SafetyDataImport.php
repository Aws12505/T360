<?php

namespace App\Imports;

use App\Models\SafetyData;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SafetyDataImport implements ToCollection, WithStartRow
{
    protected $date;
    protected $tenantId;

    public function __construct($date, $tenantId)
{
    $this->date = $date;
    $this->tenantId = $tenantId;
}


    public function startRow(): int
    {
        return 12; // Actual data starts *after* header row 11
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Stop on "total" row
            if (Str::lower(trim($row[0])) === 'total') {
                break;
            }

            // Convert "NA" and empty strings to null
            $clean = $row->map(function ($value) {
                $v = trim((string) $value);
                return ($v === '' || Str::lower($v) === 'na') ? null : $v;
            });

            // Map based on index (column A = index 0, B = 1, etc.)
            $data = [
'tenant_id' => $this->tenantId,
                'driver_name' => $clean[0] ?? null,
                'user_name' => $clean[1],
                'group' => $clean[2] ?? null,
                'group_hierarchy' => $clean[3] ?? null,
                'minutes_analyzed' => $clean[4],
                'green_minutes_percent' => $clean[5],
                'overspeeding_percent' => $clean[6],
                'driver_score' => $clean[7],
                'total_events_avg_fd_impact' => $clean[8],
                'average_following_distance_sec' => $clean[9],
                'average_following_distance_gz_impact' => $clean[10],
                'total_events' => $clean[11],
                'sign_violations' => $clean[12],
                'sign_violations_gz_impact' => $clean[13],
                'traffic_light_violation' => $clean[14],
                'traffic_light_violation_gz_impact' => $clean[15],
                'driver_distraction' => $clean[16],
                'driver_distraction_gz_impact' => $clean[17],
                'following_distance' => $clean[18],
                'following_distance_gz_impact' => $clean[19],
                'speeding_violations' => $clean[20],
                'speeding_violations_gz_impact' => $clean[21],
                'driver_drowsiness' => $clean[22],
                'driver_star' => $clean[23],
                'driver_star_gz_impact' => $clean[24],
                'vehicle_type' => $clean[25],
                'safety_normalisation_factor' => $clean[26],
                'date' => $this->date,
            ];

            // Insert or update using user_name + date
            SafetyData::updateOrCreate(
                [
                    'user_name' => $data['user_name'],
                    'date' => $data['date'],
                    'tenant_id' => $data['tenant_id'],
                ],
                $data
            );
        }
    }
}
