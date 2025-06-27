<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // === ROLES & PERMISSIONS ===

    public function role()
{
    return $this->belongsTo(Role::class);
}

public function hasRole(string $roleName): bool
{
    return $this->role && strtolower($this->role->name) === strtolower($roleName);
}

public function isManager(): bool
{
    return $this->hasRole('Manager');
}

public function isHrAdmin(): bool
{
    return $this->hasRole('Admin');
}

public function isApplicant(): bool
{
    return $this->hasRole('Applicant');
}


public function isReviewer(): bool
{
    return $this->hasRole('Reviewer');
}

    // === RELATIONSHIPS ===

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_manager', 'user_id', 'department_id')->withTimestamps();
    }

    public function reviewApplications()
    {
        return $this->belongsToMany(JobApplication::class, 'application_user', 'user_id', 'application_id')->withTimestamps();
    }

    public function profile()
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function skills()
    {
        return $this->hasMany(ApplicantSkill::class);
    }
    
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function education()
    {
        return $this->hasMany(ApplicantEducation::class);
    }

    public function experiences()
    {
        return $this->hasMany(ApplicantExperience::class);
    }

    public function references()
    {
        return $this->hasMany(ApplicantReferences::class);
    }

    public function qualifications()
    {
        return $this->hasMany(ApplicantQualifications::class);
    }

    public function attachments()
    {
        return $this->hasMany(ApplicationAttachment::class);
    }

    
public function totalExperienceYears()
{
    return $this->experiences->sum(function ($experience) {
        $start = Carbon::parse($experience->start_date);
        $end = $experience->end_date ? Carbon::parse($experience->end_date) : Carbon::now();
        return $start->diffInMonths($end) / 12; // convert months to years (float)
    });
}

/**
 * Count how many skills the user has that match the required skill names.
 *
 * @param array $requiredSkillNames
 * @return int
 */
public function matchedSkillsCount(array $requiredSkillNames): int
{
    // Get user's skill names as a plain array (lowercase for case-insensitive matching)
    $userSkillNames = $this->skills->pluck('name')->map(fn($name) => strtolower($name))->toArray();

    // Normalize required skill names (lowercase)
    $requiredSkillsLower = array_map('strtolower', $requiredSkillNames);

    // Count intersection of user's skills and required skills
    return count(array_intersect($userSkillNames, $requiredSkillsLower));
}
}
