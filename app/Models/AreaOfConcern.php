<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    use SoftDeletes;
    protected $fillable = ['concern'];

    // In AreaOfConcern model
public function repairOrders()
{
    return $this->belongsToMany(RepairOrder::class, 'area_of_concern_repair_order')
                ->withTimestamps();
}
}
