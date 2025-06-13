<?php

namespace App\Http\Requests\SMSCoaching;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class SMSScoresThresholdsRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust with policies if needed
    }

    public function rules()
    {
        return [
            // On-time
            'on_time_good' => 'required|numeric|between:0,100',
            'on_time_bad' => 'required|numeric|between:0,100',
            'on_time_minor_improvement' => 'required|numeric|between:0,100',

            // Acceptance
            'acceptance_good' => 'required|numeric|between:0,100',
            'acceptance_bad' => 'required|numeric|between:0,100',
            'acceptance_minor_improvement' => 'required|numeric|between:0,100',

            // Green Zone
            'greenzone_score_good' => 'required|numeric|max:1050',
            'greenzone_score_bad' => 'required|numeric|max:1050',
            'greenzone_score_minor_improvement' => 'required|numeric|max:1050',

            // Severe Alerts
            'severe_alerts_good' => 'required|integer|min:0',
            'severe_alerts_bad' => 'required|integer|min:0',
            'severe_alerts_minor_improvement' => 'required|integer|min:0',
            'tenant_id' => 'required|exists:tenants,id',
        ];
    }

    protected function prepareForValidation()
    {
        // If the user is not SuperAdmin, enforce the user's tenant_id.
        if (!is_null(Auth::user()->tenant_id)) { 
            $this->merge(['tenant_id' => Auth::user()->tenant_id]); 
        }
    }
}
