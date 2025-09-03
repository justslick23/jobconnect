<?php

namespace App\Console\Commands;

use App\ShortlistingReportExport;
use App\Mail\ApplicationNotShortlistedMail;
use App\Models\JobRequisition;
use App\Models\JobApplication;
use App\Models\ShortlistingSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Storage;

class AutoShortlistCommand extends Command
{
    protected $signature = 'jobs:auto-shortlist {--threshold=} {--requisition-id=} {--force} {--generate-report}';
    protected $description = 'Run auto-shortlisting for job requisitions, update statuses and notify non-shortlisted applicants';

    protected $reportData = [];

    public function handle()
    {
        $requisitionId = $this->option('requisition-id');
        $force = $this->option('force');
        $generateReport = $this->option('generate-report');

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
                    
                    // Generate and email export for each processed requisition
                    $this->generateAndEmailExport($requisition);
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

        // Generate report if requested
        if ($generateReport && !empty($this->reportData)) {
            $this->generateShortlistingReport();
        }

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

            $underReview = collect();
            $rejected = collect();
            $jobSkills = $requisition->skills ? $requisition->skills->pluck('name')->toArray() : [];
            $totalJobSkills = count($jobSkills);
            $minExperience = (float) ($requisition->min_experience ?? 0);

            // Initialize report data for this requisition
            $this->reportData[$requisition->id] = [
                'job_title' => $requisition->title ?? 'N/A',
                'job_reference' => $requisition->job_reference ?? 'N/A',
                'required_skills' => $jobSkills,
                'min_experience' => $minExperience,
                'required_education' => $requisition->required_education_level ?? 'N/A',
                'required_areas_of_study' => $requisition->required_areas_of_study ?? [],
                'threshold' => $threshold,
                'settings' => [
                    'skills_weight' => $settings->skills_weight,
                    'experience_weight' => $settings->experience_weight,
                    'education_weight' => $settings->education_weight,
                    'qualification_bonus' => $settings->qualification_bonus ?? 0,
                ],
                'applications' => []
            ];

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

                // Updated status logic: place all under review unless score is less than 50/60%
                $rejectThreshold = min(50, $threshold * 0.6); // Use 50% or 60% of threshold, whichever is lower

                if ($scores['total_score'] < $rejectThreshold) {
                    $newStatus = 'rejected';
                } else {
                    $newStatus = 'Review'; // Changed from 'shortlisted' to 'review'
                }

                $oldStatus = $application->status;
                
                // Update application status
                $application->status = $newStatus;
                $application->saveQuietly();

                // Collect detailed information for report
                $this->collectReportData($requisition->id, $application, $user, $scores, $jobSkills, $minExperience, $requisition, $newStatus, $oldStatus);

                // Collect for notifications and logging
                if ($newStatus === 'Review') {
                    $underReview->push($application);
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

            $this->info("✅ Job Requisition #{$requisition->id}: {$underReview->count()}/{$applications->count()} under review, {$rejected->count()} rejected.");

            // Send notifications to rejected applicants (if mail class exists and is configured)
            /* if (class_exists(ApplicationNotShortlistedMail::class) && $rejected->isNotEmpty()) {
                $this->sendRejectionNotifications($rejected, $requisition);
            } */

            return true;
        });
    }

    protected function generateAndEmailExport(JobRequisition $jobRequisition): void
    {
        try {
            $this->info("📊 Generating export for Job Requisition #{$jobRequisition->id}...");
    
            $applications = JobApplication::where('job_requisition_id', $jobRequisition->id)
                ->with([
                    'user.profile',
                    'user.education',
                    'user.qualifications',
                    'user.skills',
                    'user.experiences',
                    'score',
                    'interviews'
                ])
                ->get()
                ->sortByDesc(fn($a) => $a->score->total_score ?? 0);
    
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
    
            // Document properties
            $spreadsheet->getProperties()
                ->setCreator('HR Management System')
                ->setTitle("Applications for {$jobRequisition->title}")
                ->setDescription("Job applications export for {$jobRequisition->title}");
    
            // Job info header
            $sheet->setCellValue('A1', 'Job Title:')->setCellValue('B1', $jobRequisition->title);
            $sheet->setCellValue('A2', 'Department:')->setCellValue('B2', $jobRequisition->department->name ?? 'General');
            $sheet->setCellValue('A3', 'Posted Date:')->setCellValue('B3', $jobRequisition->created_at->format('M j, Y'));
            $sheet->setCellValue('A4', 'Total Applications:')->setCellValue('B4', $applications->count());
            $sheet->setCellValue('A5', 'Export Date:')->setCellValue('B5', now()->format('M j, Y g:i A'));
    
            $sheet->getStyle('A1:A5')->getFont()->setBold(true);
            $sheet->getStyle('A1:B5')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E3F2FD');
    
            // Table headers
            $headerRow = 7;
            $headers = [
                'A' => 'Applicant Name',
                'B' => 'Email',
                'C' => 'Phone',
                'D' => 'Status',
                'E' => 'Application Date',
                'F' => 'Application Score',
                'G' => 'Experience (Years)',
                'H' => 'Interview Score',
                'I' => 'Education Entries',
                'J' => 'Education Status',
                'K' => 'Qualification Entries',
                'L' => 'Skills'
            ];
    
            foreach ($headers as $column => $header) {
                $sheet->setCellValue($column . $headerRow, $header);
            }
    
            $headerRange = 'A' . $headerRow . ':L' . $headerRow;
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2196F3');
            $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
            // Populate applications
            $row = $headerRow + 1;
    
            foreach ($applications as $application) {
                $user = $application->user;
                $profile = $user->profile ?? null;
    
                // Skills
                $skills = $user->skills ?? collect();
                $skillsString = $skills->isEmpty() ? 'N/A' : $skills->pluck('name')->implode(', ');
    
                // Application score
                $appScore = $application->score ? $application->score->total_score : null;
                $appScoreString = $appScore !== null ? number_format($appScore, 2) . '/100' : 'Not Scored';
    
                // Experience
                $totalYears = $this->calculateTotalExperienceYears($user) ?: 'N/A';
    
                // Interview
                $interviewScore = $application->interviews && method_exists($application->interviews, 'averageScore') && $application->interviews->averageScore() !== null
                    ? $application->interviews->averageScore() . '/5'
                    : 'Not Conducted';
    
                // Combine education entries with numbering and status
                $educationList = $user->education->sortByDesc(fn($e) => $e->graduation_year ?? 0)
                    ->map(function($e, $i) {
                        $level = $e->education_level ?? $e->degree ?? 'N/A';
                        $field = $e->field_of_study ?? 'N/A';
                        $institution = $e->institution ?? 'N/A';
                        return ($i + 1) . ". {$level} in {$field} ({$institution})";
                    })->toArray();
    
                $educationStatusList = $user->education->sortByDesc(fn($e) => $e->graduation_year ?? 0)
                    ->map(function($e, $i) {
                        return ($i + 1) . ". " . ($e->status ?? 'N/A'); // Status column
                    })->toArray();
    
                $educationString = $educationList ? implode("\n", $educationList) : 'N/A';
                $educationStatusString = $educationStatusList ? implode("\n", $educationStatusList) : 'N/A';
    
                // Combine qualification entries with numbering
                $qualificationList = $user->qualifications->sortByDesc(fn($q) => $q->obtained_year ?? 0)
                    ->map(function($q, $i) {
                        $title = $q->title ?? $q->name ?? 'N/A';
                        $institution = $q->institution ?? 'N/A';
                        return ($i + 1) . ". {$title} ({$institution})";
                    })->toArray();
    
                $qualificationString = $qualificationList ? implode("\n", $qualificationList) : 'N/A';
    
                // Fill row
                $sheet->setCellValue('A' . $row, $user->name ?? ($user->first_name . ' ' . $user->last_name) ?? 'N/A');
                $sheet->setCellValue('B' . $row, $user->email ?? 'N/A');
                $sheet->setCellValue('C' . $row, $profile->phone ?? 'N/A');
                $sheet->setCellValue('D' . $row, ucfirst($application->status));
                $sheet->setCellValue('E' . $row, $application->created_at->format('M j, Y'));
                $sheet->setCellValue('F' . $row, $appScoreString);
                $sheet->setCellValue('G' . $row, $totalYears);
                $sheet->setCellValue('H' . $row, $interviewScore);
                $sheet->setCellValue('I' . $row, $educationString);
                $sheet->setCellValue('J' . $row, $educationStatusString);
                $sheet->setCellValue('K' . $row, $qualificationString);
                $sheet->setCellValue('L' . $row, $skillsString);
    
                // Wrap text for multi-line cells
                $sheet->getStyle('I' . $row . ':K' . $row)->getAlignment()->setWrapText(true);
    
                // Conditional row coloring based on application score
                $rowRange = 'A' . $row . ':L' . $row;
                if ($appScore !== null) {
                    if ($appScore < 50) {
                        $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFCDD2'); // Light red
                    } elseif ($appScore < 60) {
                        $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFE0B2'); // Light orange
                    } elseif ($appScore < 80) {
                        $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF9C4'); // Light yellow
                    } else {
                        $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C8E6C9'); // Light green
                    }
                }
    
                $row++;
            }
    
            // Auto-size columns
            foreach (range('A', 'L') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
    
            // Add borders
            $tableRange = 'A' . $headerRow . ':L' . ($row - 1);
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
            // Sheet name and filename
            $sheet->setTitle('Applications Export');
            $filename = 'Applications_' . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $jobRequisition->title) . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $filePath = storage_path("app/public/{$filename}");
    
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
    
            $this->sendExportEmail($jobRequisition, $filePath, $filename);
    
            if (file_exists($filePath)) {
                unlink($filePath);
            }
    
            $this->info("📧 Export sent to tokelo.foso@cbs.co.ls for Job Requisition #{$jobRequisition->id}");
    
        } catch (\Exception $e) {
            $this->error("❌ Failed to generate export for Job Requisition #{$jobRequisition->id}: " . $e->getMessage());
            Log::error("Failed to generate export for Job Requisition #{$jobRequisition->id}: " . $e->getMessage());
        }
    }
    
    

    protected function sendExportEmail(JobRequisition $jobRequisition, string $filePath, string $filename): void
    {
        try {
            Mail::send('emails.application_export', [
                'jobTitle' => $jobRequisition->title,
                'jobReference' => $jobRequisition->job_reference,
                'exportDate' => now()->format('M j, Y g:i A'),
                'applicationCount' => $jobRequisition->applications()->count()
            ], function ($message) use ($filePath, $filename, $jobRequisition) {
                $message->to('tokelo.foso@cbs.co.ls') // send to your email for now
                        ->subject('Application Export: ' . $jobRequisition->title)
                        ->attach($filePath, [
                            'as' => $filename,
                            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]);
            });
        } catch (\Exception $e) {
            Log::error("Failed to send export email: " . $e->getMessage());
            throw $e;
        }
        
        
    }

    protected function collectReportData($requisitionId, $application, $user, $scores, $jobSkills, $minExperience, $requisition, $newStatus, $oldStatus): void
    {
        // Get user skills and experience details
        $userSkills = $user->skills ? $user->skills->pluck('name')->toArray() : [];
        $userEducation = $user->education ? $user->education->toArray() : [];
        $userQualifications = $user->qualifications ? $user->qualifications->pluck('name')->toArray() : [];
        
        // Calculate experience details
        $totalExperience = $this->calculateTotalExperienceYears($user);
        $experienceDetails = [];
        
        if ($user->experiences) {
            foreach ($user->experiences as $exp) {
                $experienceDetails[] = [
                    'company' => $exp->company_name ?? 'N/A',
                    'position' => $exp->title ?? 'N/A',
                    'start_date' => $exp->start_date ?? 'N/A',
                    'end_date' => $exp->end_date ?? 'Current',
                    'duration_years' => $exp->start_date ? 
                        Carbon::parse($exp->start_date)->diffInYears($exp->end_date ? Carbon::parse($exp->end_date) : Carbon::now()) : 0
                ];
            }
        }

        // Get skill matching details
        $skillMatchDetails = $this->debugSkillMatch($jobSkills, $userSkills);

        // Get education matching details
        $educationMatchDetails = $this->getEducationMatchDetails($user, $requisition);

        $this->reportData[$requisitionId]['applications'][] = [
            'application_id' => $application->id,
            'applicant_name' => $user->first_name . ' ' . $user->last_name,
            'applicant_email' => $user->email,
            'application_date' => $application->created_at->format('Y-m-d'),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'final_score' => $scores['total_score'],
            'skills_score' => $scores['skills_score'],
            'experience_score' => $scores['experience_score'],
            'education_score' => $scores['education_score'],
            'qualification_bonus' => $scores['qualification_bonus'],
            'user_skills' => $userSkills,
            'user_qualifications' => $userQualifications,
            'total_experience_years' => $totalExperience,
            'experience_details' => $experienceDetails,
            'education_details' => $userEducation,
            'skill_match_details' => $skillMatchDetails,
            'education_match_details' => $educationMatchDetails,
            'meets_minimum_experience' => $totalExperience >= $minExperience,
            'experience_gap' => max(0, $minExperience - $totalExperience),
        ];
    }

    protected function getEducationMatchDetails($user, $requisition): array
    {
        $details = [
            'required_level' => $requisition->required_education_level ?? 'N/A',
            'required_areas' => $requisition->required_areas_of_study ?? [],
            'user_education' => [],
            'best_match' => null,
            'meets_level_requirement' => false,
            'meets_field_requirement' => false,
        ];

        if (!$user->education || $user->education->isEmpty()) {
            return $details;
        }

        $hierarchy = $this->mapEducationLevelHierarchy();
        $requiredRank = $hierarchy[trim($requisition->required_education_level ?? '')] ?? 0;

        foreach ($user->education as $education) {
            $educationLevel = $education->education_level ?? 'N/A';
            $fieldOfStudy = $education->field_of_study ?? 'N/A';
            $status = $education->status ?? 'N/A';
            
            $applicantRank = $hierarchy[trim($educationLevel)] ?? 0;
            $meetsLevel = $applicantRank >= $requiredRank;
            $meetsField = $this->checkFieldOfStudyMatch($fieldOfStudy, $details['required_areas']);

            $eduDetail = [
                'level' => $educationLevel,
                'field' => $fieldOfStudy,
                'status' => $status,
                'institution' => $education->institution ?? 'N/A',
                'graduation_year' => $education->end_date ?? 'N/A',
                'meets_level_requirement' => $meetsLevel,
                'meets_field_requirement' => $meetsField,
                'level_rank' => $applicantRank,
            ];

            $details['user_education'][] = $eduDetail;

            // Track best match
            if ($meetsLevel || $meetsField) {
                if (!$details['best_match'] || 
                    ($meetsLevel && $meetsField) || 
                    ($meetsLevel && !$details['best_match']['meets_level_requirement'])) {
                    $details['best_match'] = $eduDetail;
                }
            }

            // Update overall flags
            if ($meetsLevel) $details['meets_level_requirement'] = true;
            if ($meetsField) $details['meets_field_requirement'] = true;
        }

        return $details;
    }

    protected function generateShortlistingReport(): void
    {
        try {
            $spreadsheet = new Spreadsheet();

            foreach ($this->reportData as $requisitionId => $requisition) {
                // Create new sheet per requisition
                $sheet = $spreadsheet->createSheet();
                $title = "Job #{$requisitionId} - {$requisition['job_title']}";
                $title = preg_replace('/[:\\/*?\[\]]/', '', $title); // remove invalid characters
                $title = substr($title, 0, 31); // max 31 characters
                $sheet->setTitle($title);
                $row = 1;

                // Requisition summary info
                $sheet->setCellValue("A{$row}", "Job Title");
                $sheet->setCellValue("B{$row}", $requisition['job_title'] ?? 'N/A');
                $row++;
                $sheet->setCellValue("A{$row}", "Job Reference");
                $sheet->setCellValue("B{$row}", $requisition['job_reference'] ?? 'N/A');
                $row++;
                $sheet->setCellValue("A{$row}", "Required Skills");
                $sheet->setCellValue("B{$row}", implode(', ', $requisition['required_skills'] ?? []));
                $row++;
                $sheet->setCellValue("A{$row}", "Min Experience");
                $sheet->setCellValue("B{$row}", $requisition['min_experience'] ?? 0);
                $row++;
                $sheet->setCellValue("A{$row}", "Threshold (%)");
                $sheet->setCellValue("B{$row}", $requisition['threshold'] ?? 0);
                $row += 2;

                // Write application headers
                $headers = [
                    'Application ID', 'Applicant Name', 'Email', 'Application Date', 'Old Status', 'New Status',
                    'Final Score', 'Skills Score', 'Experience Score', 'Education Score', 'Qualification Bonus',
                    'Total Experience (Years)', 'Meets Min Experience',
                    'All Skills', 'Matched Skills', 'Best Education Match'
                ];
                $sheet->fromArray($headers, null, "A{$row}");
                $row++;

                foreach ($requisition['applications'] as $app) {
                    // Prepare education best match text
                    $eduMatch = $app['education_match_details']['best_match'] ?? null;
                    $eduMatchText = $eduMatch 
                        ? "{$eduMatch['level']} in {$eduMatch['field']} (Level OK: " . ($eduMatch['meets_level_requirement'] ? 'Yes' : 'No') . ", Field OK: " . ($eduMatch['meets_field_requirement'] ? 'Yes' : 'No') . ")"
                        : 'No match';

                    $sheet->fromArray([
                        $app['application_id'],
                        $app['applicant_name'],
                        $app['applicant_email'],
                        $app['application_date'],
                        $app['old_status'],
                        $app['new_status'],
                        $app['final_score'],
                        $app['skills_score'],
                        $app['experience_score'],
                        $app['education_score'],
                        $app['qualification_bonus'],
                        $app['total_experience_years'],
                        $app['meets_minimum_experience'] ? 'Yes' : 'No',
                        implode(', ', $app['user_skills'] ?? []),
                        implode(', ', $app['skill_match_details']['matching_keywords'] ?? []),
                        $eduMatchText
                    ], null, "A{$row}");
                    $row++;
                }

                $row += 2; // spacing before next requisition
            }

            // Remove default empty sheet
            $spreadsheet->removeSheetByIndex(0);

            // Save file
            $timestamp = now()->format('Y-m-d_H-i-s');
            $fileName = "shortlisting_report_{$timestamp}.xlsx";
            $filePath = storage_path("app/public/{$fileName}");

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            $fileUrl = Storage::disk('public')->url($fileName);

            $this->info("📊 Shortlisting report generated successfully!");
            $this->info("📁 File saved to: {$filePath}");
            $this->info("🔗 Download URL: {$fileUrl}");

        } catch (\Exception $e) {
            $this->error("❌ Failed to generate report: " . $e->getMessage());
            Log::error("Failed to generate shortlisting report: " . $e->getMessage());
        }
    }

    // ... [Keep all the existing methods: sendRejectionNotifications, calculateApplicationScores, etc.]
    // ... [I'm not repeating them here for brevity, but they remain unchanged]

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
        
        // Direct proportional scoring: 100% match = 100% score, 50% match = 50% score, etc.
        $matchPercentage = $matchedSkillsCount / $totalJobSkills;
        return min($matchPercentage, 1.0); // Cap at 100% in case user has more skills than required
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