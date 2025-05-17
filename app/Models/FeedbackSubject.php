<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedbackSubject extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    /**
     * Predefined subjects → many feedback.
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}
