<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\SmsCoachingMessage;
use App\Models\SmsCoachingThreshold;
use App\Models\SmsCoachingTenantSetting;
use App\Services\Driver\DriverDataService;
use App\Services\SMSCoaching\SmsCoachingMetrics;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Log;

class SendDriverCoaching extends Command
{
    protected $signature = 'coaching:send {tenantId}';
    protected $description = 'Send SMS coaching messages for a given tenant';

    public function __construct(
        protected DriverDataService $driverService,
        protected TwilioService $twilioService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantId = (int) $this->argument('tenantId');

        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $weekEnd = $now->copy()->endOfWeek(Carbon::SATURDAY);
        if ($now->dayOfWeek === Carbon::SUNDAY) {
            $weekStart->subWeek();
            $weekEnd->subWeek();
        }


        $driverResult = $this->driverService
            ->forTenant($tenantId)
            ->getDriversOverallPerformanceCoaching($weekStart, $weekEnd);

        $drivers = $driverResult['drivers'] ?? [];
        if (empty($drivers)) {
            $this->info("No drivers to coach for tenant {$tenantId} this week.");
            return self::SUCCESS;
        }

        $tenantMessageMap = $this->buildMessageMap(
            SmsCoachingMessage::where('tenant_id', $tenantId)->get()
        );
        $globalMessageMap = $this->buildMessageMap(
            SmsCoachingMessage::whereNull('tenant_id')->get()
        );

        $tenantThresholds = SmsCoachingThreshold::where('tenant_id', $tenantId)
            ->get()
            ->keyBy('metric_key');
        $globalThresholds = SmsCoachingThreshold::whereNull('tenant_id')
            ->get()
            ->keyBy('metric_key');

        $tenantSettings = SmsCoachingTenantSetting::where('tenant_id', $tenantId)
            ->get()
            ->keyBy('metric_key');

        $enabledMap = [];
        foreach (SmsCoachingMetrics::keys() as $metricKey) {
            $enabledMap[$metricKey] = $tenantSettings[$metricKey]->enabled ?? true;
        }

        $companyAverages = $this->calculateCompanyAverages($drivers);
        $thresholdPlaceholderMap = $this->buildThresholdPlaceholderMap($tenantThresholds, $globalThresholds);

        // Use dedicated coaching log channel
        $coachLog = Log::channel('coaching');

        foreach ($drivers as $d) {
            $name = $d['driver_name'];
            $phone = $d['mobile_phone'];

            if (!$phone) {
                $this->warn("{$name}: no mobile phone on file.");
                continue;
            }

            $messages = [];

            if ($enabledMap[SmsCoachingMetrics::METRIC_GENERAL] ?? false) {
                $generalMessage = $this->resolveMessage(
                    SmsCoachingMetrics::METRIC_GENERAL,
                    null,
                    $tenantMessageMap,
                    $globalMessageMap
                );

                if ($generalMessage) {
                    $messages[] = [
                        'metric' => SmsCoachingMetrics::METRIC_GENERAL,
                        'status' => null,
                        'message' => $generalMessage,
                    ];
                }
            }

            foreach (SmsCoachingMetrics::thresholdKeys() as $metricKey) {
                if (!($enabledMap[$metricKey] ?? false)) {
                    continue;
                }

                $threshold = $this->resolveThreshold($metricKey, $tenantThresholds, $globalThresholds);
                if (!$threshold || !$this->isThresholdConfigured($threshold)) {
                    $this->warn("{$name}: thresholds not configured for {$metricKey}.");
                    continue;
                }

                $driverValue = $this->getDriverMetricValue($metricKey, $d);
                $status = $this->categorizeByMetric($metricKey, $driverValue, $threshold);

                $template = $this->resolveMessage(
                    $metricKey,
                    $status,
                    $tenantMessageMap,
                    $globalMessageMap
                );

                if (!$template) {
                    $this->warn("{$name}: no coaching message for {$metricKey} ({$status}).");
                    continue;
                }

                $messages[] = [
                    'metric' => $metricKey,
                    'status' => $status,
                    'message' => $template,
                ];
            }

            if (empty($messages)) {
                $this->warn("{$name}: no coaching messages to send.");
                continue;
            }

            $digits = preg_replace('/\D+/', '', $phone);
            $to = '+1' . $digits;

            foreach ($messages as $entry) {
                $body = $this->fillTemplate(
                    $entry['message'],
                    $d,
                    $companyAverages,
                    $thresholdPlaceholderMap,
                    $weekStart,
                    $weekEnd
                );

                $coachLog->info('Driver coaching data', [
                    'driver' => $name,
                    'metric' => $entry['metric'],
                    'status' => $entry['status'],
                    'value' => $this->getDriverMetricValue($entry['metric'], $d),
                    'template' => $entry['message'],
                ]);

                try {
                    $sms = $this->twilioService->sendSms($to, $body);
                    $coachLog->debug('Twilio SMS payload', $sms->toArray());
                    $this->info("→ Sent SMS to {$to} (SID: {$sms->sid})");
                } catch (\Throwable $e) {
                    Log::error("Failed to send SMS to {$to}: " . $e->getMessage(), [
                        'exception' => $e,
                    ]);
                    $this->error("✖ Failed to send SMS to {$to}");
                }
            }

            $this->info(str_repeat('─', 40));
        }

        $this->info('Done sending coaching SMS.');
        return self::SUCCESS;
    }

    private function buildMessageMap($messages): array
    {
        $map = [];

        foreach ($messages as $message) {
            $key = $this->messageKey($message->metric_key, $message->status);
            $map[$key] = $message->message;
        }

        return $map;
    }

    private function messageKey(string $metricKey, ?string $status): string
    {
        return $metricKey . '|' . ($status ?? '');
    }

    private function resolveMessage(
        string $metricKey,
        ?string $status,
        array $tenantMessageMap,
        array $globalMessageMap
    ): ?string {
        $key = $this->messageKey($metricKey, $status);

        if (isset($tenantMessageMap[$key])) {
            return $tenantMessageMap[$key];
        }

        return $globalMessageMap[$key] ?? null;
    }

    private function resolveThreshold(string $metricKey, $tenantThresholds, $globalThresholds): ?array
    {
        $row = $tenantThresholds[$metricKey] ?? $globalThresholds[$metricKey] ?? null;

        if (!$row) {
            return null;
        }

        return [
            'good' => $row->good,
            'minor_improvement' => $row->minor_improvement,
            'bad' => $row->bad,
        ];
    }

    private function isThresholdConfigured(array $threshold): bool
    {
        return array_key_exists('good', $threshold)
            && array_key_exists('minor_improvement', $threshold)
            && $threshold['good'] !== null
            && $threshold['minor_improvement'] !== null;
    }

    private function categorizeByMetric(string $metricKey, float|int $value, array $threshold): string
    {
        $direction = SmsCoachingMetrics::direction($metricKey);
        $good = $threshold['good'];
        $minor = $threshold['minor_improvement'];

        if ($direction === 'low') {
            if ($value <= $good) {
                return SmsCoachingMetrics::STATUS_GOOD;
            }
            if ($value <= $minor) {
                return SmsCoachingMetrics::STATUS_MINOR;
            }
            return SmsCoachingMetrics::STATUS_BAD;
        }

        if ($value >= $good) {
            return SmsCoachingMetrics::STATUS_GOOD;
        }
        if ($value >= $minor) {
            return SmsCoachingMetrics::STATUS_MINOR;
        }
        return SmsCoachingMetrics::STATUS_BAD;
    }

    private function getDriverMetricValue(string $metricKey, array $driver): float|int
    {
        $driverKey = SmsCoachingMetrics::driverKey($metricKey);

        return $driverKey && isset($driver[$driverKey])
            ? $driver[$driverKey]
            : 0;
    }

    private function calculateCompanyAverages(array $drivers): array
    {
        if (empty($drivers)) {
            return [];
        }

        $collect = collect($drivers);

        return [
            'acceptance' => round($collect->avg('acceptance_score') ?? 0, 2),
            'on_time' => round($collect->avg('on_time_score') ?? 0, 2),
            'greenzone' => round($collect->avg('safety_score') ?? 0, 2),
            'traffic_light_violation' => round($collect->avg('traffic_light_violation') ?? 0, 2),
            'speeding_violations' => round($collect->avg('speeding_violations') ?? 0, 2),
            'following_distance' => round($collect->avg('following_distance') ?? 0, 2),
            'roadside_parking' => round($collect->avg('roadside_parking') ?? 0, 2),
            'driver_distraction' => round($collect->avg('driver_distraction') ?? 0, 2),
            'sign_violations' => round($collect->avg('sign_violations') ?? 0, 2),
        ];
    }

    private function buildThresholdPlaceholderMap($tenantThresholds, $globalThresholds): array
    {
        $map = [];

        foreach (SmsCoachingMetrics::thresholdKeys() as $metricKey) {
            $threshold = $this->resolveThreshold($metricKey, $tenantThresholds, $globalThresholds);

            if (!$threshold) {
                $threshold = [
                    'good' => null,
                    'minor_improvement' => null,
                    'bad' => null,
                ];
            }

            $map["{threshold_{$metricKey}_good}"] = $threshold['good'] ?? '';
            $map["{threshold_{$metricKey}_minor_improvement}"] = $threshold['minor_improvement'] ?? '';
            $map["{threshold_{$metricKey}_bad}"] = $threshold['bad'] ?? '';
        }

        return $map;
    }

    private function fillTemplate(
        string $template,
        array $driver,
        array $companyAverages,
        array $thresholdMap,
        Carbon $weekStart,
        Carbon $weekEnd
    ): string {
        [$first, $last] = array_pad(explode(' ', $driver['driver_name'], 2), 2, '');
        $fullName = trim("{$first} {$last}");

        $map = [
            '{driver_first_name}' => $first,
            '{driver_last_name}' => $last,
            '{driver_full_name}' => $fullName,
            '{driver_acceptance_score}' => $driver['acceptance_score'] ?? 0,
            '{driver_ontime_score}' => $driver['on_time_score'] ?? 0,
            '{driver_on_time_score}' => $driver['on_time_score'] ?? 0,
            '{driver_greenzone_score}' => $driver['safety_score'] ?? 0,
            '{driver_severe_alerts}' => $driver['severe_alerts'] ?? 0,
            '{driver_traffic_light_violation}' => $driver['traffic_light_violation'] ?? 0,
            '{driver_speeding_violations}' => $driver['speeding_violations'] ?? 0,
            '{driver_following_distance}' => $driver['following_distance'] ?? 0,
            '{driver_roadside_parking}' => $driver['roadside_parking'] ?? 0,
            '{driver_driver_distraction}' => $driver['driver_distraction'] ?? 0,
            '{driver_distraction}' => $driver['driver_distraction'] ?? 0,
            '{driver_sign_violations}' => $driver['sign_violations'] ?? 0,
            '{company_avg_acceptance}' => $companyAverages['acceptance'] ?? 0,
            '{company_avg_ontime}' => $companyAverages['on_time'] ?? 0,
            '{company_avg_on_time}' => $companyAverages['on_time'] ?? 0,
            '{company_avg_greenzone}' => $companyAverages['greenzone'] ?? 0,
            '{company_avg_traffic_light_violation}' => $companyAverages['traffic_light_violation'] ?? 0,
            '{company_avg_speeding_violations}' => $companyAverages['speeding_violations'] ?? 0,
            '{company_avg_following_distance}' => $companyAverages['following_distance'] ?? 0,
            '{company_avg_roadside_parking}' => $companyAverages['roadside_parking'] ?? 0,
            '{company_avg_driver_distraction}' => $companyAverages['driver_distraction'] ?? 0,
            '{company_avg_sign_violations}' => $companyAverages['sign_violations'] ?? 0,
            '{date_start}' => $weekStart->toDateString(),
            '{date_end}' => $weekEnd->toDateString(),
            '{date_label}' => "Week of {$weekStart->toDateString()} - {$weekEnd->toDateString()}",
        ];

        $map = array_merge($map, $thresholdMap);

        return strtr($template, $map);
    }
}
