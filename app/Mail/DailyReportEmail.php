<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\PerformanceMetricRule;
use App\Services\Performance\PerformanceCalculationsService;
use App\Services\Summaries\SafetyDataService;
use App\Services\Summaries\PerformanceDataService;
use App\Services\Summaries\MaintenanceBreakdownService;

class DailyReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected int    $tenantId;
    public    string $reportDate;
    public    string $userName;
    public    string $logoUrl;
    public    string $logoCid;

    // Yesterday's data
    public    array  $performanceMain;
    public    array  $performanceRolling;
    public    float  $mvtsPercent;
    public    array  $safetyAggregate;
    public    string $yesterdayOperationalExcellenceScore; // Add this line
    
    // T6W data
    public    array  $t6wPerformanceMain;
    public    array  $t6wPerformanceRolling;
    public    array  $t6wSafetyAggregate;
    public    array  $safetyRates;
    public    array  $safetyRatings;
    public    array  $performanceRatings;
    public    string $operationalExcellenceScore;
    public    string $overallSafetyRating;
    
    // Track data availability
    public    array  $dataAvailability;

    protected SafetyDataService $safetyDataService;
    protected PerformanceDataService $performanceDataService;
    protected MaintenanceBreakdownService $maintenanceBreakdownService;
    protected PerformanceCalculationsService $performanceCalculationsService;

    public function __construct(
        int $tenantId, 
        string $userName = 'User',
        SafetyDataService $safetyDataService = null,
        PerformanceDataService $performanceDataService = null,
        MaintenanceBreakdownService $maintenanceBreakdownService = null,
        PerformanceCalculationsService $performanceCalculationsService = null
    ) {
        $this->tenantId = $tenantId;
        $this->userName = $userName;
        
        $this->safetyDataService = $safetyDataService ?? new SafetyDataService();
        $this->performanceCalculationsService = $performanceCalculationsService ?? new PerformanceCalculationsService();
        $this->maintenanceBreakdownService = $maintenanceBreakdownService ?? new MaintenanceBreakdownService();
        // Fix: Pass required dependencies to PerformanceDataService
        $this->performanceDataService = $performanceDataService ?? new PerformanceDataService(
            $this->performanceCalculationsService,
            $this->maintenanceBreakdownService
        );
        
        // Initialize data availability tracking
        $this->dataAvailability = [
            'performance' => false,
            'safety' => false
        ];

        // Calculate yesterday's date range
        $now = Carbon::now();
        $isSunday = $now->dayOfWeek === 0; // 0 = Sunday in Carbon
        
        // Yesterday date range
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();
        
        // T6W date range (6 weeks)
        $t6wStart = $now->copy()->modify('last sunday');
        if ($isSunday) {
            $t6wStart->subWeek();
        }
        $t6wStart = $t6wStart->subWeeks(5)->startOfDay();
        
        $t6wEnd = $now->copy()->modify('this saturday');
        if ($isSunday) {
            $t6wEnd->subWeek();
        }
        $t6wEnd = $t6wEnd->endOfDay();
        
        $this->reportDate = Carbon::yesterday()->format('M d, Y');
        
        // Collect data for both time periods
        $this->collectData($yesterdayStart, $yesterdayEnd, $t6wStart, $t6wEnd);
    }

    protected function collectData(Carbon $yesterdayStart, Carbon $yesterdayEnd, Carbon $t6wStart, Carbon $t6wEnd): void
    {
        $rule = PerformanceMetricRule::first();
        
        // ─── SAFETY DATA - YESTERDAY ───────────────────────────────────────────────
        // Get aggregate safety data
        $yesterdaySafetyAggregate = $this->safetyDataService->getAggregateSafetyData($yesterdayStart, $yesterdayEnd);
                
        // Check if safety data exists
        $hasSafetyData = $yesterdaySafetyAggregate && 
                         ($yesterdaySafetyAggregate->total_minutes_analyzed ?? 0) > 0;
        $this->dataAvailability['safety'] = $hasSafetyData;
        
        // ─── SAFETY DATA - T6W ───────────────────────────────────────────────────
        // Get aggregate safety data for T6W
        $t6wSafetyAggregate = $this->safetyDataService->getAggregateSafetyData($t6wStart, $t6wEnd);
        
        // Get infractions data for T6W (not needed for the email template)
        $t6wSafetyInfractions = $this->safetyDataService->getInfractionsData($t6wStart, $t6wEnd);
        
        // Calculate safety rates and ratings for T6W
        $totalHours = ($t6wSafetyAggregate->total_minutes_analyzed ?? 0) / 60;
        $safetyRates = $this->safetyDataService->calculateViolationRates($t6wSafetyAggregate, $totalHours);
        $safetyRatings = $this->safetyDataService->calculateSafetyRatings($safetyRates, $rule);
        
        // Calculate overall safety rating for T6W
        $overallSafetyRating = $this->calculateOverallSafetyRating($safetyRatings);
        
        // ─── PERFORMANCE DATA - YESTERDAY ───────────────────────────────────────────
        // Get main performance data for yesterday
        $yesterdayPerformanceMain = $this->performanceDataService->getMainPerformanceData($yesterdayStart, $yesterdayEnd);
        
        // Get rolling performance data for yesterday
        $yesterdayPerformanceRolling = $this->performanceDataService->getRollingPerformanceData($yesterdayStart, $yesterdayEnd);
        
        // Check if performance data exists
        $hasPerformanceData = $yesterdayPerformanceMain && (
            ($yesterdayPerformanceMain->average_acceptance !== null && $yesterdayPerformanceMain->average_acceptance > 0) || 
            ($yesterdayPerformanceMain->average_on_time !== null && $yesterdayPerformanceMain->average_on_time > 0) || 
            ($yesterdayPerformanceMain->meets_safety_bonus_criteria !== null && $yesterdayPerformanceMain->meets_safety_bonus_criteria > 0)
        );
        $this->dataAvailability['performance'] = $hasPerformanceData;
        
        // ─── PERFORMANCE DATA - T6W ───────────────────────────────────────────
        // Get main performance data for T6W
        $t6wPerformanceMain = $this->performanceDataService->getMainPerformanceData($t6wStart, $t6wEnd);
        
        // Get rolling performance data for T6W
        $t6wPerformanceRolling = $this->performanceDataService->getRollingPerformanceData($t6wStart, $t6wEnd);
        
        // ─── MVtS CALCULATION - YESTERDAY ───────────────────────────────────────────────────
        // Get QS invoice amount and total miles for MVtS calculation
        $yesterdayQsInvoiceAmount = $this->maintenanceBreakdownService->getQSInvoiceAmount($yesterdayStart, $yesterdayEnd);
        $yesterdayTotalMiles = $this->maintenanceBreakdownService->getTotalMiles($yesterdayStart, $yesterdayEnd);
        $yesterdayQsMVtS = $this->maintenanceBreakdownService->calculateQSMVtS($yesterdayQsInvoiceAmount, $yesterdayTotalMiles) * 100;
        
        // ─── MVtS CALCULATION - T6W ───────────────────────────────────────────────────
        // Get QS invoice amount and total miles for T6W MVtS calculation
        $t6wQsInvoiceAmount = $this->maintenanceBreakdownService->getQSInvoiceAmount($t6wStart, $t6wEnd);
        $t6wTotalMiles = $this->maintenanceBreakdownService->getTotalMiles($t6wStart, $t6wEnd);
        $t6wQsMVtS = $this->maintenanceBreakdownService->calculateQSMVtS($t6wQsInvoiceAmount, $t6wTotalMiles) * 100;
        
        // ─── PERFORMANCE RATINGS - T6W ────────────────────────────────────────────────
        $performanceRatings = $this->performanceDataService->calculateRatings(
            $t6wPerformanceMain, 
            $t6wPerformanceRolling, 
            ['qs_MVtS' => $t6wQsMVtS], 
            $rule
        );
        
        // ─── OPERATIONAL EXCELLENCE SCORE - T6W ─────────────────────────────────────
        $operationalExcellenceScore = $this->calculateOperationalExcellenceScore($performanceRatings);
        
        // ─── PERFORMANCE RATINGS - YESTERDAY ────────────────────────────────────────────────
        $yesterdayPerformanceRatings = $this->performanceDataService->calculateRatings(
            $yesterdayPerformanceMain, 
            $yesterdayPerformanceRolling, 
            ['qs_MVtS' => $yesterdayQsMVtS], 
            $rule
        );
        
        // ─── OPERATIONAL EXCELLENCE SCORE - YESTERDAY ─────────────────────────────────────
        $yesterdayOperationalExcellenceScore = $this->calculateOperationalExcellenceScore($yesterdayPerformanceRatings);
        
        // ─── ASSIGN TO PROPERTIES ───────────────────────────────────────────────
        // Yesterday's data
        $this->performanceMain = (array)$yesterdayPerformanceMain;
        $this->performanceRolling = (array)$yesterdayPerformanceRolling;
        $this->mvtsPercent = round($yesterdayQsMVtS, 2);
        $this->safetyAggregate = (array)$yesterdaySafetyAggregate;
        $this->yesterdayOperationalExcellenceScore = $yesterdayOperationalExcellenceScore;
        
        // T6W data
        $this->t6wPerformanceMain = (array)$t6wPerformanceMain;
        $this->t6wPerformanceRolling = (array)$t6wPerformanceRolling;
        $this->t6wSafetyAggregate = (array)$t6wSafetyAggregate;
        $this->safetyRates = $safetyRates;
        $this->safetyRatings = $safetyRatings;
        $this->performanceRatings = $performanceRatings;
        $this->operationalExcellenceScore = $operationalExcellenceScore;
        $this->overallSafetyRating = $overallSafetyRating;
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
    
    /**
     * Calculate the overall safety rating based on individual safety ratings
     * 
     * @param array $ratings The safety ratings array
     * @return string The formatted overall safety rating
     */
    private function calculateOverallSafetyRating(array $ratings): string
    {
        $ratingValues = [
            'gold' => 3,
            'silver' => 2,
            'not_eligible' => 1,
        ];

        // Get all safety ratings as an array
        $safetyRatingKeys = [
            'traffic_light_violation',
            'speeding_violations',
            'following_distance',
            'driver_distraction',
            'sign_violations'
        ];

        // Find the best rating
        $bestRating = 'gold';
        foreach ($safetyRatingKeys as $key) {
            if (isset($ratings[$key]) && isset($ratingValues[$ratings[$key]]) && 
                $ratingValues[$ratings[$key]] < $ratingValues[$bestRating]) {
                $bestRating = $ratings[$key];
            }
        }
        
        return match ($bestRating) {
            'gold' => 'Gold Tier',
            'silver' => 'Silver Tier',
            'not_eligible' => 'Not Eligible',
            default => 'Not Available',
        };
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Daily Report ({$this->reportDate})");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily-report');
    }

    public function build()
    {
        // Read the PNG bytes
        $pngData = file_get_contents(public_path('images/logo.png'));
        
        // Embed it and get the CID
        $this->logoCid = $this->embedData($pngData, 'logo.png', 'image/png');
        
        return $this;
    }

    /**
     * Embed raw data in the message as an attachment.
     *
     * @param  string  $data
     * @param  string  $name
     * @param  string  $contentType
     * @return string
     */
    protected function embedData($data, $name, $contentType)
    {
        $this->attachData($data, $name, [
            'mime' => $contentType,
        ]);
        
        return 'cid:' . $name;
    }
}
