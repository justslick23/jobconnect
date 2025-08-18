<?php

namespace App\Console\Commands;

use App\Mail\ApplicationNotShortlistedMail;
use App\Models\JobRequisition;
use App\Models\ShortlistingSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AutoShortlistCommand extends Command
{
    protected $signature = 'jobs:auto-shortlist {--threshold=70} {--requisition-id=} {--force}';
    protected $description = 'Run auto-shortlisting for job requisitions, update statuses and notify non-shortlisted applicants';

    public function handle()
    {
        $threshold = (float) $this->option('threshold');
        $requisitionId = $this->option('requisition-id');
        $force = $this->option('force');

        // Load shortlisting settings
        $settings = ShortlistingSetting::first();

        if (!$settings) {
            $this->error("Shortlisting settings not found. Please configure them first.");
            return Command::FAILURE;
        }

        // Validate settings weights
        if (!$this->validateSettings($settings)) {
            $this->error("Invalid shortlisting settings configuration.");
            return Command::FAILURE;
        }

        $query = JobRequisition::query();

        if ($requisitionId) {
            $query->where('id', $requisitionId);
            
            // If specific requisition ID is provided, check if it's already been processed
            if (!$force) {
                $query->where('auto_shortlisting_completed', false);
            }
        } else {
            // For bulk processing, only process requisitions that haven't been completed
            $query->where('application_deadline', '<', now())
                  ->where('auto_shortlisting_completed', false);
        }

        $requisitions = $query->get();

        // Additional check for already processed requisitions when using --requisition-id
        if ($requisitionId && !$force) {
            $alreadyProcessed = JobRequisition::where('id', $requisitionId)
                ->where('auto_shortlisting_completed', true)
                ->first();
                
            if ($alreadyProcessed) {
                $this->warn("⚠️ Job Requisition #{$requisitionId} has already been processed for auto-shortlisting.");
                $this->info("💡 Use --force flag to re-run shortlisting for this requisition.");
                return Command::SUCCESS;
            }
        }

        if ($requisitions->isEmpty()) {
            if ($requisitionId) {
                $this->info("No job requisition found with ID #{$requisitionId} that needs auto-shortlisting.");
            } else {
                $this->info('No job requisitions found that need auto-shortlisting.');
            }
            return Command::SUCCESS;
        }

        $this->info("Starting auto-shortlisting for {$requisitions->count()} job requisition(s)...");

        $successCount = 0;
        $failureCount = 0;

        foreach ($requisitions as $requisition) {
            try {
                // Double-check before processing (race condition protection)
                if (!$force && $requisition->auto_shortlisting_completed) {
                    $this->warn("⚠️ Job Requisition #{$requisition->id} was already processed. Skipping...");
                    continue;
                }

                if ($this->processRequisition($requisition, $threshold, $settings, $force)) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Job Requisition #{$requisition->id}: Failed - {$e->getMessage()}");
                Log::error("Auto-shortlisting failed for Job Requisition #{$requisition->id}: " . $e->getMessage());
                $failureCount++;
            }
        }

        $this->info("🎉 Auto-shortlisting process completed! Success: {$successCount}, Failures: {$failureCount}");
        
        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Process a single job requisition
     */
    protected function processRequisition(JobRequisition $requisition, float $threshold, ShortlistingSetting $settings, bool $force = false): bool
    {
        // Check if already processed (unless forced)
        if (!$force && $requisition->auto_shortlisting_completed) {
            $this->warn("⚠️ Job Requisition #{$requisition->id} ({$requisition->title}): Already processed. Use --force to re-run.");
            return true;
        }

        // If forcing re-run, log it
        if ($force && $requisition->auto_shortlisting_completed) {
            $this->info("🔄 Job Requisition #{$requisition->id} ({$requisition->title}): Force re-running shortlisting...");
        }

        // Eager load user and user relations
        $applications = $requisition->applications()
            ->with(['user.skills', 'user.experiences', 'user.education', 'user.qualifications'])
            ->get();
        
        $applicationsCount = $applications->count();

        if ($applicationsCount === 0) {
            $this->warn("⚠️ Job Requisition #{$requisition->id} ({$requisition->title}): No applications to process");
            $requisition->update([
                'auto_shortlisting_completed' => true,
                'auto_shortlisting_completed_at' => now()
            ]);
            return true;
        }

        $shortlisted = collect();

        // Load required skills for the job requisition
        $jobSkills = $requisition->skills ? $requisition->skills->pluck('name')->toArray() : [];
        $totalJobSkills = count($jobSkills);
        $minExperience = (float) ($requisition->min_experience ?? 0);

        foreach ($applications as $application) {
            $user = $application->user;

            if (!$user) {
                $this->warn("⚠️ Application #{$application->id} has no associated user.");
                continue;
            }

            // Calculate scores
            $scores = $this->calculateApplicationScores($user, $jobSkills, $totalJobSkills, $minExperience, $requisition, $settings);

            // Save score and update status if meets threshold
            $application->score()->updateOrCreate([], [
                'skills_score'        => $scores['skills_score'],
                'experience_score'    => $scores['experience_score'],
                'education_score'     => $scores['education_score'],
                'qualification_bonus' => $scores['qualification_bonus'],
                'total_score'         => $scores['total_score'],
            ]);
            
            if ($scores['total_score'] >= $threshold) {
                $application->status = 'shortlisted';
                $shortlisted->push($application);
            }

            $application->saveQuietly();
        }

        // Process non-shortlisted applications (only if not forcing or if it's the first run)
        $notShortlistedCount = 0;
        if (!$force || !$requisition->auto_shortlisting_completed) {
            $notShortlistedCount = $this->processNonShortlistedApplications($requisition);
        }

        // Mark requisition as completed
        $requisition->update([
            'auto_shortlisting_completed' => true,
            'auto_shortlisting_completed_at' => now()
        ]);

        $actionText = $force && $requisition->wasChanged() === false ? "Re-processed" : "Processed";
        $this->info("✅ Job Requisition #{$requisition->id} ({$requisition->title}): {$actionText} - {$shortlisted->count()}/{$applicationsCount} shortlisted" . 
                   ($notShortlistedCount > 0 ? ", {$notShortlistedCount} rejected & notified." : "."));

        return true;
    }

    /**
     * Calculate all scores for an application
     */
    protected function calculateApplicationScores($user, array $jobSkills, int $totalJobSkills, float $minExperience, JobRequisition $requisition, ShortlistingSetting $settings): array
    {
        // Skills scoring: fraction 0..1 * skills_weight
        $userSkills = $user->skills ? $user->skills->pluck('name')->toArray() : [];
        $matchedSkillsCount = count(array_intersect($jobSkills, $userSkills));
        $skillsFraction = $totalJobSkills > 0 ? $matchedSkillsCount / $totalJobSkills : 1;
        $skillsScore = $skillsFraction * ($settings->skills_weight ?? 0);

        // Experience scoring: fraction 0..1 * experience_weight
        $totalExperienceYears = $this->calculateTotalExperienceYears($user);

        // Enforce a minimum baseline of 1 year for experience scoring
        $scoringMinExperience = $minExperience > 0 ? max($minExperience, 1) : 1;

        if ($totalExperienceYears <= 0) {
            $experienceFraction = 0;
        } else {
            $experienceFraction = min($totalExperienceYears / $scoringMinExperience, 1);
        }
        $experienceScore = $experienceFraction * ($settings->experience_weight ?? 0);

        // Education scoring: calculateEducationScore returns 0-100, convert to fraction 0..1 * education_weight
        $requiredEducationLevel = $requisition->required_education_level ?? null;
        $educationFraction = $this->calculateEducationScore($user, $requiredEducationLevel) / 100;
        $educationScore = $educationFraction * ($settings->education_weight ?? 0);

        // Qualification bonus - add directly (make sure it fits in the total 100 scale)
        $hasQualification = $user->qualifications && $user->qualifications->isNotEmpty();
        $qualificationBonusScore = $hasQualification ? ($settings->qualification_bonus ?? 0) : 0;

        // Sum all to get total score out of 100
        $totalWeight = ($settings->skills_weight ?? 0) + ($settings->experience_weight ?? 0) + ($settings->education_weight ?? 0) + ($settings->qualification_bonus ?? 0);

        $rawTotal = $skillsScore + $experienceScore + $educationScore + $qualificationBonusScore;
        
        $totalScore = $totalWeight > 0 ? min(($rawTotal / $totalWeight) * 100, 100) : 0;
        
        return [
            'skills_score' => round($skillsScore, 2),          // weighted score (e.g., 0-30)
            'experience_score' => round($experienceScore, 2),  // weighted score (e.g., 0-25)
            'education_score' => round($educationScore, 2),    // weighted score (e.g., 0-25)
            'education_percentage' => round($educationFraction * 100, 2), // raw percentage 0-100
            'qualification_bonus' => round($qualificationBonusScore, 2), // weighted bonus
            'total_score' => round($totalScore, 2),            // sum total max 100
        ];
        
    }

    /**
     * Process applications that were not shortlisted
     */
    protected function processNonShortlistedApplications(JobRequisition $requisition): int
    {
        $notShortlisted = $requisition->applications()
            ->whereNotIn('status', ['shortlisted', 'hired', 'offer sent'])
            ->with('user')
            ->get();

        $processedCount = 0;

        foreach ($notShortlisted as $application) {
            try {
                $application->status = 'rejected';
                $application->saveQuietly();

                if ($application->user && $application->user->email) {
                    Mail::to($application->user->email)->send(
                        new ApplicationNotShortlistedMail($application->user->name, $requisition->title)
                    );
                    $processedCount++;
                }
            } catch (\Exception $mailException) {
                $this->warn("⚠️ Failed to email {$application->user->email}: {$mailException->getMessage()}");
                Log::error("Email failure for application ID {$application->id}: " . $mailException->getMessage());
            }
        }

        return $processedCount;
    }

    /**
     * Calculate education score by comparing user's highest qualification 
     * against job requisition's required education level
     */
    protected function calculateEducationScore($user, $requiredEducationLevel): float
    {
        if (!$user->education || $user->education->isEmpty()) {
            return 0.0;
        }

        // If no required education level is specified, give full score
        if (!$requiredEducationLevel) {
            return 100.0;
        }

        $requiredScore = $this->mapEducationLevelToScore($requiredEducationLevel);
        $userHighestScore = 0.0;

        foreach ($user->education as $education) {
            $educationLevel = $education->education_level ?? null;
            $educationEndDate = $education->end_date ?? null;
            
            if (!$educationLevel) {
                continue;
            }

            $educationScoreRaw = $this->mapEducationLevelToScore($educationLevel);
            
            // If education is still in progress (no end_date), apply a penalty
            if (!$educationEndDate) {
                // Award 70% of the full score for ongoing education
                $educationScoreRaw = $educationScoreRaw * 0.7;
            }

            // Keep track of the highest score
            $userHighestScore = max($userHighestScore, $educationScoreRaw);
        }

        // Compare user's highest qualification against required level
        if ($requiredScore == 0) {
            // If required level is invalid, give full score
            return 100.0;
        }

        // Calculate percentage based on comparison
        $score = min(($userHighestScore / $requiredScore) * 100, 100);
        
        return round($score, 2);
    }

    /**
     * Calculate total years of experience for a user based on their experience entries
     * This method sums up individual job durations and handles overlapping periods
     */
    protected function calculateTotalExperienceYears($user): float
    {
        if (!$user->experiences || $user->experiences->isEmpty()) {
            return 0.0;
        }
    
        try {
            $periods = [];
            
            // Create periods array with start and end dates
            foreach ($user->experiences as $experience) {
                if (!$experience->start_date) {
                    continue;
                }
                
                $startDate = Carbon::parse($experience->start_date);
                $endDate = $experience->end_date 
                    ? Carbon::parse($experience->end_date) 
                    : Carbon::now(); // If no end date, assume current
                
                // Skip invalid periods
                if ($endDate->lt($startDate)) {
                    continue;
                }
                
                $periods[] = [
                    'start' => $startDate,
                    'end' => $endDate
                ];
            }
            
            if (empty($periods)) {
                return 0.0;
            }
            
            usort($periods, function($a, $b) {
                return $a['start']->timestamp <=> $b['start']->timestamp;
            });
            
            // Merge overlapping periods and calculate total
            $totalDays = 0;
            $currentStart = $periods[0]['start'];
            $currentEnd = $periods[0]['end'];
            
            for ($i = 1; $i < count($periods); $i++) {
                $period = $periods[$i];
                
                // If current period overlaps with the merged period
                if ($period['start']->lte($currentEnd)) {
                    // Extend the end date if necessary
                    $currentEnd = $currentEnd->gt($period['end']) ? $currentEnd : $period['end'];
                } else {
                    // No overlap, add the current merged period to total
                    $totalDays += $currentStart->diffInDays($currentEnd);
                    
                    // Start a new merged period
                    $currentStart = $period['start'];
                    $currentEnd = $period['end'];
                }
            }
            
            // Add the last merged period
            $totalDays += $currentStart->diffInDays($currentEnd);
            
            // Convert days to years (more accurate than using diffInYears)
            $totalYears = $totalDays / 365.25; // Account for leap years
            
            return round($totalYears, 1);
            
        } catch (\Exception $e) {
            Log::warning("Error calculating experience years for user {$user->id}: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Map education level string to numeric score (0-10 scale)
     */
    protected function mapEducationLevelToScore($level): float
    {
        $levels = [
            'High School'          => 5.0,
            'Certificate'          => 6.0,
            'Diploma'              => 7.0,
            'Associate Degree'     => 7.5,
            "Bachelor's Degree"    => 9.0,
            'Postgraduate Diploma' => 9.5,
            "Master's Degree"      => 10.0,
            'Doctorate (PhD)'      => 10.0,
        ];
    
        $key = trim($level);
        return $levels[$key] ?? 0.0;
    }

    /**
     * Validate shortlisting settings
     */
    protected function validateSettings(ShortlistingSetting $settings): bool
    {
        $requiredFields = ['skills_weight', 'experience_weight', 'education_weight'];
        
        foreach ($requiredFields as $field) {
            if (!isset($settings->$field) || !is_numeric($settings->$field)) {
                return false;
            }
        }

        return true;
    }
}