<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewScore extends Model
{
    protected $fillable = [
        'interview_id',
        'interviewer_id',
        'technical_skills',
        'communication',
        'cultural_fit',
        'problem_solving',
        'comments',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function getTotalScoreAttribute()
    {
        // Define weights (sum should equal 1.0)
        $weights = [
            'technical_skills' => 0.4,
            'communication' => 0.2,
            'cultural_fit' => 0.2,
            'problem_solving' => 0.2,
        ];

        $score = 0;
        foreach ($weights as $criteria => $weight) {
            $value = $this->{$criteria} ?? 0;
            $score += $value * $weight;
        }

        return round($score, 2); // out of max 5.0 (if scale is 1-5)
    }

    
}
