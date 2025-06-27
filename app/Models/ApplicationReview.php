<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationReview extends Model
{
    protected $fillable = [
        'job_application_id',
        'user_id',
        'comments',
        'rating',
        'recommendation',
    ];


    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
