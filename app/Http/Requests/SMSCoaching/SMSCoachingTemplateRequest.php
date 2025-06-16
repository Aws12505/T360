<?php

namespace App\Http\Requests\SMSCoaching;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\SMSCoachingTemplates;

class SMSCoachingTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        if ($user = Auth::user()) {
            $this->merge([ 'tenant_id' => $user->tenant_id ]);
        }
    }

    public function rules(): array
    {
        $options   = ['good','bad','minor_improvement'];
        $optString = implode(',', $options);

        // figure out the current template ID (if any) for update
        $routeParam = $this->route('id');
        $ignoreId   = null;

        if ($routeParam instanceof SMSCoachingTemplates) {
            $ignoreId = $routeParam->getKey();
        } elseif (is_numeric($routeParam)) {
            $ignoreId = (int) $routeParam;
        }

        return [
            'coaching_message' => ['bail','required','string','max:400'],

            'acceptance' => [
                'bail','required',"in:{$optString}",
                Rule::unique('s_m_s_coaching_templates')
                    ->where('tenant_id', $this->tenant_id)
                    ->where('ontime', $this->ontime)
                    ->where('greenzone', $this->greenzone)
                    ->where('severe_alerts', $this->severe_alerts)
                    ->ignore($ignoreId),
            ],

            'ontime'        => ['bail','required',"in:{$optString}"],
            'greenzone'     => ['bail','required',"in:{$optString}"],
            'severe_alerts' => ['bail','required',"in:{$optString}"],
        ];
    }

    public function messages(): array
    {
        return [
            'coaching_message.required' => 'Please enter the coaching message.',
            'coaching_message.string'   => 'The coaching message must be text.',
            'coaching_message.max'      => 'Coaching message may not exceed 400 characters.',

            'acceptance.required'    => 'Choose an acceptance rating.',
            'acceptance.in'          => 'Invalid acceptance rating.',
            'acceptance.unique'      => 'A template for this exact combination of ratings already exists.',

            'ontime.required'        => 'Choose an on-time rating.',
            'ontime.in'              => 'Invalid on-time rating.',

            'greenzone.required'     => 'Choose a green-zone rating.',
            'greenzone.in'           => 'Invalid green-zone rating.',

            'severe_alerts.required' => 'Choose a severe-alerts rating.',
            'severe_alerts.in'       => 'Invalid severe-alerts rating.',
        ];
    }
}
