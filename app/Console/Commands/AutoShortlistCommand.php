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
    // CLI signature - threshold is optional now
    protected $signature = 'jobs:auto-shortlist {--threshold=} {--requisition-id=} {--force}';
    protected $description = 'Run auto-shortlisting for job requisitions, update statuses and notify non-shortlisted applicants';

    public function handle()
    {
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

        // --- Use CLI threshold if provided, otherwise take from settings ---
        $threshold = $this->option('threshold') 
                     ? (float) $this->option('threshold') 
                     : ($settings->threshold ?? 70); // default fallback

        $query = JobRequisition::query();

        if ($requisitionId) {
            $query->where('id', $requisitionId);
            
            if (!$force) {
                $query->where('auto_shortlisting_completed', false);
            }
        } else {
            $query->where('application_deadline', '<', now())
                  ->where('auto_shortlisting_completed', false);
        }

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
                Log::error("Auto-shortlisting failed for Job Requisition #{$requisition->id}: " . $e->getMessage());
                $failureCount++;
            }
        }

        $this->info("🎉 Auto-shortlisting completed! Success: {$successCount}, Failures: {$failureCount}");
        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function processRequisition(JobRequisition $requisition, float $threshold, ShortlistingSetting $settings, bool $force = false): bool
    {
        if (!$force && $requisition->auto_shortlisting_completed) {
            $this->warn("⚠️ Job Requisition #{$requisition->id} already processed.");
            return true;
        }

        if ($force && $requisition->auto_shortlisting_completed) {
            $this->info("🔄 Job Requisition #{$requisition->id}: Force re-running shortlisting...");
        }

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
        $jobSkills = $requisition->skills ? $requisition->skills->pluck('name')->toArray() : [];
        $totalJobSkills = count($jobSkills);
        $minExperience = (float) ($requisition->min_experience ?? 0);

        foreach ($applications as $application) {
            $user = $application->user;
            if (!$user) continue;

            $scores = $this->calculateApplicationScores($user, $jobSkills, $totalJobSkills, $minExperience, $requisition, $settings);

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

        $notShortlistedCount = 0;
        if (!$force || !$requisition->auto_shortlisting_completed) {
            $notShortlistedCount = $this->processNonShortlistedApplications($requisition);
        }

        $requisition->update([
            'auto_shortlisting_completed' => true,
            'auto_shortlisting_completed_at' => now()
        ]);

        $this->info("✅ Job Requisition #{$requisition->id}: {$shortlisted->count()}/{$applications->count()} shortlisted, {$notShortlistedCount} rejected.");

        return true;
    }

    protected function calculateApplicationScores($user, array $jobSkills, int $totalJobSkills, float $minExperience, JobRequisition $requisition, ShortlistingSetting $settings): array
    {
        $userSkills = $user->skills ? $user->skills->pluck('name')->toArray() : [];
        $matchedSkillsCount = count(array_intersect($jobSkills, $userSkills));
        $skillsFraction = $totalJobSkills > 0 ? $matchedSkillsCount / $totalJobSkills : 1;
        $skillsScore = $skillsFraction * ($settings->skills_weight ?? 0);

        $totalExperienceYears = $this->calculateTotalExperienceYears($user);
        $scoringMinExperience = $minExperience > 0 ? max($minExperience, 1) : 1;
        $experienceFraction = $totalExperienceYears <= 0 ? 0 : min($totalExperienceYears / $scoringMinExperience, 1);
        $experienceScore = $experienceFraction * ($settings->experience_weight ?? 0);

        $requiredEducationLevel = $requisition->required_education_level ?? null;
        $educationFraction = $this->calculateEducationScore($user, $requiredEducationLevel) / 100;
        $educationScore = $educationFraction * ($settings->education_weight ?? 0);

        $hasQualification = $user->qualifications && $user->qualifications->isNotEmpty();
        $qualificationBonusScore = $hasQualification ? ($settings->qualification_bonus ?? 0) : 0;

        $totalWeight = ($settings->skills_weight ?? 0) + ($settings->experience_weight ?? 0) + ($settings->education_weight ?? 0) + ($settings->qualification_bonus ?? 0);
        $rawTotal = $skillsScore + $experienceScore + $educationScore + $qualificationBonusScore;
        $totalScore = $totalWeight > 0 ? min(($rawTotal / $totalWeight) * 100, 100) : 0;

        return [
            'skills_score' => round($skillsScore, 2),
            'experience_score' => round($experienceScore, 2),
            'education_score' => round($educationScore, 2),
            'education_percentage' => round($educationFraction * 100, 2),
            'qualification_bonus' => round($qualificationBonusScore, 2),
            'total_score' => round($totalScore, 2),
        ];
    }

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

    protected function calculateEducationScore($user, $requiredEducationLevel): float
    {
        if (!$user->education || $user->education->isEmpty()) return 0.0;
        if (!$requiredEducationLevel) return 100.0;

        $requiredScore = $this->mapEducationLevelToScore($requiredEducationLevel);
        $userHighestScore = 0.0;

        foreach ($user->education as $education) {
            $educationLevel = $education->education_level ?? null;
            $educationEndDate = $education->end_date ?? null;
            $educationStatus = strtolower($education->status ?? '');

            if (!$educationLevel) continue;

            $educationScoreRaw = $this->mapEducationLevelToScore($educationLevel);
            if ($educationStatus !== 'complete' || !$educationEndDate) {
                $educationScoreRaw *= 0.7;
            }

            $userHighestScore = max($userHighestScore, $educationScoreRaw);
        }

        if ($requiredScore == 0) return 100.0;
        return round(min(($userHighestScore / $requiredScore) * 100, 100), 2);
    }

    protected function calculateTotalExperienceYears($user): float
    {
        if (!$user->experiences || $user->experiences->isEmpty()) return 0.0;

        try {
            $periods = [];
            foreach ($user->experiences as $experience) {
                if (!$experience->start_date) continue;

                $startDate = Carbon::parse($experience->start_date);
                $endDate = $experience->end_date ? Carbon::parse($experience->end_date) : Carbon::now();
                if ($endDate->lt($startDate)) continue;

                $periods[] = ['start' => $startDate, 'end' => $endDate];
            }

            if (empty($periods)) return 0.0;

            usort($periods, fn($a, $b) => $a['start']->timestamp <=> $b['start']->timestamp);

            $totalDays = 0;
            $currentStart = $periods[0]['start'];
            $currentEnd = $periods[0]['end'];

            for ($i = 1; $i < count($periods); $i++) {
                $period = $periods[$i];
                if ($period['start']->lte($currentEnd)) {
                    $currentEnd = $currentEnd->gt($period['end']) ? $currentEnd : $period['end'];
                } else {
                    $totalDays += $currentStart->diffInDays($currentEnd);
                    $currentStart = $period['start'];
                    $currentEnd = $period['end'];
                }
            }

            $totalDays += $currentStart->diffInDays($currentEnd);
            $totalYears = $totalDays / 365.25;
            return round($totalYears, 1);

        } catch (\Exception $e) {
            Log::warning("Error calculating experience years for user {$user->id}: " . $e->getMessage());
            return 0.0;
        }
    }

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

    protected function validateSettings(ShortlistingSetting $settings): bool
    {
        $requiredFields = ['skills_weight', 'experience_weight', 'education_weight'];
        foreach ($requiredFields as $field) {
            if (!isset($settings->$field) || !is_numeric($settings->$field)) return false;
        }
        return true;
    }
}
