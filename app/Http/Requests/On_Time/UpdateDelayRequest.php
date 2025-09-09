<?php

namespace App\Http\Requests\On_Time;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateDelayRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust authorization as needed.
    }

    public function rules()
    {
        return [
            'date' => 'required|date',
            'driver_name' => 'required|string',
            'delay_type' => 'required|in:origin,destination',
            'delay_category' => 'required|in:1_120,121_600,601_plus,1_60,61_240,241_600',
            'delay_code_id' => [
                'required',
                Rule::exists('delay_codes', 'id')->whereNull('deleted_at'),
            ],
            'disputed' => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
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
