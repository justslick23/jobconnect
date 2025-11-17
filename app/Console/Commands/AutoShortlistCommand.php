<?php

namespace App\Console\Commands;

use App\Mail\ApplicationNotShortlistedMail;
use App\Models\JobRequisition;
use App\Models\ShortlistingSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class AutoShortlistCommand extends Command
{
    protected $signature = 'jobs:auto-shortlist {--threshold=} {--requisition-id=} {--force}';
    protected $description = 'AI-powered auto-shortlisting using Gemini for intelligent candidate evaluation';

    protected $geminiApiKey;
    protected $geminiModel = 'gemini-2.0-flash';

    public function __construct()
    {
        parent::__construct();
        $this->geminiApiKey = env('GEMINI_API_KEY');
    }

    public function handle()
    {
        Log::info('=== AUTO-SHORTLISTING COMMAND STARTED ===');
        Log::info('Command options:', [
            'threshold' => $this->option('threshold'),
            'requisition-id' => $this->option('requisition-id'),
            'force' => $this->option('force'),
        ]);

        if (!$this->geminiApiKey) {
            Log::error('Gemini API key not configured');
            $this->error("Gemini API key not configured. Please set GEMINI_API_KEY in your .env file.");
            return Command::FAILURE;
        }

        Log::info('Gemini API key found, model: ' . $this->geminiModel);

        $requisitionId = $this->option('requisition-id');
        $force = $this->option('force');

        Log::info('Fetching shortlisting settings...');
        $settings = ShortlistingSetting::first();
        if (!$settings) {
            Log::error('Shortlisting settings not found in database');
            $this->error("Shortlisting settings not found. Please configure them first.");
            return Command::FAILURE;
        }

        Log::info('Shortlisting settings loaded:', [
            'skills_weight' => $settings->skills_weight,
            'experience_weight' => $settings->experience_weight,
            'education_weight' => $settings->education_weight,
            'qualification_bonus' => $settings->qualification_bonus,
            'threshold' => $settings->threshold,
        ]);

        $threshold = $this->option('threshold') 
                     ? (float) $this->option('threshold') 
                     : ($settings->threshold ?? 70);

        Log::info('Using threshold: ' . $threshold . '%');

        $query = JobRequisition::query();

        if ($requisitionId) {
            Log::info('Processing specific requisition ID: ' . $requisitionId);
            $query->where('id', $requisitionId);
            if (!$force) {
                $query->where('auto_shortlisting_completed', false);
            }
            $query->where('job_status', 'closed');
        } else {
            Log::info('Processing all closed requisitions that need auto-shortlisting');
            $query->where('job_status', 'closed')
                ->where('auto_shortlisting_completed', false);
        }

        $requisitions = $query->get();
        Log::info('Found ' . $requisitions->count() . ' requisition(s) to process');

        if ($requisitions->isEmpty()) {
            $msg = $requisitionId 
                ? "No job requisition found with ID #{$requisitionId} that needs auto-shortlisting."
                : 'No job requisitions found that need auto-shortlisting.';
            Log::info($msg);
            $this->info($msg);
            return Command::SUCCESS;
        }

        $this->info("Starting AI-powered auto-shortlisting for {$requisitions->count()} job requisition(s) with threshold {$threshold}%...");

        $successCount = 0;
        $failureCount = 0;

        foreach ($requisitions as $requisition) {
            Log::info("--- Processing Job Requisition #{$requisition->id}: {$requisition->title} ---");
            
            try {
                if (!$force && $requisition->auto_shortlisting_completed) {
                    Log::warning("Job Requisition #{$requisition->id} already processed. Skipping...");
                    $this->warn("⚠️ Job Requisition #{$requisition->id} already processed. Skipping...");
                    continue;
                }

                if ($this->processRequisition($requisition, $threshold, $settings, $force)) {
                    $successCount++;
                    Log::info("Job Requisition #{$requisition->id} processed successfully");
                } else {
                    $failureCount++;
                    Log::error("Job Requisition #{$requisition->id} processing failed");
                }
            } catch (\Exception $e) {
                $this->error("❌ Job Requisition #{$requisition->id} failed: {$e->getMessage()}");
                Log::error("Auto-shortlisting exception for Job Requisition #{$requisition->id}", [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $failureCount++;
            }
        }

        Log::info('=== AUTO-SHORTLISTING COMMAND COMPLETED ===', [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        $this->info("🎉 Auto-shortlisting completed! Success: {$successCount}, Failures: {$failureCount}");
        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function processRequisition(JobRequisition $requisition, float $threshold, ShortlistingSetting $settings, bool $force = false): bool
    {
        Log::info("Processing requisition #{$requisition->id}", [
            'title' => $requisition->title,
            'force' => $force,
            'already_completed' => $requisition->auto_shortlisting_completed,
        ]);

        if (!$force && $requisition->auto_shortlisting_completed) {
            Log::warning("Requisition #{$requisition->id} already completed, skipping");
            $this->warn("⚠️ Job Requisition #{$requisition->id} already processed.");
            return true;
        }

        if ($force && $requisition->auto_shortlisting_completed) {
            Log::info("Force flag enabled - re-running shortlisting for requisition #{$requisition->id}");
            $this->info("🔄 Job Requisition #{$requisition->id}: Force re-running shortlisting...");
        }

        Log::info("Loading applications for requisition #{$requisition->id}");
        $applications = $requisition->applications()
            ->with(['user.skills', 'user.experiences', 'user.education', 'user.qualifications'])
            ->get();

        Log::info("Loaded {$applications->count()} applications for requisition #{$requisition->id}");

        if ($applications->isEmpty()) {
            Log::warning("No applications found for requisition #{$requisition->id}");
            $this->warn("⚠️ Job Requisition #{$requisition->id} has no applications.");
            
            $requisition->update([
                'auto_shortlisting_completed' => true,
                'auto_shortlisting_completed_at' => now()
            ]);
            
            Log::info("Marked requisition #{$requisition->id} as completed with no applications");
            return true;
        }

        $this->info("🤖 Using Gemini AI to evaluate {$applications->count()} applications...");
        Log::info("Starting Gemini AI evaluation for {$applications->count()} applications");

        $shortlisted = collect();
        $batchSize = 5;
        $applicationBatches = $applications->chunk($batchSize);

        Log::info("Processing applications in {$applicationBatches->count()} batches of {$batchSize}");

        foreach ($applicationBatches as $batchIndex => $batch) {
            $this->info("Processing batch " . ($batchIndex + 1) . "/" . $applicationBatches->count());
            Log::info("=== Batch " . ($batchIndex + 1) . "/" . $applicationBatches->count() . " ===");
            
            foreach ($batch as $application) {
                $userName = $application->user ? $application->user->name : 'Unknown';
                $userEmail = $application->user ? $application->user->email : 'No email';
                
                Log::info("Evaluating application #{$application->id}", [
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'current_status' => $application->status,
                ]);
                
                try {
                    $evaluation = $this->evaluateApplicationWithGemini($application, $requisition, $settings);
                    
                    if ($evaluation) {
                        Log::info("AI evaluation successful for application #{$application->id}", [
                            'scores' => $evaluation,
                        ]);
                        
                        // Save scores
                        $application->score()->updateOrCreate([], [
                            'skills_score' => $evaluation['skills_score'],
                            'experience_score' => $evaluation['experience_score'],
                            'education_score' => $evaluation['education_score'],
                            'qualification_bonus' => $evaluation['qualification_bonus'],
                            'total_score' => $evaluation['total_score'],
                            'reasoning' => $evaluation['reasoning'], // ADD THIS LINE

                        ]);

                        Log::info("Scores saved to database for application #{$application->id}");

                        // Update application status
                         // Update application status
    $oldStatus = $application->status;
    if ($evaluation['total_score'] >= $threshold) {
        $application->status = 'shortlisted';
        $shortlisted->push($application);
        Log::info("Application #{$application->id} SHORTLISTED (score: {$evaluation['total_score']}% >= threshold: {$threshold}%)");
    } else {
        Log::info("Application #{$application->id} NOT shortlisted (score: {$evaluation['total_score']}% < threshold: {$threshold}%)");
    }

    $application->saveQuietly();
    Log::info("Application status updated: {$oldStatus} -> {$application->status}");
                        // Truncate reasoning for display
                        $displayReasoning = strlen($evaluation['reasoning']) > 80 
                            ? substr($evaluation['reasoning'], 0, 77) . '...' 
                            : $evaluation['reasoning'];
                            
                        $this->line("  ✓ {$userName}: {$evaluation['total_score']}% - {$displayReasoning}");
                    } else {
                        Log::error("AI evaluation returned null for application #{$application->id}");
                        $this->warn("  ⚠️ Failed to evaluate application for {$userName}");
                    }
                    
                    // Small delay to respect API rate limits
                    Log::debug("Sleeping 0.5s before next API call");
                    usleep(500000);
                    
                } catch (\Exception $e) {
                    $this->error("  ❌ Error evaluating {$userName}: {$e->getMessage()}");
                    Log::error("Exception during evaluation of application #{$application->id}", [
                        'user_name' => $userName,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            }
        }

        Log::info("All applications processed. Shortlisted: {$shortlisted->count()}/{$applications->count()}");

        $notShortlistedCount = 0;
        if (!$force || !$requisition->auto_shortlisting_completed) {
            Log::info("Processing non-shortlisted applications for requisition #{$requisition->id}");
            $notShortlistedCount = $this->processNonShortlistedApplications($requisition);
            Log::info("{$notShortlistedCount} applications marked as rejected");
        } else {
            Log::info("Skipping non-shortlisted processing (force re-run)");
        }

        $requisition->update([
            'auto_shortlisting_completed' => true,
            'auto_shortlisting_completed_at' => now()
        ]);

        Log::info("Requisition #{$requisition->id} marked as completed", [
            'completed_at' => now()->toDateTimeString(),
        ]);

        $this->info("✅ Job Requisition #{$requisition->id}: {$shortlisted->count()}/{$applications->count()} shortlisted, {$notShortlistedCount} rejected.");

        return true;
    }

    protected function evaluateApplicationWithGemini($application, JobRequisition $requisition, ShortlistingSetting $settings): ?array
    {
        $user = $application->user;
        if (!$user) {
            Log::warning("Application {$application->id} has no associated user");
            return null;
        }

        Log::info("Building evaluation prompt for application #{$application->id}");
        $prompt = $this->buildEvaluationPrompt($user, $requisition, $settings);
        
        Log::debug("Prompt generated", [
            'application_id' => $application->id,
            'prompt_length' => strlen($prompt),
            'prompt_preview' => substr($prompt, 0, 200) . '...',
        ]);

        try {
            Log::info("Sending request to Gemini API", [
                'model' => $this->geminiModel,
                'application_id' => $application->id,
                'user' => $user->name,
            ]);

            $startTime = microtime(true);
            
            $response = Http::timeout(45)
                ->retry(2, 1000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_NONE'
                        ]
                    ]
                ]);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("Gemini API response received", [
                'application_id' => $application->id,
                'status' => $response->status(),
                'response_time_ms' => $responseTime,
            ]);

            if (!$response->successful()) {
                $errorBody = $response->body();
                Log::error("Gemini API error for application {$application->id}", [
                    'status' => $response->status(),
                    'error_body' => $errorBody,
                    'headers' => $response->headers(),
                ]);
                return null;
            }

            $result = $response->json();
            
            Log::debug("Gemini API full response", [
                'application_id' => $application->id,
                'response' => $result,
            ]);
            
            // Check for blocked content
            if (isset($result['candidates'][0]['finishReason']) && 
                $result['candidates'][0]['finishReason'] === 'SAFETY') {
                Log::warning("Gemini blocked content for application {$application->id} due to safety filters", [
                    'finish_reason' => $result['candidates'][0]['finishReason'],
                    'safety_ratings' => $result['candidates'][0]['safetyRatings'] ?? null,
                ]);
                return null;
            }
            
            if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error("Unexpected Gemini response structure for application {$application->id}", [
                    'response_structure' => json_encode($result),
                ]);
                return null;
            }

            $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'];
            
            Log::info("AI response text extracted", [
                'application_id' => $application->id,
                'response_length' => strlen($aiResponse),
                'response_preview' => substr($aiResponse, 0, 300),
            ]);

            Log::info("Full AI Response for application #{$application->id}:", [
                'response' => $aiResponse,
            ]);
            
            $parsed = $this->parseGeminiResponse($aiResponse, $settings, $application->id);
            
            if ($parsed) {
                Log::info("Successfully parsed AI response for application #{$application->id}", [
                    'parsed_scores' => $parsed,
                ]);
            }
            
            return $parsed;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Gemini API connection failed for application {$application->id}", [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return null;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("Gemini API request failed for application {$application->id}", [
                'message' => $e->getMessage(),
                'response' => $e->response ? $e->response->body() : 'No response',
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error("Unexpected error calling Gemini for application {$application->id}", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    protected function buildEvaluationPrompt($user, JobRequisition $requisition, ShortlistingSetting $settings): string
    {
        // Prepare job requirements
        $jobSkills = $requisition->skills ? $requisition->skills->pluck('name')->toArray() : [];
        $minExperience = $requisition->min_experience ?? 0;
        $requiredEducation = $requisition->required_education_level ?? 'Not specified';
        $requiredAreasOfStudy = is_array($requisition->required_areas_of_study) 
            ? $requisition->required_areas_of_study 
            : (json_decode($requisition->required_areas_of_study ?? '[]', true) ?: []);
    
        // Prepare candidate profile
        $userSkills = $user->skills ? $user->skills->pluck('name')->toArray() : [];
        
        $experiences = [];
        if ($user->experiences && $user->experiences->isNotEmpty()) {
            foreach ($user->experiences as $exp) {
                $experiences[] = [
                    'title' => $exp->title ?? '',
                    'company' => $exp->company ?? '',
                    'duration' => ($exp->start_date ?? '') . ' to ' . ($exp->end_date ?? 'Present'),
                    'description' => $exp->description ?? ''
                ];
            }
        }
    
        $education = [];
        if ($user->education && $user->education->isNotEmpty()) {
            foreach ($user->education as $edu) {
                $education[] = [
                    'level' => $edu->education_level ?? '',
                    'field' => $edu->field_of_study ?? '',
                    'institution' => $edu->institution ?? '',
                    'status' => $edu->status ?? '',
                    'end_date' => $edu->end_date ?? ''
                ];
            }
        }
    
        $qualifications = [];
        if ($user->qualifications && $user->qualifications->isNotEmpty()) {
            foreach ($user->qualifications as $qual) {
                $qualifications[] = [
                    'name' => $qual->name ?? '',
                    'issuer' => $qual->issuing_organization ?? '',
                    'date' => $qual->issue_date ?? ''
                ];
            }
        }
    
        // Format data for prompt
        $jobSkillsFormatted = $this->formatArray($jobSkills);
        $requiredAreasFormatted = $this->formatArray($requiredAreasOfStudy);
        $userSkillsFormatted = $this->formatArray($userSkills);
        $experiencesFormatted = $this->formatExperiences($experiences);
        $educationFormatted = $this->formatEducation($education);
        $qualificationsFormatted = $this->formatQualifications($qualifications);
        
        // Clean and prepare text fields
        $jobDescription = strip_tags($requisition->description ?? 'Not provided');
        $jobDescription = trim(preg_replace('/\s+/', ' ', $jobDescription)); // Remove extra whitespace
        
        $jobRequirements = $requisition->requirements ?? 'Not specified';
        $jobRequirements = strip_tags($jobRequirements);
        $jobRequirements = trim(preg_replace('/\s+/', ' ', $jobRequirements));
    
        $prompt = "You are an expert HR recruiter tasked with evaluating a job application. You MUST carefully analyze the candidate's profile against ALL the job requirements listed below and provide detailed, accurate scoring.\n\n";
        
        $prompt .= "=== JOB REQUIREMENTS ===\n\n";
        $prompt .= "Position: {$requisition->title}\n\n";
        
        $prompt .= "Required Skills: {$jobSkillsFormatted}\n";
        $prompt .= "IMPORTANT: Match candidate's skills against these required skills. Consider exact matches, related skills, and transferable skills.\n\n";
        
        $prompt .= "Minimum Experience Required: {$minExperience} years\n";
        $prompt .= "IMPORTANT: Evaluate if candidate meets or exceeds this experience requirement. Also evaluate their roles within that experience.\n\n\n";
        
        $prompt .= "Required Education Level: {$requiredEducation}\n";
        $prompt .= "Required Areas/Fields of Study: {$requiredAreasFormatted}\n";
        $prompt .= "IMPORTANT: Check if candidate's education level meets requirements and if their field of study matches the required areas.\n\n";
        
        $prompt .= "Job Description:\n{$jobDescription}\n\n";
        
        $prompt .= "Additional Job Requirements:\n{$jobRequirements}\n";
        $prompt .= "IMPORTANT: Consider how well the candidate meets these additional requirements.\n\n";
    
        $prompt .= "=== CANDIDATE PROFILE ===\n\n";
        $prompt .= "Name: {$user->name}\n\n";
        
        $prompt .= "Candidate's Skills: {$userSkillsFormatted}\n\n";
        
        $prompt .= "Work Experience:\n{$experiencesFormatted}\n\n";
        
        $prompt .= "Education:\n{$educationFormatted}\n\n";
        
        $prompt .= "Additional Qualifications/Certifications:\n{$qualificationsFormatted}\n\n";
        
        $prompt .= "=== SCORING CRITERIA ===\n\n";
        
        $prompt .= "You must score the candidate on four criteria. Each score should be between 0-100.\n\n";
        
        $prompt .= "1. SKILLS MATCH (Weight: {$settings->skills_weight}%)\n";
        $prompt .= "   - Compare the candidate's skills with the required skills listed above\n";
        $prompt .= "   - Give higher scores for direct skill matches\n";
        $prompt .= "   - Give moderate scores for related/transferable skills\n";
        $prompt .= "   - Consider the depth and breadth of their skill set\n";
        $prompt .= "   - Reference the job description and requirements\n\n";
        
        $prompt .= "2. EXPERIENCE MATCH (Weight: {$settings->experience_weight}%)\n";
        $prompt .= "   - Evaluate total years of relevant work experience vs. minimum requirement ({$minExperience} years)\n";
        $prompt .= "   - Assess the quality and relevance of their past roles to this position\n";
        $prompt .= "   - Consider career progression and growth\n";
        $prompt .= "   - Evaluate industry experience relevance\n";
        $prompt .= "   - Reference the job description to determine relevance\n\n";
        
        $prompt .= "3. EDUCATION MATCH (Weight: {$settings->education_weight}%)\n";
        $prompt .= "   - Compare education level: Required is '{$requiredEducation}'\n";
        $prompt .= "   - Compare field of study with required areas: {$requiredAreasFormatted}\n";
        $prompt .= "   - Consider completion status (completed vs. in progress)\n";
        $prompt .= "   - Evaluate how well their educational background prepares them for this role\n\n";
        
        $prompt .= "4. ADDITIONAL QUALIFICATIONS BONUS (Bonus: {$settings->qualification_bonus}%)\n";
        $prompt .= "   - Evaluate professional certifications relevant to the role\n";
        $prompt .= "   - Consider licenses, awards, or recognitions\n";
        $prompt .= "   - Assess specialized training that adds value\n";
        $prompt .= "   - This is a BONUS score that can boost the overall rating\n\n";
        
        $prompt .= "=== EVALUATION INSTRUCTIONS ===\n\n";
        $prompt .= "1. READ ALL JOB REQUIREMENTS CAREFULLY including skills, experience, education level, areas of study, description, and additional requirements\n";
        $prompt .= "2. READ THE COMPLETE CANDIDATE PROFILE including all skills, experiences, education, and qualifications\n";
        $prompt .= "3. For EACH scoring criterion, explicitly consider how the candidate matches the specific requirements\n";
        $prompt .= "4. Be thorough but fair - recognize transferable skills and relevant experience even if not exact matches\n";
        $prompt .= "5. Your reasoning should reference specific requirements and how the candidate meets or doesn't meet them\n";
        $prompt .= "6. Provide scores between 0-100 for each criterion based on your analysis\n\n";
        
        $prompt .= "=== REQUIRED RESPONSE FORMAT ===\n\n";
        $prompt .= "You MUST respond in EXACTLY this format (no markdown, no code blocks, just plain text):\n\n";
        $prompt .= "SKILLS_SCORE: [number between 0-100]\n";
        $prompt .= "EXPERIENCE_SCORE: [number between 0-100]\n";
        $prompt .= "EDUCATION_SCORE: [number between 0-100]\n";
        $prompt .= "QUALIFICATION_BONUS: [number between 0-100]\n";
        $prompt .= "TOTAL_SCORE: [calculated weighted total, 0-100]\n";
        $prompt .= "REASONING: [2-3 sentences explaining your evaluation, referencing specific requirements and how candidate meets/doesn't meet them]\n\n";
        
        $prompt .= "Now evaluate this candidate against ALL the requirements listed above.";
    
        return $prompt;
    }

    protected function parseGeminiResponse(string $response, ShortlistingSetting $settings, int $applicationId = null): ?array
{
    Log::info("Parsing Gemini response for application #{$applicationId}");
    
    try {
        // Clean the response - remove markdown code blocks if present
        $response = preg_replace('/```[a-z]*\n?/i', '', $response);
        $response = trim($response);
        
        Log::debug("Cleaned response", [
            'application_id' => $applicationId,
            'cleaned_response' => $response,
        ]);
        
        // Extract scores using regex with more flexible patterns
        preg_match('/SKILLS[_\s]SCORE:?\s*(\d+(?:\.\d+)?)/i', $response, $skillsMatch);
        preg_match('/EXPERIENCE[_\s]SCORE:?\s*(\d+(?:\.\d+)?)/i', $response, $experienceMatch);
        preg_match('/EDUCATION[_\s]SCORE:?\s*(\d+(?:\.\d+)?)/i', $response, $educationMatch);
        preg_match('/QUALIFICATION[_\s]BONUS:?\s*(\d+(?:\.\d+)?)/i', $response, $qualificationMatch);
        preg_match('/TOTAL[_\s]SCORE:?\s*(\d+(?:\.\d+)?)/i', $response, $totalMatch);
        preg_match('/REASONING:?\s*(.+?)(?:\n\n|$)/is', $response, $reasoningMatch);

        Log::debug("Regex matches", [
            'application_id' => $applicationId,
            'skills_match' => $skillsMatch ?? 'none',
            'experience_match' => $experienceMatch ?? 'none',
            'education_match' => $educationMatch ?? 'none',
            'qualification_match' => $qualificationMatch ?? 'none',
            'reasoning_match' => isset($reasoningMatch[1]) ? substr($reasoningMatch[1], 0, 100) : 'none',
        ]);

        if (!$skillsMatch || !$experienceMatch || !$educationMatch) {
            Log::error("Could not parse required scores from Gemini response", [
                'application_id' => $applicationId,
                'response_preview' => substr($response, 0, 500),
                'missing_skills' => !$skillsMatch,
                'missing_experience' => !$experienceMatch,
                'missing_education' => !$educationMatch,
            ]);
            return null;
        }

        // Get raw scores from Gemini (0-100 scale)
        $skillsRaw = min((float) $skillsMatch[1], 100);
        $experienceRaw = min((float) $experienceMatch[1], 100);
        $educationRaw = min((float) $educationMatch[1], 100);
        $qualificationRaw = isset($qualificationMatch[1]) ? min((float) $qualificationMatch[1], 100) : 0;

        Log::info("Raw scores from AI", [
            'application_id' => $applicationId,
            'skills_raw' => $skillsRaw,
            'experience_raw' => $experienceRaw,
            'education_raw' => $educationRaw,
            'qualification_raw' => $qualificationRaw,
        ]);

        // Validate settings weights
        $totalWeight = $settings->skills_weight + $settings->experience_weight + 
                      $settings->education_weight;
        
        if ($totalWeight <= 0) {
            Log::error("Invalid settings weights - total weight is 0", [
                'application_id' => $applicationId,
                'settings' => [
                    'skills_weight' => $settings->skills_weight,
                    'experience_weight' => $settings->experience_weight,
                    'education_weight' => $settings->education_weight,
                    'qualification_bonus' => $settings->qualification_bonus,
                ],
            ]);
            return null;
        }

        // CORRECTED CALCULATION:
        // Convert raw scores (0-100) to proportional contributions based on weights
        // Each weighted score represents the percentage contribution to the total
        
        // Calculate weighted contributions (these are on a 0-weight scale)
        $skillsContribution = ($skillsRaw / 100) * $settings->skills_weight;
        $experienceContribution = ($experienceRaw / 100) * $settings->experience_weight;
        $educationContribution = ($educationRaw / 100) * $settings->education_weight;
        
        // Calculate base score (0-100 scale)
        $baseScore = (($skillsContribution + $experienceContribution + $educationContribution) / $totalWeight) * 100;
        
        // Calculate qualification bonus (this is additive, not weighted)
        $qualificationBonus = ($qualificationRaw / 100) * $settings->qualification_bonus;
        
        // Total score = base score + qualification bonus (capped at 100)
        $totalScore = min($baseScore + $qualificationBonus, 100);

        Log::info("Score calculation breakdown", [
            'application_id' => $applicationId,
            'skills_contribution' => round($skillsContribution, 2),
            'experience_contribution' => round($experienceContribution, 2),
            'education_contribution' => round($educationContribution, 2),
            'total_weight' => $totalWeight,
            'base_score' => round($baseScore, 2),
            'qualification_bonus' => round($qualificationBonus, 2),
            'total_score' => round($totalScore, 2),
        ]);

        $reasoning = isset($reasoningMatch[1]) ? trim($reasoningMatch[1]) : 'AI evaluation completed';
        
        // Remove any remaining markdown or special characters from reasoning
        $reasoning = strip_tags($reasoning);
        $reasoning = preg_replace('/\*\*/', '', $reasoning);

        // Store the actual weighted scores for database
        $result = [
            'skills_score' => round($skillsContribution, 2),
            'experience_score' => round($experienceContribution, 2),
            'education_score' => round($educationContribution, 2),
            'qualification_bonus' => round($qualificationBonus, 2),
            'total_score' => round($totalScore, 2),
            'reasoning' => substr($reasoning, 0, 500),
        ];

        Log::info("Final parsed result", [
            'application_id' => $applicationId,
            'result' => $result,
        ]);

        return $result;

    } catch (\Exception $e) {
        Log::error("Error parsing Gemini response", [
            'application_id' => $applicationId,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'response_preview' => substr($response, 0, 500),
        ]);
        return null;
    }
}

    protected function formatArray(array $items): string
    {
        return empty($items) ? 'None specified' : implode(', ', $items);
    }

    protected function formatExperiences(array $experiences): string
    {
        if (empty($experiences)) return 'No experience listed';
        
        $formatted = [];
        foreach ($experiences as $exp) {
            $formatted[] = "{$exp['title']} at {$exp['company']} ({$exp['duration']})";
        }
        return implode(' | ', $formatted);
    }

    protected function formatEducation(array $education): string
    {
        if (empty($education)) return 'No education listed';
        
        $formatted = [];
        foreach ($education as $edu) {
            $status = !empty($edu['status']) ? " ({$edu['status']})" : '';
            $formatted[] = "{$edu['level']} in {$edu['field']} from {$edu['institution']}{$status}";
        }
        return implode(' | ', $formatted);
    }

    protected function formatQualifications(array $qualifications): string
    {
        if (empty($qualifications)) return 'No additional qualifications';
        
        $formatted = [];
        foreach ($qualifications as $qual) {
            $formatted[] = "{$qual['name']} from {$qual['issuer']}";
        }
        return implode(' | ', $formatted);
    }

    protected function processNonShortlistedApplications(JobRequisition $requisition): int
    {
        Log::info("Processing non-shortlisted applications for requisition #{$requisition->id}");
        
        $notShortlisted = $requisition->applications()
            ->whereNotIn('status', ['shortlisted', 'hired', 'offer sent'])
            ->with('user')
            ->get();

        Log::info("Found {$notShortlisted->count()} non-shortlisted applications");

        $processedCount = 0;

        /* Uncomment this block to enable rejection emails
        foreach ($notShortlisted as $application) {
            try {
                $oldStatus = $application->status;
                $application->status = 'rejected';
                $application->saveQuietly();

                Log::info("Application #{$application->id} status changed", [
                    'from' => $oldStatus,
                    'to' => 'rejected',
                    'user' => $application->user ? $application->user->name : 'Unknown',
                ]);

                if ($application->user && $application->user->email) {
                    Log::info("Sending rejection email", [
                        'application_id' => $application->id,
                        'email' => $application->user->email,
                        'user' => $application->user->name,
                    ]);
                    
                    Mail::to($application->user->email)->send(
                        new ApplicationNotShortlistedMail($application->user->name, $requisition->title)
                    );
                    
                    Log::info("Rejection email sent successfully", [
                        'application_id' => $application->id,
                        'email' => $application->user->email,
                    ]);
                    
                    $processedCount++;
                } else {
                    Log::warning("Cannot send email - user or email missing", [
                        'application_id' => $application->id,
                        'has_user' => $application->user !== null,
                        'has_email' => $application->user ? ($application->user->email !== null) : false,
                    ]);
                }
            } catch (\Exception $mailException) {
                $this->warn("⚠️ Failed to email {$application->user->email}: {$mailException->getMessage()}");
                Log::error("Email failure for application", [
                    'application_id' => $application->id,
                    'email' => $application->user ? $application->user->email : 'No email',
                    'message' => $mailException->getMessage(),
                    'file' => $mailException->getFile(),
                    'line' => $mailException->getLine(),
                ]);
            }
        }
        */

        Log::info("Completed processing non-shortlisted applications", [
            'requisition_id' => $requisition->id,
            'processed_count' => $processedCount,
            'total_not_shortlisted' => $notShortlisted->count(),
        ]);

        return $processedCount;
    }
} 