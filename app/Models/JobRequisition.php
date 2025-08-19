<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // Don't forget to import Log

class JobRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'slug',
        'reference_number',
        'created_by',
        'department_id',
        'title',
        'description',
        'requirements',
        'vacancies',
        'location',
        'employment_type',
        'application_deadline',
        'approval_status',
        'min_experience',
        'education_level',
        'job_status',
        'approved_by',
        'approved_at',
        'auto_shortlisting_completed',
        'auto_shortlisting_completed_at',
    ];

    protected $casts = [
        'application_deadline' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Automatically assign UUID and reference number
    protected static function booted()
    {
        static::creating(function ($job) {
            // Always assign a UUID
            $job->uuid = Str::uuid();
    
            // Initial slug
            $job->slug = Str::slug($job->title);
        });
    
        static::updating(function ($job) {
            // Update slug if title changes
            if ($job->isDirty('title')) {
                $job->slug = Str::slug($job->title);
            }
        });
    
        static::created(function ($job) {
            // Generate reference number after ID is known
            $job->reference_number = 'JOB-' . str_pad($job->id, 5, '0', STR_PAD_LEFT);
    
            // Save quietly to avoid infinite loop
            $job->saveQuietly();
        });
    }
    public function getSlugUuidAttribute()
    {
        return $this->slug . '-' . $this->uuid;
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_requisition_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function skills()
{
    return $this->belongsToMany(Skill::class, 'job_requisition_skill');
}



    public function getShortlistedApplicants(float $percentageThreshold = 70)
    {
        return $this->applications()
            ->where('status', 'shortlisted')
            ->where('score', '>=', $percentageThreshold)
            ->orderByDesc('score')
            ->get();
    }

}
