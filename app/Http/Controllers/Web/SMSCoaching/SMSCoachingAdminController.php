<?php

namespace App\Http\Controllers\Web\SMSCoaching;

use App\Http\Controllers\Controller;
use App\Models\SmsCoachingMessage;
use App\Models\SmsCoachingThreshold;
use App\Models\Tenant;
use App\Services\SMSCoaching\SmsCoachingMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class SMSCoachingAdminController extends Controller
{
    public function index(): Response
    {
        $permissions = Auth::user()->getAllPermissions();
        $tenants = Tenant::select('id', 'name')->orderBy('name')->get();
        $metrics = collect(SmsCoachingMetrics::all())
            ->map(fn($meta, $key) => array_merge(['key' => $key], $meta))
            ->values();

        $globalMessages = SmsCoachingMessage::query()
            ->whereNull('tenant_id')
            ->get();

        $globalMessageMap = [];
        foreach (SmsCoachingMetrics::all() as $key => $meta) {
            $globalMessageMap[$key] = [
                'general' => null,
                SmsCoachingMetrics::STATUS_GOOD => null,
                SmsCoachingMetrics::STATUS_MINOR => null,
                SmsCoachingMetrics::STATUS_BAD => null,
            ];
        }

        foreach ($globalMessages as $message) {
            $statusKey = $message->status ?? 'general';
            $globalMessageMap[$message->metric_key][$statusKey] = $message->message;
        }

        $globalThresholds = SmsCoachingThreshold::query()
            ->whereNull('tenant_id')
            ->get()
            ->keyBy('metric_key');

        $globalThresholdMap = [];
        foreach (SmsCoachingMetrics::thresholdKeys() as $key) {
            $row = $globalThresholds->get($key);
            $globalThresholdMap[$key] = [
                'good' => $row?->good,
                'minor_improvement' => $row?->minor_improvement,
                'bad' => $row?->bad,
            ];
        }

        $messageOverrides = SmsCoachingMessage::query()
            ->whereNotNull('tenant_id')
            ->with('tenant:id,name')
            ->orderBy('metric_key')
            ->orderBy('status')
            ->get()
            ->map(function (SmsCoachingMessage $message) {
                return [
                    'id' => $message->id,
                    'tenant_id' => $message->tenant_id,
                    'tenant_name' => $message->tenant?->name,
                    'metric_key' => $message->metric_key,
                    'status' => $message->status,
                    'message' => $message->message,
                ];
            });

        $thresholdOverrides = SmsCoachingThreshold::query()
            ->whereNotNull('tenant_id')
            ->with('tenant:id,name')
            ->orderBy('metric_key')
            ->get()
            ->map(function (SmsCoachingThreshold $threshold) {
                return [
                    'id' => $threshold->id,
                    'tenant_id' => $threshold->tenant_id,
                    'tenant_name' => $threshold->tenant?->name,
                    'metric_key' => $threshold->metric_key,
                    'good' => $threshold->good,
                    'minor_improvement' => $threshold->minor_improvement,
                    'bad' => $threshold->bad,
                ];
            });

        $placeholders = $this->getPlaceholders();

        return Inertia::render('SMSCoachingAdmin/Index', [
            'permissions' => $permissions,
            'tenants' => $tenants,
            'metrics' => $metrics,
            'globalMessages' => $globalMessageMap,
            'globalThresholds' => $globalThresholdMap,
            'messageOverrides' => $messageOverrides,
            'thresholdOverrides' => $thresholdOverrides,
            'placeholders' => $placeholders,
        ]);
    }

    public function storeGlobalMessages(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'messages' => ['required', 'array'],
            'messages.*' => ['array'],
            'messages.*.*' => ['nullable', 'string', 'max:400'],
        ])->validate();

        $messages = $request->input('messages', []);
        $statuses = [
            SmsCoachingMetrics::STATUS_GOOD,
            SmsCoachingMetrics::STATUS_MINOR,
            SmsCoachingMetrics::STATUS_BAD,
        ];

        foreach ($messages as $metricKey => $statusMap) {
            foreach ($statusMap as $statusKey => $message) {
                $status = $statusKey === 'general' ? null : $statusKey;

                if ($metricKey === SmsCoachingMetrics::METRIC_GENERAL && $status !== null) {
                    continue;
                }

                if ($metricKey !== SmsCoachingMetrics::METRIC_GENERAL && $status === null) {
                    continue;
                }

                if ($status !== null && !in_array($status, $statuses, true)) {
                    continue;
                }

                $messageText = trim((string) $message);

                $query = SmsCoachingMessage::query()
                    ->whereNull('tenant_id')
                    ->where('metric_key', $metricKey)
                    ->where('status', $status);

                if ($messageText === '') {
                    $query->delete();
                    continue;
                }

                SmsCoachingMessage::updateOrCreate([
                    'tenant_id' => null,
                    'metric_key' => $metricKey,
                    'status' => $status,
                ], [
                    'message' => $messageText,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Global messages saved.');
    }

    public function storeGlobalThresholds(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'thresholds' => ['required', 'array'],
            'thresholds.*' => ['array'],
            'thresholds.*.good' => ['nullable', 'numeric'],
            'thresholds.*.minor_improvement' => ['nullable', 'numeric'],
            'thresholds.*.bad' => ['nullable', 'numeric'],
        ])->validate();

        $thresholds = $request->input('thresholds', []);

        foreach ($thresholds as $metricKey => $values) {
            if (!SmsCoachingMetrics::isThresholdMetric($metricKey)) {
                continue;
            }

            $good = $values['good'] ?? null;
            $minor = $values['minor_improvement'] ?? null;
            $bad = $values['bad'] ?? null;

            $query = SmsCoachingThreshold::query()
                ->whereNull('tenant_id')
                ->where('metric_key', $metricKey);

            if ($good === null && $minor === null && $bad === null) {
                $query->delete();
                continue;
            }

            SmsCoachingThreshold::updateOrCreate([
                'tenant_id' => null,
                'metric_key' => $metricKey,
            ], [
                'good' => $good,
                'minor_improvement' => $minor,
                'bad' => $bad,
            ]);
        }

        return redirect()->back()->with('success', 'Global thresholds saved.');
    }

    public function storeMessageOverrides(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'tenant_ids' => ['required', 'array'],
            'tenant_ids.*' => ['integer', 'exists:tenants,id'],
            'metric_key' => ['required', 'string'],
            'status' => ['nullable', 'string'],
            'message' => ['required', 'string', 'max:400'],
        ])->validate();

        $metricKey = $request->string('metric_key')->toString();
        $status = $request->input('status');

        $allowedStatuses = [
            SmsCoachingMetrics::STATUS_GOOD,
            SmsCoachingMetrics::STATUS_MINOR,
            SmsCoachingMetrics::STATUS_BAD,
        ];

        if ($metricKey === SmsCoachingMetrics::METRIC_GENERAL) {
            $status = null;
        } elseif (!in_array($status, $allowedStatuses, true)) {
            return redirect()->back()->with('error', 'Status is required for threshold-based messages.');
        }

        foreach ($request->input('tenant_ids', []) as $tenantId) {
            SmsCoachingMessage::query()
                ->where('tenant_id', $tenantId)
                ->where('metric_key', $metricKey)
                ->where('status', $status)
                ->delete();

            SmsCoachingMessage::create([
                'tenant_id' => $tenantId,
                'metric_key' => $metricKey,
                'status' => $status,
                'message' => $request->input('message'),
            ]);
        }

        return redirect()->back()->with('success', 'Message override saved.');
    }

    public function storeThresholdOverrides(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'tenant_ids' => ['required', 'array'],
            'tenant_ids.*' => ['integer', 'exists:tenants,id'],
            'metric_key' => ['required', 'string'],
            'good' => ['required', 'numeric'],
            'minor_improvement' => ['required', 'numeric'],
            'bad' => ['required', 'numeric'],
        ])->validate();

        $metricKey = $request->string('metric_key')->toString();

        if (!SmsCoachingMetrics::isThresholdMetric($metricKey)) {
            return redirect()->back()->with('error', 'Metric does not support thresholds.');
        }

        foreach ($request->input('tenant_ids', []) as $tenantId) {
            SmsCoachingThreshold::updateOrCreate([
                'tenant_id' => $tenantId,
                'metric_key' => $metricKey,
            ], [
                'good' => $request->input('good'),
                'minor_improvement' => $request->input('minor_improvement'),
                'bad' => $request->input('bad'),
            ]);
        }

        return redirect()->back()->with('success', 'Threshold override saved.');
    }

    public function deleteMessageOverride(SmsCoachingMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->back()->with('success', 'Message override removed.');
    }

    public function deleteThresholdOverride(SmsCoachingThreshold $threshold): RedirectResponse
    {
        $threshold->delete();

        return redirect()->back()->with('success', 'Threshold override removed.');
    }

    private function getPlaceholders(): array
    {
        return [
            ['value' => '{driver_first_name}', 'label' => 'First Name'],
            ['value' => '{driver_last_name}', 'label' => 'Last Name'],
            ['value' => '{driver_full_name}', 'label' => 'Full Name'],
            ['value' => '{driver_acceptance_score}', 'label' => 'Driver Acceptance %'],
            ['value' => '{driver_on_time_score}', 'label' => 'Driver On-Time %'],
            ['value' => '{driver_greenzone_score}', 'label' => 'Driver Green Zone'],
            ['value' => '{driver_severe_alerts}', 'label' => 'Driver Severe Alerts (Total)'],
            ['value' => '{driver_traffic_light_violation}', 'label' => 'Traffic Light Violations'],
            ['value' => '{driver_speeding_violations}', 'label' => 'Speeding Violations'],
            ['value' => '{driver_following_distance}', 'label' => 'Following Distance'],
            ['value' => '{driver_roadside_parking}', 'label' => 'Roadside Parking'],
            ['value' => '{driver_driver_distraction}', 'label' => 'Driver Distraction'],
            ['value' => '{driver_sign_violations}', 'label' => 'Sign Violations'],
            ['value' => '{company_avg_acceptance}', 'label' => 'Company Avg Acceptance %'],
            ['value' => '{company_avg_on_time}', 'label' => 'Company Avg On-Time %'],
            ['value' => '{company_avg_greenzone}', 'label' => 'Company Avg Green Zone'],
            ['value' => '{company_avg_traffic_light_violation}', 'label' => 'Company Avg Traffic Light Violations'],
            ['value' => '{company_avg_speeding_violations}', 'label' => 'Company Avg Speeding Violations'],
            ['value' => '{company_avg_following_distance}', 'label' => 'Company Avg Following Distance'],
            ['value' => '{company_avg_roadside_parking}', 'label' => 'Company Avg Roadside Parking'],
            ['value' => '{company_avg_driver_distraction}', 'label' => 'Company Avg Driver Distraction'],
            ['value' => '{company_avg_sign_violations}', 'label' => 'Company Avg Sign Violations'],
            ['value' => '{threshold_acceptance_good}', 'label' => 'Acceptance Good Threshold'],
            ['value' => '{threshold_acceptance_minor_improvement}', 'label' => 'Acceptance Needs Improvement Threshold'],
            ['value' => '{threshold_acceptance_bad}', 'label' => 'Acceptance Bad Threshold'],
            ['value' => '{threshold_on_time_good}', 'label' => 'On-Time Good Threshold'],
            ['value' => '{threshold_on_time_minor_improvement}', 'label' => 'On-Time Needs Improvement Threshold'],
            ['value' => '{threshold_on_time_bad}', 'label' => 'On-Time Bad Threshold'],
            ['value' => '{threshold_greenzone_good}', 'label' => 'Green Zone Good Threshold'],
            ['value' => '{threshold_greenzone_minor_improvement}', 'label' => 'Green Zone Needs Improvement Threshold'],
            ['value' => '{threshold_greenzone_bad}', 'label' => 'Green Zone Bad Threshold'],
            ['value' => '{threshold_traffic_light_violation_good}', 'label' => 'Traffic Light Violations Good Threshold'],
            ['value' => '{threshold_traffic_light_violation_minor_improvement}', 'label' => 'Traffic Light Violations Needs Improvement Threshold'],
            ['value' => '{threshold_traffic_light_violation_bad}', 'label' => 'Traffic Light Violations Bad Threshold'],
            ['value' => '{threshold_speeding_violations_good}', 'label' => 'Speeding Violations Good Threshold'],
            ['value' => '{threshold_speeding_violations_minor_improvement}', 'label' => 'Speeding Violations Needs Improvement Threshold'],
            ['value' => '{threshold_speeding_violations_bad}', 'label' => 'Speeding Violations Bad Threshold'],
            ['value' => '{threshold_following_distance_good}', 'label' => 'Following Distance Good Threshold'],
            ['value' => '{threshold_following_distance_minor_improvement}', 'label' => 'Following Distance Needs Improvement Threshold'],
            ['value' => '{threshold_following_distance_bad}', 'label' => 'Following Distance Bad Threshold'],
            ['value' => '{threshold_roadside_parking_good}', 'label' => 'Roadside Parking Good Threshold'],
            ['value' => '{threshold_roadside_parking_minor_improvement}', 'label' => 'Roadside Parking Needs Improvement Threshold'],
            ['value' => '{threshold_roadside_parking_bad}', 'label' => 'Roadside Parking Bad Threshold'],
            ['value' => '{threshold_driver_distraction_good}', 'label' => 'Driver Distraction Good Threshold'],
            ['value' => '{threshold_driver_distraction_minor_improvement}', 'label' => 'Driver Distraction Needs Improvement Threshold'],
            ['value' => '{threshold_driver_distraction_bad}', 'label' => 'Driver Distraction Bad Threshold'],
            ['value' => '{threshold_sign_violations_good}', 'label' => 'Sign Violations Good Threshold'],
            ['value' => '{threshold_sign_violations_minor_improvement}', 'label' => 'Sign Violations Needs Improvement Threshold'],
            ['value' => '{threshold_sign_violations_bad}', 'label' => 'Sign Violations Bad Threshold'],
            ['value' => '{date_start}', 'label' => 'Date Start'],
            ['value' => '{date_end}', 'label' => 'Date End'],
            ['value' => '{date_label}', 'label' => 'Date Label'],
        ];
    }
}
