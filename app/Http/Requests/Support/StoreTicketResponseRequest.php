<?php
namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Ticket;

class StoreTicketResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = Ticket::findOrFail($this->ticket_id);

        // Closed tickets cannot be replied to
        if ($ticket->status === 'closed') {
            return false;
        }

        // Super-admin can always respond
        if ($this->user()->tenant_id === null) {
            return true;
        }

        // Normal user may only respond to their own tickets
        return $ticket->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => 'required|exists:tickets,id',
            'message'   => 'required|string',
        ];
    }
}
