<?php

namespace App\Http\Requests\Acceptance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRejectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'             => 'required|date',
            'driver_name'      => 'required|string',
            'rejection_type'   => 'required|in:block,load',
            'rejection_category'=> [
                'required',
                function ($attribute, $value, $fail) {
                    $type = $this->input('rejection_type');
                    
                    // Valid categories for block type
                    $blockCategories = ['after_start', 'within_24', 'more_than_24', 'advanced_rejection'];
                    // Valid categories for load type
                    $loadCategories = ['after_start', 'within_6', 'more_than_6'];
                    
                    if ($type === 'block' && !in_array($value, $blockCategories)) {
                        $fail('The rejection category is invalid for block type rejections.');
                    }
                    
                    if ($type === 'load' && !in_array($value, $loadCategories)) {
                        $fail('The rejection category is invalid for load type rejections.');
                    }
                },
            ],
            'reason_code_id'   => [
                'required',
                Rule::exists('rejection_reason_codes', 'id')->whereNull('deleted_at'),
            ],
            'disputed'         => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
            'tenant_id' => 'required|exists:tenants,id',
        ];
    }
    
    protected function prepareForValidation()
    {
        if (!is_null(Auth::user()->tenant_id)) { 
            $this->merge(['tenant_id' => Auth::user()->tenant_id]); 
        }
    }
    
    public function messages()
    {
        return [
            'rejection_category.required' => 'The rejection category field is required.',
        ];
    }
}
