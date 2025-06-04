<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDriverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name'   => 'required|string',
            'last_name'    => 'required|string',
            'email'        => 'required|email|unique:drivers,email',
            'mobile_phone' => 'required|string',
            'hiring_date'  => 'required|date',
            'tenant_id'    => 'required|exists:tenants,id',
            'netradyne_user_name' => 'required|string',
            'password'     => 'required|string',
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
