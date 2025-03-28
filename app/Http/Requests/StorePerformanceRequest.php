<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
/**
 * Class StorePerformanceRequest
 *
 * Validates data for creating a new performance record.
 *
 * Command:
 *   php artisan make:request StorePerformanceRequest
 */
class StorePerformanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'date'                            => 'required|date',
            'acceptance'                      => 'required|numeric',
            'on_time_to_origin'               => 'required|numeric',
            'on_time_to_destination'          => 'required|numeric',
            'maintenance_variance_to_spend'   => 'required|numeric',
            'open_boc'                        => 'required|integer',
            'meets_safety_bonus_criteria'     => 'required|boolean',
            'vcr_preventable'                 => 'required|integer',
        ];
    }
    protected function prepareForValidation()
    {
        // If the authenticated user is not a SuperAdmin, always use the user's tenant_id.
        if (!is_null(Auth::user()->tenant_id)) {
            $this->merge(['tenant_id' => Auth::user()->tenant_id]);
        }
    }
}
