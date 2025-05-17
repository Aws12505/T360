<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:feedback_subjects,name',
        ];
    }
}
