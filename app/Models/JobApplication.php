<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_requisition_id',
        'status',
        'submitted_at',
        'notes',
        'uuid',
        'score',
        'score_breakdown', // new  // add uuid here so you can mass assign if needed
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'score' => 'float',
        'score_breakdown' => 'array',
    ];

    // Keep default primaryKey = 'id', no override

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::created(function ($application) {
            // Run auto-shortlist for the job requisition when a new application is created
            $application->jobRequisition->autoShortlistApplicants();
        });
    
        static::updated(function ($application) {
            // Optionally also run after updates if needed
            $application->jobRequisition->autoShortlistApplicants();
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobRequisition()
    {
        return $this->belongsTo(JobRequisition::class);
    }

    public function reviews()
    {
        return $this->hasMany(ApplicationReview::class, 'job_application_id');
    }

    public function interviews()
{
    return $this->hasOne(Interview::class);
}

}
