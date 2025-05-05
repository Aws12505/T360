<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TicketSubject
 *
 * Represents a predefined subject that can be used for support tickets.
 *
 * Properties:
 * - name: The subject name/text.
 *
 * Relationships:
 * - Has many Tickets.
 */
class TicketSubject extends Model
{
    use SoftDeletes;
    protected $fillable = ['name'];

    /**
     * Get the tickets associated with this subject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}