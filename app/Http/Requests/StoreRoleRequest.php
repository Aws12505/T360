<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreRoleRequest
 *
 * Validates data for creating a new role.
 *
 * Command:
 *   php artisan make:request StoreRoleRequest
 */
class StoreRoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
        ];
    }
}
