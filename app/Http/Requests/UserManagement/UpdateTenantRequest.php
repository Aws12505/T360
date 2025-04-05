<?php

namespace App\Http\Requests\UserManagement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateTenantRequest
 *
 * Validates data for updating an existing tenant.
 *
 * @method \Illuminate\Routing\Route route(string $param)
 * 
 * Command:
 *   php artisan make:request UpdateTenantRequest
 */
class UpdateTenantRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $tenantId = $this->route('tenant'); // Assumes route parameter is named 'tenant'
        return [
            'name' => 'required|string|max:255|unique:tenants,name,' . $tenantId,
            'slug' => 'required|string|max:255|unique:tenants,slug,' . $tenantId,
        ];
    }
}
