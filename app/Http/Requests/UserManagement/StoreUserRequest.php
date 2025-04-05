<?php

namespace App\Http\Requests\UserManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

/**
 * Class StoreUserRequest
 *
 * Validates data for creating a new user.
 *
 * Command:
 *   php artisan make:request StoreUserRequest
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Modify this as needed.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:8',
            'tenant_id' => 'nullable|sometimes|exists:tenants,id',
            'roles'            => 'nullable|array',
            'user_permissions' => 'nullable|array',
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
