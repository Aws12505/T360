<?php

namespace App\Http\Requests\On_Time;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
/**
 * Class StoreDelayRequest
 *
 * This request class handles the validation for creating a new delay record.
 */
class StoreDelayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Adjust as needed for your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date' => 'required|date',
            'driver_name' => 'required|string',
            'delay_type' => 'required|in:origin,destination',
            'delay_category' => 'required|in:1_120,121_600,601_plus',
            'delay_code_id' => 'required|exists:delay_codes,id',
            'disputed' => 'required|boolean',
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
