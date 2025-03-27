<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
class DelayCode extends Model
{
    protected $fillable = ['code'];

    public function delays()
    {
        return $this->hasMany(Delay::class);
    }

}
