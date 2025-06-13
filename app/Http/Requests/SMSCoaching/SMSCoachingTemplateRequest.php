<?php

namespace App\Http\Requests\SMSCoaching;

use Illuminate\Foundation\Http\FormRequest;

class SMSCoachingTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coaching_message' => 'required|string|max:150',
            'acceptance' => 'required|in:good,bad,minor_improvement',
            'ontime' => 'required|in:good,bad,minor_improvement',
            'greenzone' => 'required|in:good,bad,minor_improvement',
            'severe_alerts' => 'required|in:good,bad,minor_improvement',
            'tenant_id' => 'required|integer|exists:tenants,id',
        ];
    }
}
