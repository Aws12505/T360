<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class UpdateRejectionRequest
 *
 * Validates data for updating an existing rejection.
 *
 * Command:
 *   php artisan make:request UpdateRejectionRequest
 */
class UpdateRejectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'             => 'required|date',
            'driver_name'      => 'required|string',
            'rejection_type'   => 'required|in:block,load',
            'rejection_category'=> 'required|in:more_than_6,within_6,after_start',
            'reason_code_id'   => 'required|exists:rejection_reason_codes,id',
            'disputed'         => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
            'tenant_id' => 'required|exists:tenants,id',
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
