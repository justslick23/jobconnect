<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'date_of_birth', 'gender',
        'phone', 'address', 'city', 'state', 'country', 'district'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   

    public function experiences()
    {
        return $this->hasMany(ApplicantExperience::class);
    }

    public function education()
    {
        return $this->hasMany(ApplicantEducation::class);
    }
}
