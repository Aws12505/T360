<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\SMSScoresThresholds;
use App\Models\SMSCoachingTemplates;
use App\Services\Driver\DriverDataService;
use App\Services\Summaries\PerformanceDataService;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Log;

class SendDriverCoaching extends Command
{
    protected $signature   = 'coaching:send {tenantId}';
    protected $description = 'Send SMS coaching messages for a given tenant';

    public function __construct(
        protected DriverDataService      $driverService,
        protected PerformanceDataService $perfService,
        protected TwilioService          $twilioService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantId   = (int) $this->argument('tenantId');
        $thresholds = SMSScoresThresholds::where('tenant_id', $tenantId)->first();

        if (! $thresholds) {
            $this->error("No thresholds found for tenant {$tenantId}.");
            return self::FAILURE;
        }

        $now       = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $weekEnd   = $now->copy()->endOfWeek(Carbon::SATURDAY);
        if ($now->dayOfWeek === Carbon::SUNDAY) {
             $weekStart->subWeek();
             $weekEnd  ->subWeek();
        }


        $driverResult = $this->driverService
            ->forTenant($tenantId)
            ->getDriversOverallPerformanceCoaching($weekStart, $weekEnd);

        $drivers = $driverResult['drivers'] ?? [];
        if (empty($drivers)) {
            $this->info("No drivers to coach for tenant {$tenantId} this week.");
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

        // Use dedicated coaching log channel
        $coachLog = Log::channel('coaching');

        foreach ($drivers as $d) {
            $name  = $d['driver_name'];
            $phone = $d['mobile_phone'];

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
                $this->warn("{$name}: no coaching template for " . json_encode($cats));
                continue;
            }

            // Log ratings & template into coaching.log
            $coachLog->info('Driver coaching data', [
                'driver'   => $name,
                'ratings'  => [
                    'acceptance'    => "{$cats['acceptance']} ({$d['acceptance_score']}%)",
                    'on_time'       => "{$cats['ontime']} ({$d['on_time_score']}%)",
                    'greenzone'     => "{$cats['greenzone']} ({$d['safety_score']})",
                    'severe_alerts' => "{$cats['severe_alerts']} ({$d['severe_alerts']})",
                ],
                'template' => $templ->coaching_message,
            ]);

            $body = $this->fillTemplate(
                $templ->coaching_message,
                $d,
                compact('compAcc','compOT','compGreen')
            );

            // Format US phone to E.164
            $digits = preg_replace('/\D+/', '', $phone);
            $to     = '+1' . $digits;

            try {
                $sms = $this->twilioService->sendSms($to, $body);

                // Log full Twilio response payload into coaching.log
                $coachLog->debug('Twilio SMS payload', $sms->toArray());

                $this->info("→ Sent SMS to {$to} (SID: {$sms->sid})");
            } catch (\Throwable $e) {
                Log::error("Failed to send SMS to {$to}: " . $e->getMessage(), [
                    'exception' => $e,
                ]);
                $this->error("✖ Failed to send SMS to {$to}");
            }

            $this->info(str_repeat('─', 40));
        }

        $this->info('Done sending coaching SMS.');
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
