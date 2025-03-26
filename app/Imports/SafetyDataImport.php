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
        return 12; // Start after header
    }

    public function collection(Collection $rows)
    {
        $rowIndex = $this->startRow(); // start from row 12
    
        foreach ($rows as $row) {
            if (Str::lower(trim($row[0])) === 'total') {
                break;
            }
    
            $clean = $row->map(function ($value) {
                $v = trim((string)$value);
                return ($v === '' || Str::lower($v) === 'na') ? null : $v;
            });
    
            $maybeDate = $clean[51] ?? null;
            $rowDate = ($maybeDate && strtotime($maybeDate)) ? date('Y-m-d', strtotime($maybeDate)) : $this->date;
    
            while (count($clean) < 52) {
                $clean->push(null);
            }
    
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
                'high_g' => $clean[12],
                'low_impact' => $clean[13],
                'driver_initiated' => $clean[14],
                'potential_collision' => $clean[15],
                'sign_violations' => $clean[16],
                'sign_violations_gz_impact' => $clean[17],
                'traffic_light_violation' => $clean[18],
                'traffic_light_violation_gz_impact' => $clean[19],
                'u_turn' => $clean[20],
                'u_turn_gz_impact' => $clean[21],
                'hard_braking' => $clean[22],
                'hard_braking_gz_impact' => $clean[23],
                'hard_turn' => $clean[24],
                'hard_turn_gz_impact' => $clean[25],
                'hard_acceleration' => $clean[26],
                'hard_acceleration_gz_impact' => $clean[27],
                'driver_distraction' => $clean[28],
                'driver_distraction_gz_impact' => $clean[29],
                'following_distance' => $clean[30],
                'following_distance_gz_impact' => $clean[31],
                'speeding_violations' => $clean[32],
                'speeding_violations_gz_impact' => $clean[33],
                'seatbelt_compliance' => $clean[34],
                'camera_obstruction' => $clean[35],
                'driver_drowsiness' => $clean[36],
                'weaving' => $clean[37],
                'weaving_gz_impact' => $clean[38],
                'collision_warning' => $clean[39],
                'collision_warning_gz_impact' => $clean[40],
                'requested_video' => $clean[41],
                'backing' => $clean[42],
                'roadside_parking' => $clean[43],
                'driver_distracted_hard_brake' => $clean[44],
                'following_distance_hard_brake' => $clean[45],
                'driver_distracted_following_distance' => $clean[46],
                'driver_star' => $clean[47],
                'driver_star_gz_impact' => $clean[48],
                'vehicle_type' => $clean[49],
                'safety_normalisation_factor' => $clean[50],
                'date' => $rowDate,
            ];
    
            try {
                SafetyData::updateOrCreate(
                    [
                        'user_name' => $data['user_name'],
                        'date' => $data['date'],
                        'tenant_id' => $data['tenant_id'],
                    ],
                    $data
                );
            } catch (\Throwable $e) {
                throw new \Exception("Error on row {$rowIndex} (Excel row): " . $e->getMessage());
            }
    
            $rowIndex++;
        }
    }
    
}
