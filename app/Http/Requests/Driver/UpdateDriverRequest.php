<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDriverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $driverId = $this->route('driver');
        return [
            'first_name'   => 'required|string',
            'last_name'    => 'required|string',
            'email'        => 'required|email|unique:drivers,email,' . $driverId,
            'mobile_phone' => 'required|string',
            'hiring_date'  => 'required|date',
            'tenant_id'    => 'required|exists:tenants,id',
            'netradyne_user_name' => 'required|string',
            'password'     => 'nullable|string|min:8',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // NEW
        ];
    }

    protected function prepareForValidation()
    {
        if (!is_null(Auth::user()->tenant_id)) {
            $this->merge(['tenant_id' => Auth::user()->tenant_id]);
        }
    }
}
