<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantQualifications extends Model
{
    protected $table = 'qualifications';

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'institution',
        'issued_date',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
