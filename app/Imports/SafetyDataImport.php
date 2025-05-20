<?php

namespace App\Imports;

use App\Models\SafetyData;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class SafetyDataImport
 *
 * This class handles the import of safety data from an Excel file.
 * It implements the ToCollection and WithStartRow interfaces provided by Maatwebsite\Excel.
 *
 * The import process does the following:
 *   - Starts reading from row 12 (after header rows).
 *   - Iterates over each row, cleaning data and mapping values to their corresponding fields.
 *   - If the first cell in a row (converted to lowercase) is "total", the import is stopped.
 *   - Ensures each row has exactly 52 columns by padding missing values with null.
 *   - Uses the updateOrCreate method to insert or update records in the safety_data table based on user_name, date, and tenant_id.
 *
 * Usage:
 *   Excel::import(new SafetyDataImport($date, $tenantId), $file);
 */
class SafetyDataImport implements ToCollection, WithStartRow, WithChunkReading, WithBatchInserts
{
    /**
     * The default date to use if a row does not contain a valid date.
     *
     * @var string
     */
    protected $date;

    /**
     * The tenant ID associated with the imported data.
     *
     * @var int
     */
    protected $tenantId;
    
    /**
     * Batch size for processing records
     * 
     * @var int
     */
    protected $batchSize = 500;

    /**
     * Constructor.
     *
     * @param string $date      Default date to use if no date is provided in the row.
     * @param int    $tenantId  The ID of the tenant to assign to the imported data.
     */
    public function __construct($date, $tenantId)
    {
        $this->date = $date;
        $this->tenantId = $tenantId;
    }

    /**
     * Specify the starting row for the import.
     *
     * @return int
     */
    public function startRow(): int
    {
        // Start importing after header rows (row 12)
        return 12;
    }
    
    /**
     * Specify chunk size for reading
     * 
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000; // Process 1000 rows at a time
    }
    
    /**
     * Specify batch size for inserts
     * 
     * @return int
     */
    public function batchSize(): int
    {
        return $this->batchSize; // Use the class property for batch size
    }

    /**
     * Process a collection of rows from the Excel file.
     *
     * @param Collection $rows  A collection of rows from the Excel file.
     * @return void
     * @throws \Exception If there is an error processing a row.
     */
    public function collection(Collection $rows)
    {
        // Set initial row index starting from the configured start row.
        $rowIndex = $this->startRow();
        
        // Prepare batch arrays for bulk operations
        $recordsToUpdate = [];
        $recordsToInsert = [];
        $processedKeys = [];
        
        // Cache for existing records to reduce DB queries
        $existingRecords = [];
        
        // Precompile lowercase string for comparison (minor optimization)
        $totalString = 'total';
        
        // Start a database transaction
        DB::beginTransaction();
        
        try {
            // Iterate over each row in the collection.
            foreach ($rows as $row) {
                // If the first cell (trimmed, lowercased) equals "total", stop processing further rows.
                $firstCell = isset($row[0]) ? Str::lower(trim((string)$row[0])) : '';
                if ($firstCell === $totalString) {
                    break;
                }
                
                // Skip empty rows
                if (empty($firstCell) && count(array_filter($row->toArray())) < 3) {
                    $rowIndex++;
                    continue;
                }
        
                // Clean the row data: trim, cast to string, and convert empty/NA values to null.
                $clean = $this->cleanRowData($row);
        
                // Retrieve the value from column index 51.
                $maybeDate = $clean[51] ?? null;
        
                // Convert the date
                $rowDate = $this->parseDate($maybeDate);
        
                // Ensure the row has exactly 52 columns by padding with null values if needed.
                while (count($clean) < 52) {
                    $clean->push(null);
                }
        
                // Map cleaned row values to their corresponding safety_data fields.
                $data = $this->mapRowToData($clean, $rowDate);
                
                // Create a unique key for this record
                $key = $data['user_name'] . '|' . $data['date'] . '|' . $data['tenant_id'];
                
                // Check if we've already processed this key in the current batch
                if (in_array($key, $processedKeys)) {
                    $rowIndex++;
                    continue;
                }
                
                $processedKeys[] = $key;
                
                // Check if record exists (using our cache first)
                $exists = false;
                
                if (!isset($existingRecords[$key])) {
                    // Only query the database when necessary
                    $exists = SafetyData::where([
                        'user_name' => $data['user_name'],
                        'date' => $data['date'],
                        'tenant_id' => $data['tenant_id'],
                    ])->exists();
                    
                    $existingRecords[$key] = $exists;
                } else {
                    $exists = $existingRecords[$key];
                }
                
                if ($exists) {
                    $recordsToUpdate[] = $data;
                } else {
                    $recordsToInsert[] = $data;
                }
                
                // Process in batches to avoid memory issues
                if (count($recordsToUpdate) + count($recordsToInsert) >= $this->batchSize) {
                    $this->processBatch($recordsToUpdate, $recordsToInsert);
                    $recordsToUpdate = [];
                    $recordsToInsert = [];
                }
        
                $rowIndex++;
            }
            
            // Process any remaining records
            if (count($recordsToUpdate) + count($recordsToInsert) > 0) {
                $this->processBatch($recordsToUpdate, $recordsToInsert);
            }
            
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
        }
    }
    
    /**
     * Clean row data by trimming and converting empty/NA values to null
     * 
     * @param Collection $row
     * @return Collection
     */
    protected function cleanRowData(Collection $row): Collection
    {
        return $row->map(function ($value) {
            if ($value === null) {
                return null;
            }
            
            $v = trim((string)$value);
            return ($v === '' || Str::lower($v) === 'na') ? null : $v;
        });
    }
    
    /**
     * Parse date from various formats
     * 
     * @param mixed $maybeDate
     * @return string
     */
    protected function parseDate($maybeDate): string
    {
        if (is_numeric($maybeDate)) {
            try {
                // If the cell is numeric, treat it as an Excel date serial.
                $converted = ExcelDate::excelToDateTimeObject($maybeDate);
                return Carbon::instance($converted)->format('Y-m-d');
            } catch (\Exception $e) {
                return $this->date;
            }
        } elseif ($maybeDate && strtotime($maybeDate)) {
            return date('Y-m-d', strtotime($maybeDate));
        } else {
            return $this->date;
        }
    }
    
    /**
     * Map row data to database fields
     * 
     * @param Collection $clean
     * @param string $rowDate
     * @return array
     */
    protected function mapRowToData(Collection $clean, string $rowDate): array
    {
        return [
            'tenant_id'                           => $this->tenantId,
            'driver_name'                         => $clean[0] ?? null,
            'user_name'                           => $clean[1],
            'group'                               => $clean[2] ?? null,
            'group_hierarchy'                     => $clean[3] ?? null,
            'minutes_analyzed'                    => $clean[4],
            'green_minutes_percent'               => $clean[5],
            'overspeeding_percent'                => $clean[6],
            'driver_score'                        => $clean[7],
            'total_events_avg_fd_impact'          => $clean[8],
            'average_following_distance_sec'      => $clean[9],
            'average_following_distance_gz_impact'=> $clean[10],
            'total_events'                        => $clean[11],
            'high_g'                              => $clean[12],
            'low_impact'                          => $clean[13],
            'driver_initiated'                    => $clean[14],
            'potential_collision'                 => $clean[15],
            'sign_violations'                     => $clean[16],
            'sign_violations_gz_impact'           => $clean[17],
            'traffic_light_violation'             => $clean[18],
            'traffic_light_violation_gz_impact'   => $clean[19],
            'u_turn'                              => $clean[20],
            'u_turn_gz_impact'                    => $clean[21],
            'hard_braking'                        => $clean[22],
            'hard_braking_gz_impact'              => $clean[23],
            'hard_turn'                           => $clean[24],
            'hard_turn_gz_impact'                 => $clean[25],
            'hard_acceleration'                   => $clean[26],
            'hard_acceleration_gz_impact'         => $clean[27],
            'driver_distraction'                  => $clean[28],
            'driver_distraction_gz_impact'        => $clean[29],
            'following_distance'                  => $clean[30],
            'following_distance_gz_impact'        => $clean[31],
            'speeding_violations'                 => $clean[32],
            'speeding_violations_gz_impact'       => $clean[33],
            'seatbelt_compliance'                 => $clean[34],
            'camera_obstruction'                  => $clean[35],
            'driver_drowsiness'                   => $clean[36],
            'weaving'                             => $clean[37],
            'weaving_gz_impact'                   => $clean[38],
            'collision_warning'                   => $clean[39],
            'collision_warning_gz_impact'         => $clean[40],
            'requested_video'                     => $clean[41],
            'backing'                             => $clean[42],
            'roadside_parking'                    => $clean[43],
            'driver_distracted_hard_brake'        => $clean[44],
            'following_distance_hard_brake'       => $clean[45],
            'driver_distracted_following_distance'=> $clean[46],
            'driver_star'                         => $clean[47],
            'driver_star_gz_impact'               => $clean[48],
            'vehicle_type'                        => $clean[49],
            'safety_normalisation_factor'         => $clean[50],
            'date'                                => $rowDate,
        ];
    }
    
    /**
     * Process a batch of records
     * 
     * @param array $recordsToUpdate
     * @param array $recordsToInsert
     * @return void
     */
    protected function processBatch(array $recordsToUpdate, array $recordsToInsert): void
    {
        // Handle updates
        foreach ($recordsToUpdate as $record) {
            SafetyData::updateOrCreate(
                [
                    'user_name' => $record['user_name'],
                    'date'      => $record['date'],
                    'tenant_id' => $record['tenant_id'],
                ],
                $record
            );
        }
        
        // Handle inserts in bulk if possible
        if (count($recordsToInsert) > 0) {
            SafetyData::insert($recordsToInsert);
        }
    }
}
