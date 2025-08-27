<?php

namespace App\Console\Commands;

use App\Mail\ApplicationNotShortlistedMail;
use App\Models\JobRequisition;
use App\Models\ShortlistingSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AutoShortlistCommand extends Command
{
    protected $signature = 'jobs:auto-shortlist {--threshold=} {--requisition-id=} {--force}';
    protected $description = 'Run auto-shortlisting for job requisitions, update statuses and notify non-shortlisted applicants';

    public function handle()
    {
        $requisitionId = $this->option('requisition-id');
        $force = $this->option('force');

        $settings = ShortlistingSetting::first();
        if (!$settings) {
            $this->error("Shortlisting settings not found. Please configure them first.");
            return Command::FAILURE;
        }

        if (!$this->validateSettings($settings)) {
            $this->error("Invalid shortlisting settings configuration.");
            return Command::FAILURE;
        }

        $threshold = $this->option('threshold') 
                     ? (float) $this->option('threshold') 
                     : ($settings->threshold ?? 70);

        // Validate threshold range
        if ($threshold < 0 || $threshold > 100) {
            $this->error("Threshold must be between 0 and 100. Provided: {$threshold}");
            return Command::FAILURE;
        }

        $query = JobRequisition::query();

        if ($requisitionId) {
            $query->where('id', $requisitionId);
            if (!$force) {
                $query->where('auto_shortlisting_completed', false);
            }
        } else {
            $query->where('auto_shortlisting_completed', false);
        }

        // Only process closed jobs
        $query->where('job_status', 'closed');

        $requisitions = $query->get();

        if ($requisitions->isEmpty()) {
            $msg = $requisitionId 
                ? "No job requisition found with ID #{$requisitionId} that needs auto-shortlisting."
                : 'No job requisitions found that need auto-shortlisting.';
            $this->info($msg);
            return Command::SUCCESS;
        }

        $this->info("Starting auto-shortlisting for {$requisitions->count()} job requisition(s) with threshold {$threshold}%...");

        $successCount = 0;
        $failureCount = 0;

        foreach ($requisitions as $requisition) {
            try {
                if (!$force && $requisition->auto_shortlisting_completed) {
                    $this->warn("⚠️ Job Requisition #{$requisition->id} already processed. Skipping...");
                    continue;
                }

                if ($this->processRequisition($requisition, $threshold, $settings, $force)) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Job Requisition #{$requisition->id} failed: {$e->getMessage()}");
                Log::error("Auto-shortlisting failed for Job Requisition #{$requisition->id}: " . $e->getMessage(), [
                    'requisition_id' => $requisition->id,
                    'exception' => $e
                ]);
                $failureCount++;
            }
        }

        $this->info("🎉 Auto-shortlisting completed! Success: {$successCount}, Failures: {$failureCount}");
        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function processRequisition(JobRequisition $requisition, float $threshold, ShortlistingSetting $settings, bool $force = false): bool
    {
        if ($force && $requisition->auto_shortlisting_completed) {
            $this->info("🔄 Job Requisition #{$requisition->id}: Force re-running shortlisting...");
        }

        // Use database transaction for data integrity
        return DB::transaction(function() use ($requisition, $threshold, $settings, $force) {
            $applications = $requisition->applications()
                ->with(['user.skills', 'user.experiences', 'user.education', 'user.qualifications'])
                ->get();

            if ($applications->isEmpty()) {
                $this->warn("⚠️ Job Requisition #{$requisition->id} has no applications.");
                $requisition->update([
                    'auto_shortlisting_completed' => true,
                    'auto_shortlisting_completed_at' => now()
                ]);
                return true;
            }

            $shortlisted = collect();
            $rejected = collect();
            $jobSkills = $requisition->skills ? $requisition->skills->pluck('name')->toArray() : [];
            $totalJobSkills = count($jobSkills);
            $minExperience = (float) ($requisition->min_experience ?? 0);

            foreach ($applications as $application) {
                $user = $application->user;
                if (!$user) {
                    $this->warn("⚠️ Application #{$application->id} has no associated user. Skipping...");
                    continue;
                }

                $scores = $this->calculateApplicationScores($user, $jobSkills, $totalJobSkills, $minExperience, $requisition, $settings);

                // Update or create score record
                $application->score()->updateOrCreate([], [
                    'skills_score'        => $scores['skills_score'],
                    'experience_score'    => $scores['experience_score'],
                    'education_score'     => $scores['education_score'],
                    'qualification_bonus' => $scores['qualification_bonus'],
                    'total_score'         => $scores['total_score'],
                ]);

                // Determine new status
                $potentialThreshold = $threshold - 10; // e.g., 10 points below main threshold

                if ($scores['total_score'] >= $threshold) {
                    $newStatus = 'shortlisted';
                } elseif ($scores['total_score'] >= $potentialThreshold) {
                    $newStatus = 'review';
                } else {
                    $newStatus = 'rejected';
                }
                                $oldStatus = $application->status;
                
                // Update application status
                $application->status = $newStatus;
                $application->saveQuietly();

                // Collect for notifications and logging
                if ($newStatus === 'shortlisted') {
                    $shortlisted->push($application);
                } else {
                    $rejected->push($application);
                }

                // Log status changes for audit
                if ($force && $oldStatus !== $newStatus) {
                    Log::info("Application #{$application->id} status changed from '{$oldStatus}' to '{$newStatus}' (Score: {$scores['total_score']}%)");
                }
            }

            // Update requisition completion status
            $requisition->update([
                'auto_shortlisting_completed' => true,
                'auto_shortlisting_completed_at' => now()
            ]);

            $this->info("✅ Job Requisition #{$requisition->id}: {$shortlisted->count()}/{$applications->count()} shortlisted, {$rejected->count()} rejected.");

            // Send notifications to rejected applicants (if mail class exists and is configured)
         /*    if (class_exists(ApplicationNotShortlistedMail::class) && $rejected->isNotEmpty()) {
                $this->sendRejectionNotifications($rejected, $requisition);
            } */

            return true;
        });
    }

    protected function sendRejectionNotifications($rejectedApplications, JobRequisition $requisition): void
    {
        $notificationCount = 0;
        foreach ($rejectedApplications as $application) {
            try {
               /*  if ($application->user && $application->user->email) {
                    Mail::to($application->user->email)
                        ->send(new ApplicationNotShortlistedMail($application, $requisition));
                    $notificationCount++;
                } */
            } catch (\Exception $e) {
                Log::warning("Failed to send rejection notification to application #{$application->id}: " . $e->getMessage());
            }
        }
        $this->info("📧 Sent {$notificationCount} rejection notifications.");
    }

    protected function calculateApplicationScores($user, array $jobSkills, int $totalJobSkills, float $minExperience, JobRequisition $requisition, ShortlistingSetting $settings): array
    {
        // Skills Score
        $userSkills = $user->skills ? $user->skills->pluck('name')->toArray() : [];
        $matchedSkillsCount = $this->countMatchedSkills($jobSkills, $userSkills);
        $skillsFraction = $this->calculateSkillsFraction($matchedSkillsCount, $totalJobSkills);
        $skillsScore = $skillsFraction * ($settings->skills_weight ?? 0);

        // Experience Score
        $totalExperienceYears = $this->calculateTotalExperienceYears($user);
        $scoringMinExperience = $minExperience > 0 ? max($minExperience, 1) : 1;
        $experienceFraction = $totalExperienceYears <= 0 ? 0 : min($totalExperienceYears / $scoringMinExperience, 1);
        $experienceScore = $experienceFraction * ($settings->experience_weight ?? 0);

        // Education Score
        $requiredEducationLevel = $requisition->required_education_level ?? null;
        $requiredAreasOfStudy = $requisition->required_areas_of_study ?? [];
        $educationPercentage = $this->calculateEducationScore($user, $requiredEducationLevel, $requiredAreasOfStudy);
        $educationFraction = $educationPercentage / 100;
        $educationScore = $educationFraction * ($settings->education_weight ?? 0);

        // Qualification Bonus
        $hasQualification = $user->qualifications && $user->qualifications->isNotEmpty();
        $qualificationBonusScore = $hasQualification ? ($settings->qualification_bonus ?? 0) : 0;

        // Calculate total score (normalized to 100%)
        $totalWeight = ($settings->skills_weight ?? 0) + ($settings->experience_weight ?? 0) + ($settings->education_weight ?? 0);
        
        // Prevent division by zero
        if ($totalWeight <= 0) {
            $this->warn("⚠️ Total weight for scoring is 0. Check shortlisting settings.");
            $totalScore = 0;
        } else {
            $rawTotal = $skillsScore + $experienceScore + $educationScore;
            $baseScore = ($rawTotal / $totalWeight) * 100;
            $totalScore = min($baseScore + (($qualificationBonusScore / $totalWeight) * 100), 100);
        }

        return [
            'skills_score' => round($skillsScore, 2),
            'experience_score' => round($experienceScore, 2),
            'education_score' => round($educationScore, 2),
            'education_percentage' => round($educationPercentage, 2),
            'qualification_bonus' => round($qualificationBonusScore, 2),
            'total_score' => round($totalScore, 2),
        ];
    }
    protected function calculateSkillsFraction(float $matchedSkillsCount, int $totalJobSkills): float
    {
        if ($totalJobSkills == 0) return 1.0; // No skills required = perfect match
        
        $matchPercentage = $matchedSkillsCount / $totalJobSkills;
        
        // Option 2: Boosted scoring - more generous below 60%
        if ($matchPercentage >= 0.6) {
            return 1.0; // 60%+ skills = 100% score
        } elseif ($matchPercentage >= 0.3) {
            // 30-59% skills get boosted scoring
            // Scale 30-60% to 50-100% (more generous boost)
            return 0.5 + (($matchPercentage - 0.3) / 0.3) * 0.5;
        } else {
            // 0-30% skills = proportional (0-30% score)
            return $matchPercentage;
        }
    }

    protected function countMatchedSkills(array $jobSkills, array $userSkills): float
    {
        // Get all keywords from all job skills combined
        $allJobKeywords = $this->getAllKeywords($jobSkills);
        
        // Get all keywords from all user skills combined  
        $allUserKeywords = $this->getAllKeywords($userSkills);
        
        // Find how many job keywords the user has
        $matchingKeywords = array_intersect($allJobKeywords, $allUserKeywords);
        $matchedCount = count($matchingKeywords);
        
        // Return the count of matched keywords
        return (float) $matchedCount;
    }

    protected function getAllKeywords(array $skills): array
    {
        $allKeywords = [];
        
        foreach ($skills as $skill) {
            $keywords = $this->extractKeywords($skill);
            $allKeywords = array_merge($allKeywords, $keywords);
        }
        
        // Remove duplicates - each keyword only counts once
        return array_unique($allKeywords);
    }

    protected function extractKeywords(string $skill): array
    {
        // Convert to lowercase and clean
        $skill = strtolower(trim($skill));
        
        // Remove common noise patterns using regex
        $cleaningPatterns = [
            // Remove experience qualifiers
            '/\b\d+[\+\-]?\s*(years?|yrs?|months?|mo)\s*(of\s*)?(experience|exp)?\b/i',
            
            // Remove skill level descriptors
            '/\b(beginner|intermediate|advanced|expert|proficient|strong|excellent|good|solid|proven|basic)\b/i',
            
            // Remove generic skill terms
            '/\b(skills?|abilities?|knowledge|expertise|experience|exp)\b/i',
            
            // Remove common phrases
            '/\b(working\s+(with|in)|experience\s+(with|in)|knowledge\s+of|familiar\s+with)\b/i',
            
            // Remove version numbers
            '/\b(v\d+|version\s*\d+|\d+\.\d+)\b/i',
            
            // Remove common connecting words
            '/\b(and|or|with|in|of|for|at|to|from|using|through|including|such|as|like|related)\b/i',
            
            // Remove articles and basic verbs
            '/\b(the|a|an|is|are|was|were|be|been|have|has|had|do|does|did|will|would|could|should)\b/i'
        ];
        
        foreach ($cleaningPatterns as $pattern) {
            $skill = preg_replace($pattern, ' ', $skill);
        }
        
        // Clean up extra whitespace
        $skill = preg_replace('/\s+/', ' ', trim($skill));
        
        // Split on various delimiters
        $words = preg_split('/[\s\-_,\/\\\\&|]+/', $skill);
        $keywords = [];
        
        foreach ($words as $word) {
            // Clean punctuation but keep # + . for tech terms like C#, .NET
            $word = trim($word, '.,;:!?()[]{}"\'+');
            
            // Keep meaningful words
            if (strlen($word) >= 2 && 
                !is_numeric($word) &&
                !empty(trim($word))) {
                $keywords[] = $word;
            }
        }
        
        return array_unique(array_filter($keywords));
    }

    // Simple debugging to see keyword matches
    protected function debugSkillMatch(array $jobSkills, array $userSkills): array
    {
        $allJobKeywords = $this->getAllKeywords($jobSkills);
        $allUserKeywords = $this->getAllKeywords($userSkills);
        $matchingKeywords = array_intersect($allJobKeywords, $allUserKeywords);
        $missingKeywords = array_diff($allJobKeywords, $allUserKeywords);
        
        $debug = [
            'job_skills' => $jobSkills,
            'user_skills' => $userSkills,
            'all_job_keywords' => array_values($allJobKeywords),
            'all_user_keywords' => array_values($allUserKeywords),
            'matching_keywords' => array_values($matchingKeywords),
            'missing_keywords' => array_values($missingKeywords),
            'match_count' => count($matchingKeywords),
            'total_job_keywords' => count($allJobKeywords),
            'match_percentage' => count($allJobKeywords) > 0 ? (count($matchingKeywords) / count($allJobKeywords)) * 100 : 0,
            'final_score' => $this->calculateSkillsFraction(count($matchingKeywords), count($allJobKeywords))
        ];
        
        return $debug;
    }

    protected function calculateTotalExperienceYears($user): float
    {
        if (!$user->experiences || $user->experiences->isEmpty()) {
            return 0.0;
        }

        $periods = [];
        foreach ($user->experiences as $exp) {
            if (!$exp->start_date) continue;
            
            try {
                $start = Carbon::parse($exp->start_date);
                $end = $exp->end_date ? Carbon::parse($exp->end_date) : Carbon::now();
                
                // Skip invalid date ranges
                if ($end->lt($start)) continue;
                
                $periods[] = ['start' => $start, 'end' => $end];
            } catch (\Exception $e) {
                // Skip invalid dates
                Log::warning("Invalid date in experience for user {$user->id}: " . $e->getMessage());
                continue;
            }
        }

        if (empty($periods)) return 0.0;

        // Sort periods by start date
        usort($periods, fn($a, $b) => $a['start']->timestamp <=> $b['start']->timestamp);

        // Merge overlapping periods
        $totalDays = 0;
        $current = $periods[0];

        for ($i = 1; $i < count($periods); $i++) {
            $next = $periods[$i];
            
            // If next period overlaps with current, merge them
            if ($next['start']->lte($current['end'])) {
                $current['end'] = $current['end']->gt($next['end']) ? $current['end'] : $next['end'];
            } else {
                // No overlap, add current period to total and move to next
                $totalDays += $current['start']->diffInDays($current['end']) + 1; // +1 to include both start and end days
                $current = $next;
            }
        }

        // Add the last period
        $totalDays += $current['start']->diffInDays($current['end']) + 1;

        return round($totalDays / 365.25, 1); // Account for leap years
    }

    protected function mapEducationLevelHierarchy(): array
    {
        // Higher index = higher level
        return [
            'High School' => 1,
            'Certificate' => 2,
            'Diploma' => 3,
            'Associate Degree' => 4,
            "Bachelor's Degree" => 5,
            'Postgraduate Diploma' => 6,
            "Master's Degree" => 7,
            'Doctorate (PhD)' => 8,
        ];
    }
    
    protected function calculateEducationScore($user, $requiredEducationLevel, $requiredAreasOfStudy = []): float
    {
        if (!$user->education || $user->education->isEmpty()) {
            return $requiredEducationLevel ? 0.0 : 100.0;
        }
        
        if (!$requiredEducationLevel) {
            return 100.0; // No education requirement = perfect score
        }
    
        $hierarchy = $this->mapEducationLevelHierarchy();
        $requiredRank = $hierarchy[trim($requiredEducationLevel)] ?? 0;
        $bestScore = 0.0;
    
        foreach ($user->education as $education) {
            $educationLevel = $education->education_level ?? null;
            $educationStatus = strtolower(trim($education->status ?? ''));
            $fieldOfStudy = $education->field_of_study ?? null;
            $hasEndDate = !empty($education->end_date);
    
            if (!$educationLevel) continue;
    
            $applicantRank = $hierarchy[trim($educationLevel)] ?? 0;
    
            // Check requirements
            $meetsLevelRequirement = $applicantRank >= $requiredRank;
            $meetsFieldRequirement = $this->checkFieldOfStudyMatch($fieldOfStudy, $requiredAreasOfStudy);
            $isComplete = ($educationStatus === 'complete' && $hasEndDate);
    
            $currentScore = 0.0;
    
            // Scoring logic
            if ($meetsLevelRequirement && $meetsFieldRequirement && $isComplete) {
                $currentScore = 100; // Perfect match
            } elseif ($meetsLevelRequirement && $meetsFieldRequirement) {
                $currentScore = 70; // Good match but not complete
            } elseif ($meetsLevelRequirement && $isComplete) {
                $currentScore = 40; // Right level, wrong field, but complete
            } elseif ($meetsFieldRequirement && $isComplete) {
                $currentScore = 30; // Right field, lower level, complete
            } elseif ($meetsLevelRequirement) {
                $currentScore = 25; // Right level, wrong field, incomplete
            } elseif ($meetsFieldRequirement) {
                $currentScore = 15; // Right field, lower level, incomplete
            } elseif ($isComplete) {
                $currentScore = 10; // Wrong field and level, but at least complete
            }
    
            $bestScore = max($bestScore, $currentScore);
        }
    
        return round($bestScore, 2);
    }

    protected function checkFieldOfStudyMatch($userFieldOfStudy, $requiredAreasOfStudy): bool
    {
        if (empty($requiredAreasOfStudy) || !is_array($requiredAreasOfStudy)) {
            return true; // No field requirement
        }
        
        if (empty($userFieldOfStudy)) {
            return false; // User has no field specified
        }

        $userField = strtolower(trim($userFieldOfStudy));
        
        foreach ($requiredAreasOfStudy as $requiredArea) {
            if (empty($requiredArea)) continue;
            
            $requiredField = strtolower(trim($requiredArea));
            
            // Exact match
            if ($userField === $requiredField) return true;
            
            // Partial match (contains)
            if (strpos($userField, $requiredField) !== false || strpos($requiredField, $userField) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function validateSettings(ShortlistingSetting $settings): bool
    {
        $requiredFields = ['skills_weight', 'experience_weight', 'education_weight'];
        
        foreach ($requiredFields as $field) {
            if (!isset($settings->$field) || !is_numeric($settings->$field) || $settings->$field < 0) {
                $this->error("Invalid or missing setting: {$field}");
                return false;
            }
        }
        
        // Check if qualification_bonus exists and is valid
        if (isset($settings->qualification_bonus) && (!is_numeric($settings->qualification_bonus) || $settings->qualification_bonus < 0)) {
            $this->error("Invalid qualification_bonus setting");
            return false;
        }
        
        return true;
    }
}