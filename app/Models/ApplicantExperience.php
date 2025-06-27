<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicantExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'job_title', 'company', 'description', 'start_date', 'end_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

