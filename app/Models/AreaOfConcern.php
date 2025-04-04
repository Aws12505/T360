<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AreaOfConcern
 *
 * Represents a unique area of concern.
 *
 * Properties:
 * - concern: The unique string representing the area of concern.
 */
class AreaOfConcern extends Model
{
    protected $fillable = ['concern'];
}
