<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreTenantRequest
 *
 * Validates data for creating a new tenant.
 *
 * Command:
 *   php artisan make:request StoreTenantRequest
 */
class StoreTenantRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
