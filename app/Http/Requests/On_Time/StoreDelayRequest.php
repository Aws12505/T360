<?php

namespace App\Http\Requests\On_Time;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDelayRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $tenantId = !is_null(Auth::user()->tenant_id)
            ? Auth::user()->tenant_id
            : $this->input('tenant_id');

        return [
            'date' => 'required|date',
            'delay_type' => 'required|in:origin,destination',
            'driver_name' => 'nullable|string|max:75',
            'delay_duration_hours' => 'required|integer|min:0',
            'delay_duration_minutes' => 'required|integer|min:0|max:59',
            'delay_reason' => 'nullable|string',
            'load_id' => [
                'nullable',
                'string',
                Rule::unique('delays', 'load_id')
                    ->where(
                        fn($query) => $query
                            ->where('tenant_id', $tenantId)
                            ->where('delay_type', $this->input('delay_type'))
                    ),
            ],
            'disputed' => 'nullable|in:none,pending,won,lost',
            'driver_controllable' => 'nullable|boolean',
            'carrier_controllable' => 'nullable|boolean',
            'tenant_id' => 'required|exists:tenants,id',
        ];
    }

    protected function prepareForValidation()
    {
        if (!is_null(Auth::user()->tenant_id)) {
            $this->merge(['tenant_id' => Auth::user()->tenant_id]);
        }
    }
}