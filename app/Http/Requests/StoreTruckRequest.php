<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class StoreTruckRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'truckid'  => 'required|integer',
            'type'      => 'required|in:daycab,sleepercab',
            'make'      => 'required|in:international,kenworth,peterbilt,volvo,freightliner',
            'fuel'      => 'required|in:cng,diesel',
            'license'   => 'required|integer',
            'vin'       => 'required|string|unique:trucks,vin',
            'tenant_id' => 'required|exists:tenants,id',
            'is_active'    => 'sometimes|boolean',
            'inspection_status' => 'required|in:good,expired',
            'inspection_expiry_date' => 'required|date',
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
