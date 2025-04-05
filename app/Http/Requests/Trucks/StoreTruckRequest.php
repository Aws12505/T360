<?php

namespace App\Http\Requests\Trucks;

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
            'is_returned'  => 'sometimes|boolean',
            'inspection_status' => 'required|in:good,expired',
            'inspection_expiry_date' => 'required|date',
        ];
    }
    protected function prepareForValidation()
    {
        if (Auth::user()->tenant_id) {
            $this->merge([
                'tenant_id' => Auth::user()->tenant_id,
            ]);
        }
    }
}
