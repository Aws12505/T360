<?php

namespace App\Http\Requests\MilesDriven;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateMilesDrivenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'week_start_date' => 'required|date',
            'week_end_date' => 'required|date|after_or_equal:week_start_date',
            'miles' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];

        // Only require tenant_id for super admins
        if (Auth::user()->tenant_id === null) {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // If user is not a super admin, set tenant_id automatically
        if (Auth::user()->tenant_id !== null) {
            $this->merge([
                'tenant_id' => Auth::user()->tenant_id,
            ]);
        }
    }
}