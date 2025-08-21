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

                    // Filter by specific requisition ID if provided
                    if ($requisitionId) {
                        $query->where('id', $requisitionId);

                        if (!$force) {
                            $query->where('auto_shortlisting_completed', false);
                        }

                        // Only run if job is closed
                        $query->where('job_status', 'closed');

                    } else {
                        // Only closed jobs that haven't been short-listed yet
                        $query->where('job_status', 'closed')
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
        // Enhanced skills matching with more lenient scoring and keyword matching
        $userSkills = $user->skills ? $user->skills->pluck('name')->toArray() : [];
        $matchedSkillsCount = $this->countMatchedSkills($jobSkills, $userSkills);
        
        // More lenient skills scoring - full marks for matching a reasonable portion
        $skillsFraction = $this->calculateSkillsFraction($matchedSkillsCount, $totalJobSkills);
        $skillsScore = $skillsFraction * ($settings->skills_weight ?? 0);

        $totalExperienceYears = $this->calculateTotalExperienceYears($user);
        $scoringMinExperience = $minExperience > 0 ? max($minExperience, 1) : 1;
        $experienceFraction = $totalExperienceYears <= 0 ? 0 : min($totalExperienceYears / $scoringMinExperience, 1);
        $experienceScore = $experienceFraction * ($settings->experience_weight ?? 0);

        $requiredEducationLevel = $requisition->required_education_level ?? null;
        $requiredAreasOfStudy = $requisition->required_areas_of_study ?? [];
        $educationFraction = $this->calculateEducationScore($user, $requiredEducationLevel, $requiredAreasOfStudy) / 100;
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

    /**
     * Calculate skills fraction with more lenient scoring
     * Users can get full marks without matching all skills
     */
    protected function calculateSkillsFraction(float $matchedSkillsCount, int $totalJobSkills): float
    {
        if ($totalJobSkills == 0) {
            return 1.0; // No skills required, full score
        }
        
        if ($matchedSkillsCount == 0) {
            return 0.0; // No skills matched, no score
        }
        
        // Graduated scoring - get higher fractions for partial matches
        // This gives diminishing returns but allows full marks with fewer skills
        
        // For 1-2 skills: need 100% match
        if ($totalJobSkills <= 2) {
            return $matchedSkillsCount / $totalJobSkills;
        }
        
        // For 3-4 skills: can get full marks with 75% match
        if ($totalJobSkills <= 4) {
            $threshold = ceil($totalJobSkills * 0.75);
            if ($matchedSkillsCount >= $threshold) {
                return 1.0;
            }
            return $matchedSkillsCount / $threshold;
        }
        
        // For 5+ skills: can get full marks with 60% match
        $threshold = ceil($totalJobSkills * 0.6);
        if ($matchedSkillsCount >= $threshold) {
            return 1.0;
        }
        
        // Scale score based on how close they are to the threshold
        return $matchedSkillsCount / $threshold;
    }

    protected function countMatchedSkills(array $jobSkills, array $userSkills): float
    {
        $matchedCount = 0;

        foreach ($jobSkills as $jobSkill) {
            if ($this->skillMatches($jobSkill, $userSkills)) {
                $matchedCount++;
            }
        }

        return $matchedCount;
    }

    protected function skillMatches(string $jobSkill, array $userSkills): bool
    {
        $jobSkillNormalized = $this->normalizeSkill($jobSkill);
        
        foreach ($userSkills as $userSkill) {
            $userSkillNormalized = $this->normalizeSkill($userSkill);
            
            // Exact match after normalization
            if ($jobSkillNormalized === $userSkillNormalized) {
                return true;
            }
            
            // Contains match (either direction)
            if (strpos($userSkillNormalized, $jobSkillNormalized) !== false || 
                strpos($jobSkillNormalized, $userSkillNormalized) !== false) {
                return true;
            }
            
            // Check for common skill variations and synonyms
            if ($this->areSkillVariations($jobSkillNormalized, $userSkillNormalized)) {
                return true;
            }

            // Enhanced keyword matching for related terms
            if ($this->hasRelatedKeywords($jobSkillNormalized, $userSkillNormalized)) {
                return true;
            }
        }
        
        return false;
    }

    protected function normalizeSkill(string $skill): string
    {
        // Remove common variations and normalize
        $skill = strtolower(trim($skill));
        
        // Remove common words/suffixes
        $removeWords = ['.js', '.net', ' programming', ' development', ' developer', ' language', ' framework', ' technology', ' skills', ' skill'];
        $skill = str_replace($removeWords, '', $skill);
        
        // Remove special characters and extra spaces
        $skill = preg_replace('/[^\w\s]/', ' ', $skill);
        $skill = preg_replace('/\s+/', ' ', $skill);
        
        return trim($skill);
    }

    protected function areSkillVariations(string $skill1, string $skill2): bool
    {
        // Define common skill variations
        $variations = [
            'javascript' => ['js', 'ecmascript', 'javascript', 'java script'],
            'python' => ['python', 'python3', 'py', 'python programming'],
            'csharp' => ['c#', 'csharp', 'c sharp', 'dotnet', '.net', 'dot net'],
            'nodejs' => ['node.js', 'nodejs', 'node', 'javascript backend', 'node js'],
            'reactjs' => ['react.js', 'reactjs', 'react', 'react framework', 'react js'],
            'vuejs' => ['vue.js', 'vuejs', 'vue', 'vue framework', 'vue js'],
            'angular' => ['angular.js', 'angularjs', 'angular', 'angular framework'],
            'mysql' => ['mysql', 'my sql', 'mysql database'],
            'postgresql' => ['postgresql', 'postgres', 'postgre sql', 'postgres sql'],
            'mongodb' => ['mongodb', 'mongo db', 'mongo', 'nosql'],
            'photoshop' => ['photoshop', 'adobe photoshop', 'ps', 'photo shop'],
            'illustrator' => ['illustrator', 'adobe illustrator', 'ai'],
            'html' => ['html', 'html5', 'hypertext markup language', 'markup'],
            'css' => ['css', 'css3', 'cascading style sheets', 'styling'],
            'php' => ['php', 'php7', 'php8', 'hypertext preprocessor'],
            'java' => ['java', 'java8', 'java11', 'java17', 'jdk'],
            'cplusplus' => ['c++', 'cpp', 'c plus plus', 'cplusplus'],
            'git' => ['git', 'github', 'gitlab', 'version control', 'source control'],
            'docker' => ['docker', 'containerization', 'containers'],
            'kubernetes' => ['kubernetes', 'k8s', 'container orchestration'],
            'aws' => ['aws', 'amazon web services', 'cloud computing'],
            'azure' => ['azure', 'microsoft azure', 'azure cloud'],
            'gcp' => ['gcp', 'google cloud platform', 'google cloud'],
        ];
        
        foreach ($variations as $baseSkill => $variantsList) {
            if ((in_array($skill1, $variantsList) && in_array($skill2, $variantsList))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check for related keywords that might indicate similar skills
     * This handles cases where people describe skills differently
     */
    protected function hasRelatedKeywords(string $jobSkill, string $userSkill): bool
    {
        // Define related keywords and concepts
        $relatedKeywords = [
            'web development' => ['frontend', 'backend', 'fullstack', 'full stack', 'web dev', 'website', 'web app', 'html', 'css', 'javascript'],
            'frontend' => ['ui', 'user interface', 'client side', 'react', 'vue', 'angular', 'html', 'css', 'javascript'],
            'backend' => ['server side', 'api', 'database', 'server', 'node', 'php', 'python', 'java'],
            'database' => ['sql', 'mysql', 'postgresql', 'mongodb', 'data management', 'queries'],
            'mobile development' => ['ios', 'android', 'react native', 'flutter', 'mobile app', 'app development'],
            'ui design' => ['user interface', 'ux', 'user experience', 'design', 'figma', 'sketch', 'photoshop'],
            'ux design' => ['user experience', 'ui', 'user interface', 'design', 'wireframes', 'prototyping'],
            'data analysis' => ['analytics', 'data science', 'excel', 'sql', 'python', 'r', 'statistics'],
            'project management' => ['pmp', 'agile', 'scrum', 'kanban', 'project coordination', 'team lead'],
            'digital marketing' => ['seo', 'sem', 'social media', 'content marketing', 'google ads', 'facebook ads'],
            'graphic design' => ['photoshop', 'illustrator', 'indesign', 'visual design', 'branding', 'logo design'],
            'accounting' => ['bookkeeping', 'financial reporting', 'quickbooks', 'excel', 'financial analysis'],
            'sales' => ['business development', 'lead generation', 'customer relations', 'crm', 'revenue'],
            'customer service' => ['customer support', 'help desk', 'client relations', 'support', 'communication'],
            'writing' => ['content writing', 'copywriting', 'technical writing', 'blog writing', 'content creation'],
            'devops' => ['ci cd', 'deployment', 'automation', 'docker', 'kubernetes', 'aws', 'cloud'],
            'testing' => ['qa', 'quality assurance', 'automation testing', 'manual testing', 'selenium'],
            'machine learning' => ['ai', 'artificial intelligence', 'data science', 'python', 'tensorflow', 'deep learning'],
        ];

        foreach ($relatedKeywords as $concept => $keywords) {
            $jobSkillMatches = $this->containsAnyKeyword($jobSkill, [$concept, ...$keywords]);
            $userSkillMatches = $this->containsAnyKeyword($userSkill, [$concept, ...$keywords]);
            
            if ($jobSkillMatches && $userSkillMatches) {
                return true;
            }
        }

        // Check for partial word matches (useful for compound skills)
        $jobWords = explode(' ', $jobSkill);
        $userWords = explode(' ', $userSkill);
        
        foreach ($jobWords as $jobWord) {
            if (strlen($jobWord) >= 4) { // Only check meaningful words
                foreach ($userWords as $userWord) {
                    if (strlen($userWord) >= 4 && 
                        (strpos($userWord, $jobWord) !== false || strpos($jobWord, $userWord) !== false)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if a skill contains any of the given keywords
     */
    protected function containsAnyKeyword(string $skill, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (strpos($skill, $keyword) !== false) {
                return true;
            }
        }
        return false;
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

    protected function calculateEducationScore($user, $requiredEducationLevel, $requiredAreasOfStudy = []): float 
    {
        if (!$user->education || $user->education->isEmpty()) return 0.0;
        if (!$requiredEducationLevel) return 100.0;
    
        $requiredScore = $this->mapEducationLevelToScore($requiredEducationLevel);
        $bestScore = 0.0;
    
        foreach ($user->education as $education) {
            $educationLevel = $education->education_level ?? null;
            $educationStatus = strtolower($education->status ?? '');
            $fieldOfStudy = $education->field_of_study ?? null;
            $hasEndDate = !empty($education->end_date);
    
            if (!$educationLevel) continue;
    
            $educationLevelScore = $this->mapEducationLevelToScore($educationLevel);
            
            // Three main criteria
            $meetsLevelRequirement = $educationLevelScore >= $requiredScore;
            $meetsFieldRequirement = $this->checkFieldOfStudyMatch($fieldOfStudy, $requiredAreasOfStudy);
            $isComplete = ($educationStatus === 'complete' && $hasEndDate);
            
            $currentScore = 0.0;
            
            if ($meetsLevelRequirement && $meetsFieldRequirement && $isComplete) {
                // Perfect match: 100%
                $currentScore = 100.0;
                
            } elseif ($meetsLevelRequirement && $meetsFieldRequirement) {
                // Right level + right field, but incomplete: 70%
                $currentScore = 70.0;
                
            } elseif ($meetsLevelRequirement && $isComplete) {
                // Right level + complete, but wrong field: 40%
                $currentScore = 40.0;
                
            } elseif ($meetsFieldRequirement && $isComplete) {
                // Right field + complete, but level too low: 30%
                $currentScore = 30.0;
                
            } elseif ($meetsLevelRequirement) {
                // Only right level (wrong field, incomplete): 25%
                $currentScore = 25.0;
                
            } elseif ($meetsFieldRequirement) {
                // Only right field (level too low, incomplete): 15%
                $currentScore = 15.0;
                
            } elseif ($isComplete) {
                // Only complete (wrong level, wrong field): 10%
                $currentScore = 10.0;
                
            } else {
                // None of the criteria met: 0%
                $currentScore = 0.0;
            }
    
            $bestScore = max($bestScore, $currentScore);
        }
    
        return round($bestScore, 2);
    }
    
    protected function checkFieldOfStudyMatch($userFieldOfStudy, $requiredAreasOfStudy): bool
    {
        // If no field requirements, consider it a match
        if (empty($requiredAreasOfStudy)) return true;
        
        // If user has no field of study, no match
        if (empty($userFieldOfStudy)) return false;
    
        $userField = strtolower(trim($userFieldOfStudy));
        
        foreach ($requiredAreasOfStudy as $requiredArea) {
            $requiredField = strtolower(trim($requiredArea));
            
            // Exact match
            if ($userField === $requiredField) return true;
            
            // Check if either contains the other (handles variations)
            if (strpos($userField, $requiredField) !== false || 
                strpos($requiredField, $userField) !== false) {
                return true;
            }
        }
        
        return false;
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