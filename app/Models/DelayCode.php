<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DelayCode
 *
 * Represents a unique delay code used in delay records.
 *
 * Properties:
 * - code: The unique string code.
 *
 * Relationships:
 * - Has many Delays.
 */
class DelayCode extends Model
{
    use SoftDeletes;
    protected $fillable = ['code'];

    /**
     * Get the delays associated with this delay code.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function delays()
    {
        return $this->hasMany(Delay::class);
    }
}
