<?php

namespace App\Http\Requests\UserManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class UpdateUserRequest
 *
 * Validates data for updating an existing user.
 *
 * @method \Illuminate\Routing\Route route(string $param)
 * 
 * Command:
 *   php artisan make:request UpdateUserRequest
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Modify authorization logic as needed.
    }

    public function rules()
    {
        $userId = $this->route('user'); // Assumes route parameter is named 'user'
        return [
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $userId,
            'password'         => 'nullable|string|min:8',
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
