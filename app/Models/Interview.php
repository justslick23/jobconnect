<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    //

    
    protected $fillable = [
        'job_application_id',
        'interview_date',
        'applicant_id',
    ];

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function reviews()
{
    return $this->hasMany(InterviewReview::class);
}

// Interview.php
public function application()
{
    return $this->belongsTo(JobApplication::class);
}


public function applicant()
{
    return $this->belongsTo(User::class);
}

public function scores()
{
    return $this->hasMany(InterviewScore::class);
}

public function averageScore()
{
    $scores = $this->scores;

    if ($scores->isEmpty()) return null;

    return round($scores->avg(function ($score) {
        return $score->total_score; // You can define this accessor in InterviewScore model
    }), 2);
}


}
