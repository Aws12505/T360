<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
class RejectionReasonCode extends Model
{
    protected $fillable = ['reason_code'];

    public function rejections()
    {
        return $this->hasMany(Rejection::class);
    }

}
