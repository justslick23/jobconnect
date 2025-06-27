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
    ];

    protected $casts = [
        'application_deadline' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Automatically assign UUID and reference number
    protected static function booted()
    {
        static::creating(function ($job) {
            $job->uuid = Str::uuid();
        });

        static::created(function ($job) {
            $job->reference_number = 'JOB-' . str_pad($job->id, 5, '0', STR_PAD_LEFT);
            $job->saveQuietly(); // avoid infinite loop
        });
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
public function autoShortlistApplicants(float $percentageThreshold = 70)
{
    $requiredSkillNames = $this->skills()->pluck('name')->toArray();
    $requiredEducationLevel = $this->education_level;
    $minExperienceYears = max($this->min_experience, 1);

    $maxSkillPoints = count($requiredSkillNames) > 0 ? 100 : 0;
    $maxExperiencePoints = 50;
    $maxEducationPoints = 50;
    $maxQualificationBonus = 20;

    $totalMaxPoints = $maxSkillPoints + $maxExperiencePoints + $maxEducationPoints + $maxQualificationBonus;

    // Education levels map for comparison
    $educationLevelsMap = [
        'Other' => 0,
        'High School' => 1,
        'Certificate' => 2,
        'Diploma' => 3,
        'Associate Degree' => 4,
        "Bachelor's Degree" => 5,
        'Postgraduate Diploma' => 6,
        "Master's Degree" => 7,
        'Doctorate (PhD)' => 8,
    ];

    $applications = $this->applications()->with([
        'user.skills',
        'user.experiences',
        'user.education',
        'user.qualifications',
    ])->get();

    $scored = $applications->map(function ($application) use (
        $requiredSkillNames,
        $requiredEducationLevel,
        $minExperienceYears,
        $maxSkillPoints,
        $maxExperiencePoints,
        $maxEducationPoints,
        $maxQualificationBonus,
        $totalMaxPoints,
        $educationLevelsMap
    ) {
        $user = $application->user;

        // Skills score
        if ($maxSkillPoints > 0) {
            $matchedSkillsCount = $user->skills->whereIn('name', $requiredSkillNames)->count();
            $totalRequiredSkills = count($requiredSkillNames);
            $skillRatio = $matchedSkillsCount / $totalRequiredSkills;
            $skillScore = $skillRatio * $maxSkillPoints;
        } else {
            // No required skills, so no skill points
            $skillScore = 0;
        }

        // Experience score
        $totalExperience = $user->totalExperienceYears();
        $experienceRatio = min($totalExperience / $minExperienceYears, 1);
        $experienceScore = $experienceRatio * $maxExperiencePoints;

        // Education score
        $userEducationLevels = $user->education->pluck('education_level')->toArray();
        $highestEducation = collect($userEducationLevels)->max();

        $educationScore = 0;
        if ($highestEducation !== null) {
            $requiredRank = $educationLevelsMap[$requiredEducationLevel] ?? 0;
            $applicantRank = $educationLevelsMap[$highestEducation] ?? 0;

            if ($applicantRank >= $requiredRank) {
                $educationScore = $maxEducationPoints;
            }
        }

        // Qualification bonus
        $qualificationsCount = $user->qualifications->count();
        $qualificationBonus = min($qualificationsCount * 3, $maxQualificationBonus);

        // Total score
        $totalScore = $skillScore + $experienceScore + $educationScore + $qualificationBonus;

        $percentageScore = $totalMaxPoints > 0
            ? ($totalScore / $totalMaxPoints) * 100
            : 0;

        // Persist score + breakdown
        $application->score = round($percentageScore, 2);
        $application->score_breakdown = [
            'skills' => round($skillScore, 2),
            'experience' => round($experienceScore, 2),
            'education' => round($educationScore, 2),
            'qualifications' => round($qualificationBonus, 2),
            'total' => round($totalScore, 2),
        ];

        return $application;
    });

    // Filter and update shortlisted
    $shortlisted = $scored->filter(fn($app) => $app->score >= $percentageThreshold);


    // Not shortlisted - still save score and breakdown
    $scored->reject(fn($app) => $app->score >= $percentageThreshold)
        ->each(function ($application) {
            $application->saveQuietly();
            Log::info("❌ Not shortlisted: App #{$application->id}, Score: {$application->score}%");
        });

    return $shortlisted->sortByDesc('score')->values();
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
