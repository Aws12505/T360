<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\SMSScoresThresholds;
use App\Models\SMSCoachingTemplates;
use App\Services\Driver\DriverDataService;
use App\Services\Summaries\PerformanceDataService;
use Illuminate\Support\Facades\Log;

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
        $tenantId = (int)$this->argument('tenantId');

        // 1) thresholds
        $th = SMSScoresThresholds::where('tenant_id', $tenantId)->first();
        if (! $th) {
            $this->error("No thresholds for tenant {$tenantId}");
            return 1;
        }

        // 2) driver metrics
        $data    = $this->driverService
                        ->forTenant($tenantId)
                        ->getDriversOverallPerformanceCoaching();
        $drivers = $data['drivers'] ?? [];

        if (empty($drivers)) {
            $this->info("No drivers for tenant {$tenantId}");
            return 0;
        }

        // 2a) eager-load all templates for this tenant, index by "acc|ontime|green|alerts"
        $templates = SMSCoachingTemplates::where('tenant_id', $tenantId)
            ->get()
            ->keyBy(fn($t) => implode('|', [
                $t->acceptance,
                $t->ontime,
                $t->greenzone,
                $t->severe_alerts,
            ]));

        // 3) company averages: last week acceptance & on-time, green = avg safety_score
        $now  = Carbon::now();
        $oneW = $now->copy()->subWeek();
        $m    = $this->perfService->getMainPerformanceData($oneW, $now);

        $compAcc   = round($m->average_acceptance ?? 0, 2);
        $compOT    = round($m->average_on_time   ?? 0, 2);
        $compGreen = round(collect($drivers)->avg('safety_score') ?? 0, 2);

        // 4) loop & send
        foreach ($drivers as $row) {
            $name = $row['driver_name'];

            // determine categories
            $cats = [
                'acceptance'    => $this->cat(
                    $row['acceptance_score'],
                    $th->acceptance_good,
                    $th->acceptance_minor_improvement
                ),
                'ontime'        => $this->cat(
                    $row['on_time_score'],
                    $th->on_time_good,
                    $th->on_time_minor_improvement
                ),
                'greenzone'     => $this->cat(
                    $row['safety_score'],
                    $th->greenzone_score_good,
                    $th->greenzone_score_minor_improvement
                ),
                'severe_alerts' => $this->revCat(
                    $row['severe_alerts'],
                    $th->severe_alerts_good,
                    $th->severe_alerts_minor_improvement
                ),
            ];

            // build the lookup key
            $key = implode('|', [
                $cats['acceptance'],
                $cats['ontime'],
                $cats['greenzone'],
                $cats['severe_alerts'],
            ]);

            // one O(1) lookup instead of N queries
            $tmpl = $templates->get($key);

            if (! $tmpl) {
                $this->warn("{$name}: no template for ".json_encode($cats));
                continue;
            }

            $msg = $this->fillPlaceholders(
                $tmpl->coaching_message,
                $row,
                compact('compAcc','compOT','compGreen')
            );

            // dispatch your SMS job here; for now:
            Log::info("SMS to {$name}: {$msg}");
        }

        $this->info("Done.");
        return 0;
    }

    protected function cat(float $v, float $g, float $m): string
    {
        return $v >= $g
             ? 'good'
             : ($v >= $m ? 'minor_improvement' : 'bad');
    }

    protected function revCat(int $v, int $g, int $m): string
    {
        return $v <= $g
             ? 'good'
             : ($v <= $m ? 'minor_improvement' : 'bad');
    }

    protected function fillPlaceholders(string $tpl, array $d, array $c): string
    {
        $map = [
            '{driver_first_name}'       => explode(' ', $d['driver_name'])[0],
            '{driver_last_name}'        => explode(' ', $d['driver_name'])[1] ?? '',
            '{driver_acceptance_score}' => $d['acceptance_score'],
            '{driver_ontime_score}'     => $d['on_time_score'],
            '{driver_greenzone_score}'  => $d['safety_score'],
            '{driver_severe_alerts}'    => $d['severe_alerts'],
            '{company_avg_acceptance}'  => $c['compAcc'],
            '{company_avg_ontime}'      => $c['compOT'],
            '{company_avg_greenzone}'   => $c['compGreen'],
        ];

        return strtr($tpl, $map);
    }
}
