<?php

namespace App\Http\Requests\UserManagement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateRoleRequest
 *
 * Validates data for updating an existing role.
 *
 * @method \Illuminate\Routing\Route route(string $param)
 * 
 * Command:
 *   php artisan make:request UpdateRoleRequest
 */
class UpdateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $roleId = $this->route('role'); // Assumes route parameter is named 'role'
        return [
            'name'        => 'required|string|max:255|unique:roles,name,' . $roleId,
            'permissions' => 'nullable|array',
        ];
    }
}
