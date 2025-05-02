<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRepairOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ro_number'           => 'nullable|string',
            'ro_open_date'        => 'required|date',
            'ro_close_date'       => 'nullable|date',
            'truck_id'            => 'required|exists:trucks,id',
            'area_of_concerns'    => 'nullable|array', // Changed from required to nullable
            'area_of_concerns.*'  => 'exists:area_of_concerns,id',
            'repairs_made'        => 'nullable|string',
            'vendor_id'           => 'required|exists:vendors,id',
            'wo_number'           => 'string',
            'wo_status_id'        => 'required|exists:wo_statuses,id', // Changed from enum validation to foreign key
            'invoice'             => 'nullable|string',
            'invoice_amount'      => 'nullable|numeric',
            'invoice_received'    => 'required|boolean',
            'on_qs'               => 'required|in:yes,no,not expected',
            'qs_invoice_date'     => 'nullable|date',
            'disputed'            => 'required|boolean',
            'dispute_outcome'     => 'nullable|string',
            'tenant_id'           => 'required|exists:tenants,id',
        ];
    }

    protected function prepareForValidation()
    {
        if (!is_null(Auth::user()->tenant_id)) {
            $this->merge(['tenant_id' => Auth::user()->tenant_id]);
        }
    }
}
