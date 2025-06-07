<?php

namespace App\Http\Requests\UserManagement;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tenant;

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
        // Get the tenant by slug from the route parameter
        $tenantSlug = $this->route('tenantSlug');
        $tenant = Tenant::where('slug', $tenantSlug)->first();
        $tenantId = $tenant ? $tenant->id : null;

        return [
            'name' => 'required|string|max:255|unique:tenants,name,' . $tenantId,
            'slug' => 'required|string|max:255|alpha_dash|unique:tenants,slug,' . $tenantId,
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'timezone' => 'required|string|in:' . implode(',', timezone_identifiers_list()),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
        ];
    }
}
