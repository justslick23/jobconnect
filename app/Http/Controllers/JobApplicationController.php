<?php

namespace App\Http\Controllers;


use App\Models\JobRequisition;
use Illuminate\Support\Facades\Mail;
use App\Mail\OfferLetterMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use App\Models\JobApplication;
use App\Models\ShortlistingSetting;
use App\Mail\JobApplicationSubmitted;
use Carbon\Carbon;
use App\Models\ApplicationAttachment;
use App\Mail\ApplicationNotShortlistedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class JobApplicationController extends Controller
{
   

    
    
    public function create(Request $request)
    {
        $jobRequisitionId = $request->query('job_requisition');
        $jobRequisition = JobRequisition::findOrFail($jobRequisitionId);
        
        $user = Auth::user();
        $profile = $user->profile;

         // Check if user has already applied for this job requisition
         $existingApplication = JobApplication::where('user_id', $user->id)
         ->where('job_requisition_id', $jobRequisitionId)
         ->first();
         
     if ($existingApplication) {
         return redirect()->route('job-applications.show', $existingApplication->uuid)
             ->with('info', 'You have already applied for this position. You can view your application status here.');
     }
        
        // Check if profile exists and is not in draft status
        if (!$profile) {
            return redirect()->route('applicant.profile.create')
                ->with('error', 'Please complete your profile before applying for jobs.');
        }
        
        // Assuming your profile has a 'status' field that can be 'draft' or 'completed'
        if ($profile->is_draft == true) {
            return redirect()->route('applicant.profile.create')
                ->with('warning', 'Please complete your profile before applying for jobs. Your profile is currently in draft status.');
        }

         // Check if user has already applied for this job requisition
         $existingApplication = JobApplication::where('user_id', $user->id)
         ->where('job_requisition_id', $request->job_requisition_id)
         ->first();

     if ($existingApplication) {
         return redirect()->route('job-applications.show', $existingApplication->uuid)
             ->with('error', 'You have already submitted an application for this position.');
     }
        
        // Alternative: Check for required fields if you don't have a status field
        // Uncomment and modify these checks based on your profile requirements
        /*
        $requiredFields = ['first_name', 'last_name', 'phone'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (empty($profile->$field)) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            $fieldNames = implode(', ', array_map('ucfirst', str_replace('_', ' ', $missingFields)));
            return redirect()->route('applicant.profile.create')
                ->with('warning', "Please complete your profile before applying. Missing fields: {$fieldNames}");
        }
        */
        
        // Get user related data
        $skills = $user->skills;
        $education = $user->education;
        $experience = $user->experiences;
        $references = $user->references;
        $qualifications = $user->qualifications;
        $attachments = $user->attachments;
        
        return view('job_applications.create', compact(
            'jobRequisition', 'profile', 'skills', 'education', 
            'experience', 'references', 'qualifications', 'attachments'
        ));
    }
    public function index(Request $request)
    {
        $user = Auth::user();
    
        // Base query with eager loading of related user, job requisition, and score
     // Base query WITHOUT eager loading
$query = JobApplication::query();

// Filter by job requisition if provided in request
if ($request->filled('job_requisition_id')) {
    $query->where('job_requisition_id', $request->job_requisition_id);
}

// If logged-in user is an applicant, only show their own applications
if ($user->isApplicant()) {
    $query->where('user_id', $user->id);
}

// Execute query
$applications = $query->get();

    
        // For applicants: get only job requisitions they applied to
        // For others (HR/admin): get all job requisitions ordered by creation date
        $jobRequisitions = $user->isApplicant()
            ? JobRequisition::whereIn('id', $applications->pluck('job_requisition_id'))
                ->orderBy('created_at', 'desc')
                ->get()
            : JobRequisition::orderBy('created_at', 'desc')->get();
    
        // Return view with applications and job requisitions
        return view('job_applications.index', compact('applications', 'jobRequisitions'));
    }
    
    
    public function quickAction(Request $request, $id)
    {
        try {
            $application = JobApplication::findOrFail($id);
            
            // Validate the action
            $validActions = ['shortlist', 'reject', 'offer_sent', 'hired'];
            $action = $request->input('action');
            
            if (!in_array($action, $validActions)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid action provided'
                ], 400);
            }
            
            // Check permissions
            $user = auth()->user();
            if (!$user->isHrAdmin()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized action'
                ], 403);
            }
            
            $oldStatus = $application->status;
            
            // Map actions to statuses
            $statusMap = [
                'shortlist' => 'shortlisted',
                'reject' => 'rejected',
                'offer_sent' => 'offer sent',
                'hired' => 'hired'
            ];
            
            $newStatus = $statusMap[$action];
            
            $application->status = $newStatus;
            $application->updated_at = now();
            $application->save();

            if ($newStatus === 'rejected') {
                Mail::to($application->applicant->email) // assuming applicant relationship exists
                    ->queue(new ApplicationNotShortlistedMail(
                        $application->user->name,
                        $application->jobRequisition->title // assuming jobRequisition relationship
                    ));
            }
            
            // Log the status change
            Log::info('Application status updated via quick action', [
                'application_id' => $application->id,
                'old_status' => $oldStatus,
                'new_status' => $application->status,
                'updated_by' => auth()->id(),
                'action' => $action
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Application status updated successfully',
                'new_status' => $application->status,
                'status_label' => $this->getStatusLabel($application->status),
                'application_id' => $application->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in quick action', [
                'application_id' => $id,
                'action' => $request->input('action'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Error updating application status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk action for multiple applications
     */
    public function bulkAction(Request $request)
    {
        try {
            $request->validate([
                'action' => [
                    'required',
                    Rule::in(['shortlist', 'reject', 'offer_sent', 'hired'])
                ],
                'applications' => 'required|array|min:1',
                'applications.*' => 'exists:job_applications,id'
            ]);
            
            // Check permissions
            $user = auth()->user();
            if (!$user->isHrAdmin()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized action'
                ], 403);
            }
            
            $action = $request->input('action');
            $applicationIds = $request->input('applications');
            
            // Map action to status
            $statusMap = [
                'shortlist' => 'shortlisted',
                'reject' => 'rejected',
                'offer_sent' => 'offer sent',
                'hired' => 'hired'
            ];
            
            $newStatus = $statusMap[$action];
            
            DB::beginTransaction();
            
            try {
                // Get applications before update for logging
                $applications = JobApplication::whereIn('id', $applicationIds)->get();
                
                $updatedCount = JobApplication::whereIn('id', $applicationIds)
                    ->update([
                        'status' => $newStatus,
                        'updated_at' => now()
                    ]);

                    if ($newStatus === 'rejected') {
                        foreach ($applications as $app) {
                            Mail::to($app->applicant->email)
                                ->queue(new ApplicationNotShortlistedMail(
                                    $app->user->name,
                                    $app->jobRequisition->title
                                ));
                        }
                    }
                
                DB::commit();
                
                // Log bulk update
                Log::info('Bulk application status update', [
                    'action' => $action,
                    'new_status' => $newStatus,
                    'applications_updated' => $updatedCount,
                    'application_ids' => $applicationIds,
                    'updated_by' => auth()->id()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "{$updatedCount} application(s) updated to '{$this->getStatusLabel($newStatus)}' successfully",
                    'updated_count' => $updatedCount,
                    'new_status' => $newStatus,
                    'status_label' => $this->getStatusLabel($newStatus),
                    'application_ids' => $applicationIds
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in bulk application status update', [
                'action' => $request->input('action'),
                'applications' => $request->input('applications'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating applications: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get human-readable status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'submitted' => 'Submitted',
            'shortlisted' => 'Shortlisted',
            'rejected' => 'Rejected',
            'offer sent' => 'Offer Sent',
            'offer_sent' => 'Offer Sent',
            'hired' => 'Hired',
            'interview scheduled' => 'Interview Scheduled',
            'review' => 'Under Review'
        ];
        
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
    
    /**
     * Get status badge class for frontend
     */
    private function getStatusBadgeClass($status)
    {
        $classes = [
            'submitted' => 'badge-info',
            'shortlisted' => 'badge-warning',
            'rejected' => 'badge-danger',
            'offer sent' => 'badge-primary',
            'offer_sent' => 'badge-primary',
            'hired' => 'badge-success',
            'interview scheduled' => 'badge-warning',
            'review' => 'badge-secondary'
        ];
        
        return $classes[$status] ?? 'badge-secondary';
    }
    
    /**
     * Optional: Send notification to applicant about status change
     */
 


     public function show($uuid)
     {
         $user = auth()->user();
     
         // Find job application by UUID with 'score' eager loaded
         $job_application = JobApplication::with('score')->where('uuid', $uuid)->firstOrFail();
         $settings = ShortlistingSetting::first();
     
         // Authorization checks
         if ($user->isApplicant() && $job_application->user_id !== $user->id) {
             abort(403, 'Unauthorized access.');
         }
     
         if ($user->isHrAdmin() && $job_application->user_id === $user->id) {
             abort(403, 'Unauthorized access.');
         }
     
         $interview = $job_application->interview;
     
         return view('job_applications.show', [
             'application' => $job_application,
             'interview' => $interview,
             'settings' => $settings
         ]);
     }
     

    

    public function store(Request $request)
    {
        $request->validate([
            'job_requisition_id' => 'required|exists:job_requisitions,id',
            'application_source' => 'required|string|max:255',
            'other_source' => 'nullable|string|max:255',
        ]);
    
        $user = Auth::user();
    
        // Optional: Check if user has completed profile or other eligibility criteria
        
        if (!$user->profile) {
            return back()->withErrors(['profile' => 'Please complete your profile before applying.']);
        }
    
        // Create the job application
        $application = new JobApplication();
        $application->user_id = $user->id;
        $application->job_requisition_id = $request->job_requisition_id;
        $application->application_source = $request->application_source === "Other"
    ? $request->other_source
    : $request->application_source;

        // You can also attach or serialize other profile info if desired, but usually you link to user only.
    
        $application->save();
        Mail::to($user->email)->send(new JobApplicationSubmitted($application));


        return redirect()->route('job-applications.index')->with('success', 'Application submitted!');
    }



    public function downloadResume($applicationId)
    {
        $application = JobApplication::with([
            'user.profile', 
            'user.skills', 
            'user.experiences', 
            'user.education', 
            'user.qualifications', 
            'user.references',
            'jobRequisition.department',
        
        ])->findOrFail($applicationId);
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.resume', compact('application'))
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'Arial',
            'debugKeepTemp' => false,
            'dpi' => 96, // default is 96, lowering dpi reduces quality but increases speed
            'fontCache' => storage_path('fonts/'), // ensure this folder exists and is writable
        ]);
    
        
        $fileName = 'Resume_' . str_replace(' ', '_', $application->user->name) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }

public function exportProfile($id)
{
    $application = JobApplication::with([
        'user.profile',
        'user.skills',
        'user.experiences' => function($query) {
            $query->orderBy('start_date', 'desc');
        },
        'user.education' => function($query) {
            $query->orderBy('start_date', 'desc');
        },
        'user.attachments',
        'jobRequisition.department'
    ])->findOrFail($id);

    // Check permissions
    if (!auth()->user()->isHrAdmin() && !auth()->user()->isManager()) {
        abort(403, 'Unauthorized');
    }

    $user = $application->user;
    $profile = $user->profile;

    // Prepare data for PDF
    $data = [
        'user' => $user,
        'profile' => $profile,
        'application' => $application,
        'skills' => $user->skills,
        'experiences' => $user->experiences,
        'education' => $user->education,
        'appliedJob' => $application->jobRequisition
    ];

    // Generate PDF
    $pdf = Pdf::loadView('pdf.application', $data);
    $pdf->setPaper('A4', 'portrait');
    
    $fileName = 'CV_' . str_replace(' ', '_', $user->name) . '_' . now()->format('Y-m-d') . '.pdf';
    
    return $pdf->download($fileName);
}
public function sendOfferLetter($applicationId)
{
    $jobApplication = JobApplication::findOrFail($applicationId);
    
    // Update status to indicate offer has been sent (physically)
    $jobApplication->status = 'offer sent';
    $jobApplication->save();

    return redirect()->back()->with('success', 'Application status updated to "Offer Sent". Physical offer letter can now be prepared and sent.');
}

public function submitReview(Request $request, JobApplication $application)
{
    $request->validate([
        'comments' => 'required|string|max:2000',
        'rating' => 'required|integer|min:1|max:5',
        'recommendation' => 'required|in:proceed,hold,reject',
    ]);

    \App\Models\ApplicationReview::updateOrCreate(
        [
            'job_application_id' => $application->id,
            'user_id' => auth()->id(),
        ],
        [
            'comments' => $request->comments,
            'rating' => $request->rating,
            'recommendation' => $request->recommendation,
        ]
    );

    return redirect()->back()->with('success', 'Review submitted successfully.');
}

public function exportByJob($jobId)
{
    try {
        // Find the job requisition with department
        $jobRequisition = JobRequisition::with(['department'])->findOrFail($jobId);

        // Get all applications with related data, sorted by application score descending
        $applications = JobApplication::where('job_requisition_id', $jobId)
            ->with(['user.profile', 'user.education', 'user.qualifications', 'user.skills', 'score', 'interviews'])
            ->get()
            ->sortByDesc(fn($a) => $a->score->total_score ?? 0);

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('HR Management System')
            ->setTitle("Applications for {$jobRequisition->title}")
            ->setDescription("Job applications export for {$jobRequisition->title}")
            ->setKeywords('job applications export')
            ->setCategory('HR Reports');

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
            'I' => 'Education Level',
            'J' => 'Area of Study',
            'K' => 'Institution',
            'L' => 'Qualification Title',
            'M' => 'Qualification Institution',
            'N' => 'Skills'
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $headerRange = 'A' . $headerRow . ':N' . $headerRow;
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

            // Applicant info
            $sheet->setCellValue('A' . $row, $user->name ?? 'N/A');
            $sheet->setCellValue('B' . $row, $user->email ?? 'N/A');
            $sheet->setCellValue('C' . $row, $profile->phone ?? 'N/A');
            $sheet->setCellValue('D' . $row, ucfirst($application->status));
            $sheet->setCellValue('E' . $row, $application->created_at->format('M j, Y'));

            // Application score
            $appScore = $application->score ? $application->score->total_score : null;
            $sheet->setCellValue('F' . $row, $appScore !== null ? number_format($appScore, 2) . '/100' : 'Not Scored');

            // Experience in whole years
            $experiences = $user->experiences ?? collect();
            $totalYears = $experiences->sum(function($exp) {
                $start = Carbon::parse($exp->start_date);
                $end = $exp->end_date ? Carbon::parse($exp->end_date) : Carbon::now();
                return $start->diffInYears($end);
            });
            $sheet->setCellValue('G' . $row, $totalYears ?: 'N/A');
            

            // Interview score
            $interviewScore = $application->interviews && $application->interviews->averageScore() !== null
                ? $application->interviews->averageScore() . '/5'
                : 'Not Conducted';
            $sheet->setCellValue('H' . $row, $interviewScore);

            // Education info
            $education = $user->education->sortByDesc(fn($e) => $e->graduation_year ?? 0)->first();
            $sheet->setCellValue('I' . $row, $education->degree ?? 'N/A');
            $sheet->setCellValue('J' . $row, $education->field_of_study ?? 'N/A');
            $sheet->setCellValue('K' . $row, $education->institution ?? 'N/A');
            
            // Qualifications / certifications
            $qualification = $user->qualifications->first();
            $sheet->setCellValue('L' . $row, $qualification->title ?? 'N/A');
            $sheet->setCellValue('M' . $row, $qualification->institution ?? 'N/A');

            // Skills (comma-separated)
            $skills = $user->skills ?? collect();
            $skillsString = $skills->isEmpty() ? 'N/A' : $skills->pluck('name')->implode(', ');
            $sheet->setCellValue('N' . $row, $skillsString);

            // Conditional row coloring based on application score
            $rowRange = 'A' . $row . ':N' . $row;
            if ($appScore !== null) {
                if ($appScore < 60) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA'); // red
                } elseif ($appScore < 70) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3CD'); // yellow
                } else {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E8'); // green
                }
            }

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $tableRange = 'A' . $headerRow . ':N' . ($row - 1);
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Sheet name and filename
        $sheet->setTitle('Applications Export');
        $filename = 'Applications_' . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $jobRequisition->title) . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;

    } catch (\Exception $e) {
        \Log::error('Excel export error: ' . $e->getMessage());
        return back()->with('error', 'Failed to export data. Please try again.');
    }
}



    /**
     * Export all applications across all job requisitions
     */
    public function exportAll()
    {
        try {
            $applications = JobApplication::with([
                'user.profile',
                'user.experiences',
                'user.education',
                'user.skills',
                'user.qualifications',
                'jobRequisition.department',
                'score',
                'interviews'
            ])->orderBy('created_at', 'desc')->get();
    
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
    
            // Document properties
            $spreadsheet->getProperties()
                ->setCreator('HR Management System')
                ->setTitle('All Job Applications Export')
                ->setDescription('Complete job applications export')
                ->setKeywords('job applications export all')
                ->setCategory('HR Reports');
    
            // Headers
            $headers = [
                'A' => 'Job Title',
                'B' => 'Department',
                'C' => 'Applicant Name',
                'D' => 'Email',
                'E' => 'Phone',
                'F' => 'Status / Review',
                'G' => 'Application Date',
                'H' => 'Application Score',
                'I' => 'Experience (Years)',
                'J' => 'Interview Score',
                'K' => 'Education Level',
                'L' => 'Institution',
                'M' => 'Areas of Study',
                'N' => 'Skills',
                'O' => 'Qualifications'
            ];
    
            $headerRow = 1;
            foreach ($headers as $col => $header) {
                $sheet->setCellValue($col . $headerRow, $header);
            }
    
            // Style headers
            $headerRange = 'A' . $headerRow . ':O' . $headerRow;
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2196F3');
            $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
            // Add data
            $row = 2;
            foreach ($applications as $application) {
    
                // Applicant info
                $sheet->setCellValue('A' . $row, $application->jobRequisition->title ?? 'N/A');
                $sheet->setCellValue('B' . $row, $application->jobRequisition->department->name ?? 'General');
                $sheet->setCellValue('C' . $row, $application->user->name ?? 'N/A');
                $sheet->setCellValue('D' . $row, $application->user->email ?? 'N/A');
                $sheet->setCellValue('E' . $row, $application->user->profile->phone ?? 'N/A');
                $sheet->setCellValue('F' . $row, ucfirst($application->status) . 
                    ($application->review ? ' / ' . ucfirst($application->review) : ''));
                $sheet->setCellValue('G' . $row, $application->created_at->format('M j, Y'));
    
                // Application score
                $appScore = $application->score ? $application->score->total_score : null;
                $sheet->setCellValue('H' . $row, $appScore !== null ? $appScore : 'Not Scored');
    
                // Experience (whole years)
                $experiences = $application->user->experiences ?? collect();
                if ($experiences->isEmpty()) {
                    $totalYears = 'N/A';
                } else {
                    $totalMonths = $experiences->sum(function($exp) {
                        $start = Carbon::parse($exp->start_date);
                        $end = $exp->end_date ? Carbon::parse($exp->end_date) : Carbon::now();
                        return $start->diffInMonths($end);
                    });
                    $totalYears = floor($totalMonths / 12);
                }
                $sheet->setCellValue('I' . $row, $totalYears);
    
                // Interview score
                $interviewScore = $application->interviews && $application->interviews->averageScore() !== null ? 
                    $application->interviews->averageScore() . '/5' : 'Not Conducted';
                $sheet->setCellValue('J' . $row, $interviewScore);
    
                // Education and Institution
                $education = $application->user->education ?? collect();
                $sheet->setCellValue('K' . $row, $education->pluck('degree')->filter()->implode(', ') ?: 'N/A');
                $sheet->setCellValue('L' . $row, $education->pluck('institution')->filter()->implode(', ') ?: 'N/A');
                $sheet->setCellValue('M' . $row, $education->pluck('area_of_study')->filter()->implode(', ') ?: 'N/A');
    
                // Skills
                $skills = $application->user->skills ?? collect();
                $sheet->setCellValue('N' . $row, $skills->isEmpty() ? 'N/A' : implode(",\n", $skills->pluck('name')->toArray()));
    
                // Qualifications (title + institution)
                $qualifications = $application->user->qualifications ?? collect();
                $sheet->setCellValue('O' . $row, $qualifications->isEmpty() ? 'N/A' : 
                    $qualifications->map(fn($q) => $q->title . ' - ' . ($q->institution ?? 'N/A'))->implode(",\n"));
    
                // Enable text wrap
                $sheet->getStyle('A' . $row . ':O' . $row)->getAlignment()->setWrapText(true);
    
                // Auto row height
                $sheet->getRowDimension($row)->setRowHeight(-1);
    
                // Conditional row coloring based on application score
                $rowRange = 'A' . $row . ':O' . $row;
                if ($appScore === null) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F5');
                } elseif ($appScore < 60) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC7CE'); // Red
                } elseif ($appScore >= 60 && $appScore <= 84) {
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFEB9C'); // Yellow
                } else { // 85+
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6EFCE'); // Green
                }
    
                $row++;
            }
    
            // Auto-size columns
            foreach (range('A', 'O') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
    
            // Borders
            $tableRange = 'A1:O' . ($row - 1);
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
            $sheet->setTitle('All Applications');
            $filename = 'All_Applications_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
    
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
    
            $writer->save('php://output');
            exit;
    
        } catch (\Exception $e) {
            \Log::error('Excel export all error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export data. Please try again.');
        }
    }
    
    public function downloadAttachment($id)
    {
        $attachment = ApplicationAttachment::findOrFail($id);
    
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, "File not found on server.");
        }
    
        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }
    

  /**
     * Auto shortlist applications for a job requisition based on requirements.
     *
     * @param  int|string  $jobRequisitionId
     * @return \Illuminate\Http\RedirectResponse
     */
 


}
