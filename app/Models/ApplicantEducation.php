<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicantEducation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'institution', 'degree', 'field_of_study', 'education_level', 'start_date', 'end_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
