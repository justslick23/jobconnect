<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewReview extends Model
{
    protected $fillable = ['interview_id', 'user_id', 'comments', 'rating', 'recommendation'];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
