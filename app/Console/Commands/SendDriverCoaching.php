<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\SMSScoresThresholds;
use App\Models\SMSCoachingTemplates;
use App\Services\Driver\DriverDataService;
use App\Services\Summaries\PerformanceDataService;

class SendDriverCoaching extends Command
{
    protected $signature   = 'coaching:send {tenantId}';
    protected $description = 'Send SMS coaching messages for a given tenant';

    public function __construct(
        protected DriverDataService      $driverService,
        protected PerformanceDataService $perfService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantId   = (int)$this->argument('tenantId');
        $thresholds = SMSScoresThresholds::where('tenant_id', $tenantId)->first();
        if (! $thresholds) {
            $this->error("No thresholds for tenant {$tenantId}");
            return self::FAILURE;
        }
        // compute this week (Sun→Sat), roll back if today is Sunday
        // $now       = Carbon::now();
        // $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        // $weekEnd   = $now->copy()->endOfWeek(Carbon::SATURDAY);
        // if ($now->dayOfWeek === Carbon::SUNDAY) {
        //     $weekStart->subWeek();
        //     $weekEnd  ->subWeek();
        // }
        // ───────────────────────────────────────────────────────────────
        // for testing, fix your window here; in prod revert to Carbon logic
        $weekStart = Carbon::parse('2023-07-10');
        $weekEnd   = Carbon::now();
        // ───────────────────────────────────────────────────────────────

        $driverResult = $this->driverService
            ->forTenant($tenantId)
            ->getDriversOverallPerformanceCoaching($weekStart, $weekEnd);

        $drivers = $driverResult['drivers'] ?? [];
        if (empty($drivers)) {
            $this->info("No drivers for tenant {$tenantId}");
            return self::SUCCESS;
        }

        $templateMap = SMSCoachingTemplates::where('tenant_id', $tenantId)
            ->get()
            ->keyBy(fn($t) => implode('|', [
                $t->acceptance,
                $t->ontime,
                $t->greenzone,
                $t->severe_alerts,
            ]));

        $mainPerf = $this->perfService
            ->getMainPerformanceData($weekStart, $weekEnd);

        $compAcc   = round($mainPerf->average_acceptance ?? 0, 2);
        $compOT    = round($mainPerf->average_on_time   ?? 0, 2);
        $compGreen = round(collect($drivers)->avg('safety_score') ?? 0, 2);

        foreach ($drivers as $d) {
            $name  = $d['driver_name'];
            $phone = $d['mobile_phone'];

            // determine categories
            $cats = [
                'acceptance'    => $this->categorize(
                    $d['acceptance_score'],
                    $thresholds->acceptance_good,
                    $thresholds->acceptance_minor_improvement
                ),
                'ontime'        => $this->categorize(
                    $d['on_time_score'],
                    $thresholds->on_time_good,
                    $thresholds->on_time_minor_improvement
                ),
                'greenzone'     => $this->categorize(
                    $d['safety_score'],
                    $thresholds->greenzone_score_good,
                    $thresholds->greenzone_score_minor_improvement
                ),
                'severe_alerts' => $this->reverseCategorize(
                    $d['severe_alerts'],
                    $thresholds->severe_alerts_good,
                    $thresholds->severe_alerts_minor_improvement
                ),
            ];

            $key   = implode('|', $cats);
            $templ = $templateMap->get($key);

            if (! $templ) {
                $this->warn("{$name}: no template for ".json_encode($cats));
                continue;
            }

            $body = $this->fillTemplate(
                $templ->coaching_message,
                $d,
                compact('compAcc','compOT','compGreen')
            );

            // ─── NEW: print categories + raw scores ───────────────────────
            $this->info("Ratings & Scores:");
            $this->info("  acceptance    = {$cats['acceptance']} ({$d['acceptance_score']}%)");
            $this->info("  on-time       = {$cats['ontime']} ({$d['on_time_score']}%)");
            $this->info("  green-zone    = {$cats['greenzone']} ({$d['safety_score']})");
            $this->info("  severe_alerts = {$cats['severe_alerts']} ({$d['severe_alerts']})");
            // ───────────────────────────────────────────────────────────────

            $this->info("----");
            $this->info("To:   {$phone} ({$name})");
            $this->info("Body: {$body}");
        }

        $this->info('Done sending coaching SMS');
        return self::SUCCESS;
    }

    private function categorize(float $value, float $good, float $minor): string
    {
        if ($value >= $good) {
            return 'good';
        }
        if ($value >= $minor) {
            return 'minor_improvement';
        }
        return 'bad';
    }

    private function reverseCategorize(int $count, int $good, int $minor): string
    {
        if ($count <= $good) {
            return 'good';
        }
        if ($count <= $minor) {
            return 'minor_improvement';
        }
        return 'bad';
    }

    private function fillTemplate(string $template, array $d, array $c): string
    {
        [$first, $last] = array_pad(explode(' ', $d['driver_name'], 2), 2, '');

        $map = [
            '{driver_first_name}'       => $first,
            '{driver_last_name}'        => $last,
            '{driver_acceptance_score}' => $d['acceptance_score'],
            '{driver_ontime_score}'     => $d['on_time_score'],
            '{driver_greenzone_score}'  => $d['safety_score'],
            '{driver_severe_alerts}'    => $d['severe_alerts'],
            '{company_avg_acceptance}'  => $c['compAcc'],
            '{company_avg_ontime}'      => $c['compOT'],
            '{company_avg_greenzone}'   => $c['compGreen'],
        ];

        return strtr($template, $map);
    }
}
