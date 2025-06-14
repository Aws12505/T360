<?php

namespace App\Http\Requests\SMSCoaching;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SMSCoachingTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check(); // More explicit authorization check
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $performanceOptions = implode(',', ['good', 'bad', 'minor_improvement']);

        return [
            'coaching_message' => [
                'required',
                'string',
                'max:400'
            ],
            'acceptance' => [
                'required',
                'in:' . $performanceOptions
            ],
            'ontime' => [
                'required',
                'in:' . $performanceOptions
            ],
            'greenzone' => [
                'required',
                'in:' . $performanceOptions
            ],
            'severe_alerts' => [
                'required',
                'in:' . $performanceOptions
            ],
            'tenant_id' => [
                'sometimes',
                'nullable',
                'exists:tenants,id'
            ],
            // ✅ Composite unique validation
            'composite_key' => [
                Rule::unique('sms_coaching_templates')
                    ->where(function ($query) {
                        return $query
                            ->where('tenant_id', $this->tenant_id)
                            ->where('acceptance', $this->acceptance)
                            ->where('ontime', $this->ontime)
                            ->where('greenzone', $this->greenzone)
                            ->where('severe_alerts', $this->severe_alerts);
                    })
                    ->ignore($this->route('sms_coaching_template')), // if editing
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (Auth::check() && !is_null(Auth::user()->tenant_id)) {
            $this->merge([
                'tenant_id' => Auth::user()->tenant_id
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'coaching_message.required' => 'A coaching message is required.',
            'coaching_message.max' => 'The coaching message must not exceed 400 characters.',
            '*.required' => 'Please select a performance rating.',
            '*.in' => 'The selected performance rating is invalid.',
            'composite_key.unique' => 'A template with this combination of ratings already exists for this tenant.',
        ];
    }
}