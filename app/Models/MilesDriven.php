<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MilesDriven
 *
 * Represents a weekly miles driven record in the application.
 *
 * Properties:
 * - id: The primary key.
 * - tenant_id: The ID of the tenant this record belongs to.
 * - week_start_date: The start date of the week.
 * - week_end_date: The end date of the week.
 * - miles: The number of miles driven during the week.
 * - notes: Optional notes about the miles driven.
 *
 * Relationships:
 * - Belongs to a Tenant.
 */
class MilesDriven extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'miles_driven';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'week_start_date',
        'week_end_date',
        'miles',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'miles' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns this miles driven record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}