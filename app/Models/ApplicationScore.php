<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationScore extends Model
{
    protected $fillable = [
        'application_id',
        'skills_score',
        'experience_score',
        'education_score',
        'qualification_bonus',
        'total_score',
    ];


    
}
