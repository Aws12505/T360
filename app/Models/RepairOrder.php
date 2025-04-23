<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\TenantScope;
class RepairOrder extends Model
{
    protected $fillable = [
        'ro_number',
        'ro_open_date',
        'ro_close_date',
        'truck_id',
        'repairs_made',
        'vendor_id',
        'wo_number',
        'wo_status_id',
        'invoice',
        'invoice_amount',
        'invoice_received',
        'on_qs',
        'qs_invoice_date',
        'disputed',
        'dispute_outcome',
        'tenant_id',
    ];

    // Relationship to Truck
    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    // Relationship to Vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Many-to-many with Areas Of Concern
    public function areasOfConcern()
{
    return $this->belongsToMany(AreaOfConcern::class, 'area_of_concern_repair_order')
                ->withTimestamps()
                ->withTrashed(); // Include soft deleted areas of concern
}

    // Relationship to Tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Relationship to WoStatus
    public function woStatus()
    {
        return $this->belongsTo(WoStatus::class);
    }

    protected static function booted()
    {
        if(Auth::check()) {
            static::addGlobalScope(new TenantScope);
        }
    }
}
