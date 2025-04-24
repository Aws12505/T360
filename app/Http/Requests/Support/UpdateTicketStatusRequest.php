<?php
namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only super-admin (tenant_id null) may update status
        return $this->user()->tenant_id === null;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:open,in_progress,closed',
        ];
    }
}
