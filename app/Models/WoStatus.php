<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WoStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    /**
     * Get the repair orders that have this status.
     */
    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class, 'wo_status_id');
    }

    
}