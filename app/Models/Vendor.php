<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Vendor
 *
 * Represents a unique vendor.
 *
 * Properties:
 * - vendor_name: The unique name representing the vendor.
 */
class Vendor extends Model
{
    use SoftDeletes;
    protected $fillable = ['vendor_name'];
    
}
