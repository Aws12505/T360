<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PerformanceMetricRule;
use App\Services\Performance\PerformanceCalculationsService;

class DailyReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected int    $tenantId;
    public    string $reportDate;
    public    string $userName; // Add this property for the user's name

    public    array  $performanceMain;
    public    array  $performanceRolling;
    public    float  $mvtsPercent;
    public    array  $performanceRatings;
    public    string $operationalExcellenceScore;

    public    array  $safetyAggregate;
    public    array  $safetyRates;
    public    array  $safetyRatings;
    public    array  $safetyInfractions;
    
    // Add new property to track data availability
    public    array  $dataAvailability;

    public function __construct(int $tenantId, string $userName = 'User')
    {
        $this->tenantId = $tenantId;
        $this->userName = $userName; // Store the user's name

        // ─── Yesterday's Data ────────────────────────────────────────────────────
        $yesterday = Carbon::yesterday();
        $start = $yesterday->copy()->startOfDay();
        $end = $yesterday->copy()->endOfDay();

        $this->reportDate = $yesterday->format('M d, Y');
        
        // Initialize data availability tracking
        $this->dataAvailability = [
            'performance' => false,
            'safety' => false
        ];

        $this->collectData($start, $end);
    }

    protected function collectData(Carbon $start, Carbon $end): void
    {
        $rule = PerformanceMetricRule::first();
        $calc = new PerformanceCalculationsService();

        // ─── PERFORMANCE MAIN ───────────────────────────────────────────────────
        $main = DB::table('performances')
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('date', [$start, $end])
            ->selectRaw("
                AVG(acceptance)         AS avg_acceptance,
                AVG(on_time)            AS avg_on_time,
                CASE WHEN SUM(meets_safety_bonus_criteria) >= COUNT(*)/2 
                     THEN 1 ELSE 0 END AS meets_safety
            ")
            ->first();
            
        // Check if performance data exists
        $hasPerformanceData = $main && (
            ($main->avg_acceptance !== null && $main->avg_acceptance > 0) || 
            ($main->avg_on_time !== null && $main->avg_on_time > 0) || 
            ($main->meets_safety !== null && $main->meets_safety > 0)
        );
        
        $this->dataAvailability['performance'] = $hasPerformanceData;

        // ─── PERFORMANCE ROLLING ────────────────────────────────────────────────
        $roll = DB::table('performances')
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('date', [$start, $end])
            ->selectRaw("
                SUM(open_boc)           AS sum_open_boc,
                SUM(vcr_preventable)    AS sum_vcr_preventable,
                SUM(vmcr_p)             AS sum_vmcr_p
            ")
            ->first();

        // ─── MVtS WITH CANCELLATION FILTER ──────────────────────────────────────
        $qsInvoice = DB::table('repair_orders')
            ->leftJoin('wo_statuses', 'repair_orders.wo_status_id', '=', 'wo_statuses.id')
            ->where('repair_orders.tenant_id', $this->tenantId)
            ->where('repair_orders.on_qs', 'yes')
            ->whereBetween('repair_orders.qs_invoice_date', [$start, $end])
            ->where(function($q) {
                $q->where('wo_statuses.name', '!=', 'Canceled');
            })
            ->sum('repair_orders.invoice_amount') ?: 0;

        $totalMiles = DB::table('miles_driven')
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('week_start_date', [$start, $end])
            ->sum('miles') ?: 0;

        $divisor     = $rule->mvts_divisor ?: 0.135;
        $mvtsRaw     = $totalMiles > 0
            ? ($qsInvoice / $totalMiles) / $divisor * 100
            : 0;
        $mvtsPercent = round($mvtsRaw, 2);

        // ─── PERFORMANCE RATINGS ────────────────────────────────────────────────
        $ratings = [
            'average_acceptance'                => $calc->getRating($main->avg_acceptance,  $rule, 'acceptance'),
            'average_on_time'                   => $calc->getRating($main->avg_on_time,       $rule, 'on_time'),
            'average_maintenance_variance_to_spend' => $calc->getRating($mvtsRaw,                $rule, 'maintenance_variance'),
            'open_boc'                          => $calc->getRating($roll->sum_open_boc,      $rule, 'open_boc'),
            'vcr_preventable'                   => $calc->getRating($roll->sum_vcr_preventable,$rule,'vcr_preventable'),
            'vmcr_p'                            => $calc->getRating($roll->sum_vmcr_p,         $rule,'vmcr_p'),
            'meets_safety_bonus_criteria'       => $calc->getSafetyBonusRating($main->meets_safety, $rule),
        ];

        // ─── OPERATIONAL EXCELLENCE SCORE ─────────────────────────────────────
        $this->operationalExcellenceScore = $this->calculateOperationalExcellenceScore($ratings);

        // ─── SAFETY AGGREGATE ──────────────────────────────────────────────────
        $agg = DB::table('safety_data')
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('date', [$start, $end])
            ->selectRaw("
                SUM(traffic_light_violation) AS traffic_light_violation,
                SUM(speeding_violations)     AS speeding_violations,
                SUM(following_distance)      AS following_distance,
                SUM(driver_distraction)      AS driver_distraction,
                SUM(sign_violations)         AS sign_violations,
                SUM(minutes_analyzed)        AS total_minutes,
                AVG(driver_score)            AS avg_driver_score
            ")
            ->first();
            
        // Check if safety data exists
        $hasSafetyData = $agg && $agg->total_minutes > 0;
        $this->dataAvailability['safety'] = $hasSafetyData;

        $hours = ($agg->total_minutes ?: 0) / 60;

        // rates per 1 000 h
        $rates = [];
        foreach ([
            'traffic_light_violation',
            'speeding_violations',
            'following_distance',
            'driver_distraction',
            'sign_violations',
        ] as $metric) {
            $agg->$metric = round($agg->$metric,0);
            $rates[$metric] = $hours > 0
                ? round(($agg->$metric / $hours) * 1000, 2)
                : 0;
        }

        // safety ratings
        $safetyRatings = [];
        foreach ($rates as $metric => $rate) {
            $prefix = match($metric) {
                'speeding_violations' => 'speeding_violation',
                'sign_violations'     => 'sign_violation',
                default               => $metric,
            };
            $safetyRatings[$metric] = $this->calcSafetyRating($rate, $rule, $prefix);
        }

        // infractions
        $infra = DB::table('safety_data')
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('date', [$start, $end])
            ->selectRaw("
                SUM(driver_star)           AS driver_star,
                SUM(potential_collision)   AS potential_collision,
                SUM(hard_braking)          AS hard_braking,
                SUM(hard_turn)             AS hard_turn,
                SUM(hard_acceleration)     AS hard_acceleration,
                SUM(u_turn)                AS u_turn,
                SUM(seatbelt_compliance)   AS seatbelt_compliance,
                SUM(camera_obstruction)    AS camera_obstruction,
                SUM(driver_drowsiness)     AS driver_drowsiness,
                SUM(weaving)               AS weaving,
                SUM(collision_warning)     AS collision_warning,
                SUM(backing)               AS backing,
                SUM(roadside_parking)      AS roadside_parking,
                SUM(high_g)                AS high_g
            ")
            ->first();

        // assign to properties
        $this->performanceMain     = (array)$main;
        $this->performanceRolling  = (array)$roll;
        $this->mvtsPercent         = $mvtsPercent;
        $this->performanceRatings  = $ratings;

        $this->safetyAggregate     = (array)$agg;
        $this->safetyRates         = $rates;
        $this->safetyRatings       = $safetyRatings;
        $this->safetyInfractions   = (array)$infra;
    }

    private function calculateOperationalExcellenceScore(array $ratings): string
    {
        $values = [
            'fantastic_plus' => 5,
            'fantastic'      => 4,
            'good'           => 3,
            'fair'           => 2,
            'poor'           => 1,
        ];
        $keys = [
            'average_acceptance',
            'average_on_time',
            'average_maintenance_variance_to_spend',
            'open_boc',
            'vcr_preventable',
            'vmcr_p',
        ];

        $best = 'fantastic_plus';
        foreach ($keys as $k) {
            if (isset($ratings[$k]) && $values[$ratings[$k]] < $values[$best]) {
                $best = $ratings[$k];
            }
        }

        return match ($best) {
            'fantastic_plus' => 'Fantastic +',
            'fantastic'      => 'Fantastic',
            'good'           => 'Good',
            'fair'           => 'Fair',
            'poor'           => 'Poor',
            default          => 'Not Available',
        };
    }

    private function compareValues($value, $threshold, $operator): bool
    {
        return match ($operator) {
            'less'          => $value < $threshold,
            'less_or_equal' => $value <= $threshold,
            'equal'         => $value == $threshold,
            'more_or_equal'=> $value >= $threshold,
            'more'          => $value > $threshold,
            default         => false,
        };
    }

    private function calcSafetyRating(float $rate, PerformanceMetricRule $rule, string $prefix): string
    {
        $goldTh = $rule->{"{$prefix}_gold"} ?? null;
        $goldOp = $rule->{"{$prefix}_gold_operator"} ?? null;
        if ($goldTh !== null && $goldOp && $this->compareValues($rate, $goldTh, $goldOp)) {
            return 'gold';
        }

        $silverTh = $rule->{"{$prefix}_silver"} ?? null;
        $silverOp = $rule->{"{$prefix}_silver_operator"} ?? null;
        if ($silverTh !== null && $silverOp && $this->compareValues($rate, $silverTh, $silverOp)) {
            return 'silver';
        }

        return 'not_eligible';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Daily Report ({$this->reportDate})");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily-report');
    }
}
