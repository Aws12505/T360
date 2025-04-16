<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\TenantScope;
class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'name',
        'description',
        'price',
        'currency_code',
        'next_billing_at',
        'last_billing_at',
        'expiry_year',
        'expiry_month',
        'last_four_digits',
        'card_type',
        'payment_gateway',
        'billing_address',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'next_billing_at'  => 'date',
        'last_billing_at'  => 'date',
        'expiry_year'      => 'integer',
        'expiry_month'     => 'integer',
        'billing_address'  => 'array',
    ];

    /**
     * A subscription belongs to a tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

     /**
     * Boot the model and apply TenantScope if a user is authenticated.
     *
     * @return void
     */
    protected static function booted()
    {
        if(Auth::check()) {
            static::addGlobalScope(new TenantScope);
        }
    }
}
