<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['vendor_name'];
    
}
