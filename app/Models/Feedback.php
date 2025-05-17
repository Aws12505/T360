<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'subject',
        'message',
        'seen_by_admin',
    ];

    /**
     * The user who submitted this feedback.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
