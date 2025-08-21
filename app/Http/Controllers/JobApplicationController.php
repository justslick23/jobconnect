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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
    
        // Assuming you have these relationships set up on the User model
        $profile = $user->profile;  
        $skills = $user->skills;
        $education = $user->education;
        $experience = $user->experiences;
        $references = $user->references;
        $qualifications = $user->qualifications;
        $attachments = $user->attachments;

        return view('job_applications.create', compact(
            'jobRequisition', 'profile', 'skills', 'education', 'experience', 'references', 'qualifications', 'attachments'
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
            if (!auth()->user()->isHrAdmin() && !auth()->user()->canManageApplications()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized action'
                ], 403);
            }
            
            $oldStatus = $application->status;
            
            // Map actions to statuses
            switch ($action) {
                case 'shortlist':
                    $application->status = 'shortlisted';
                    break;
                case 'reject':
                    $application->status = 'rejected';
                    break;
                case 'offer_sent':
                    $application->status = 'offer_sent';
                    break;
                case 'hired':
                    $application->status = 'hired';
                    break;
            }
            
            $application->updated_by = auth()->id();
            $application->save();
            
            // Log the status change
            Log::info('Application status updated', [
                'application_id' => $application->id,
                'old_status' => $oldStatus,
                'new_status' => $application->status,
                'updated_by' => auth()->id()
            ]);
            
            // Optional: Send notification to applicant
            // $this->notifyApplicant($application, $action);
            
            return response()->json([
                'success' => true, 
                'message' => 'Application status updated successfully',
                'new_status' => $application->status,
                'status_label' => $this->getStatusLabel($application->status)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating application status', [
                'application_id' => $id,
                'action' => $request->input('action'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Error updating application status. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Update application status (alternative method using PATCH)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => [
                    'required',
                    Rule::in(['submitted', 'shortlisted', 'rejected', 'offer sent', 'hired'])
                ]
            ]);
    
            $application = JobApplication::findOrFail($id);
    
            // Check permissions
            if (!auth()->user()->isHrAdmin() && !auth()->user()->canManageApplications()) {
                return redirect()->back()->with('error', 'You are not authorized to update this application.');
            }
    
            $oldStatus = $application->status;
            $application->status = $request->input('status');
            $application->save();
    
            Log::info('Application status updated', [
                'application_id' => $application->id,
                'old_status' => $oldStatus,
                'new_status' => $application->status,
                'updated_by' => auth()->id()
            ]);
    
            return redirect()->back()->with('success', 'Application status updated successfully.');
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating application status', [
                'application_id' => $id,
                'status' => $request->input('status'),
                'error' => $e->getMessage()
            ]);
    
            return redirect()->back()->with('error', 'Something went wrong while updating the application.');
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
                    Rule::in(['shortlist', 'reject', 'offersent', 'hired'])
                ],
                'applications' => 'required|array|min:1',
                'applications.*' => 'exists:job_applications,id'
            ]);
            
            // Check permissions
            if (!auth()->user()->isHrAdmin() && !auth()->user()->canManageApplications()) {
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
                $updatedCount = JobApplication::whereIn('id', $applicationIds)
                    ->update([
                        'status' => $newStatus,

                        'updated_at' => now()
                    ]);
                
                DB::commit();
                
                Log::info('Bulk application status update', [
                    'action' => $action,
                    'new_status' => $newStatus,
                    'applications_updated' => $updatedCount,
                    'application_ids' => $applicationIds,
                    'updated_by' => auth()->id()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "{$updatedCount} application(s) updated successfully",
                    'updated_count' => $updatedCount,
                    'new_status' => $newStatus
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in bulk application status update', [
                'action' => $request->input('action'),
                'applications' => $request->input('applications'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating applications. Please try again.'
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
            'offer_sent' => 'Offer Sent',
            'hired' => 'Hired'
        ];
        
        return $labels[$status] ?? ucfirst($status);
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

        // Get all applications with related data
        $applications = JobApplication::where('job_requisition_id', $jobId)
        ->orderBy('created_at', 'desc')
        ->get();

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
            'A' => '#',
            'B' => 'Applicant Name',
            'C' => 'Email',
            'D' => 'Phone',
            'E' => 'Status',
            'F' => 'Application Date',
            'G' => 'Application Score',
            'H' => 'Interview Score',
            'I' => 'Experience (Years)',
            'J' => 'Education Level',
            'K' => 'Skills',
            'L' => 'Cover Letter Preview',
            'M' => 'Last Updated'
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . $headerRow, $header);
        }

        $headerRange = 'A' . $headerRow . ':M' . $headerRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2196F3');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Populate applications
        $row = $headerRow + 1;
        $counter = 1;

        foreach ($applications as $application) {
            $sheet->setCellValue('A' . $row, $counter);
            $sheet->setCellValue('B' . $row, $application->user->name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $application->user->email ?? 'N/A');

            // Phone
            $phone = $application->user->profile->phone ?? 'N/A';
            $sheet->setCellValue('D' . $row, $phone);

            $sheet->setCellValue('E' . $row, ucfirst($application->status));
            $sheet->setCellValue('F' . $row, $application->created_at->format('M j, Y'));

            // Application score
            $appScore = $application->score ? number_format($application->score->total_score, 2) . '/100' : 'Not Scored';
            $sheet->setCellValue('G' . $row, $appScore);

            // Interview score
            $interviewScore = $application->interviews && $application->interviews->averageScore() !== null
                ? $application->interviews->averageScore() . '/5'
                : 'Not Conducted';
            $sheet->setCellValue('H' . $row, $interviewScore);

            // Experience in years
            $experiences = $application->user->experiences ?? collect();
            if ($experiences->isEmpty()) {
                $totalYears = 'N/A';
            } else {
                $totalMonths = $experiences->sum(function($exp) {
                    $start = Carbon::parse($exp->start_date);
                    $end = $exp->end_date ? Carbon::parse($exp->end_date) : Carbon::now();
                    return $start->diffInMonths($end);
                });
                $totalYears = round($totalMonths / 12, 1);
            }
            $sheet->setCellValue('I' . $row, $totalYears);

            // Education (all degrees)
            $educationString = $application->user->education
                ->pluck('degree')
                ->filter()
                ->implode(', ');
            $sheet->setCellValue('J' . $row, $educationString ?: 'N/A');

            // Skills (all names)
            $skills = $application->user->skills ?? collect();
            $skillsString = $skills->isEmpty() ? 'N/A' : $skills->pluck('name')->implode(', ');
            $sheet->setCellValue('K' . $row, $skillsString);

            // Cover letter preview
            $coverLetter = $application->cover_letter ? substr(strip_tags($application->cover_letter), 0, 100) . '...' : 'N/A';
            $sheet->setCellValue('L' . $row, $coverLetter);

            $sheet->setCellValue('M' . $row, $application->updated_at->format('M j, Y g:i A'));

            // Row coloring based on status
            $rowRange = 'A' . $row . ':M' . $row;
            switch (strtolower($application->status)) {
                case 'hired':
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E8');
                    break;
                case 'shortlisted':
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3CD');
                    break;
                case 'rejected':
                    $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8D7DA');
                    break;
            }

            $row++;
            $counter++;
        }

        // Auto-size columns
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $tableRange = 'A' . $headerRow . ':M' . ($row - 1);
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Summary statistics
        $summaryRow = $row + 2;
        $sheet->setCellValue('A' . $summaryRow, 'SUMMARY STATISTICS');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(12);

        $summaryRow++;
        $sheet->setCellValue('A' . $summaryRow, 'Total Applications:')->setCellValue('B' . $summaryRow, $applications->count());

        $summaryRow++;
        $sheet->setCellValue('A' . $summaryRow, 'Shortlisted:')->setCellValue('B' . $summaryRow, $applications->where('status', 'shortlisted')->count());

        $summaryRow++;
        $sheet->setCellValue('A' . $summaryRow, 'Hired:')->setCellValue('B' . $summaryRow, $applications->where('status', 'hired')->count());

        $summaryRow++;
        $sheet->setCellValue('A' . $summaryRow, 'Rejected:')->setCellValue('B' . $summaryRow, $applications->where('status', 'rejected')->count());

        $summaryRow++;
        $sheet->setCellValue('A' . $summaryRow, 'Pending:')->setCellValue('B' . $summaryRow, $applications->where('status', 'submitted')->count());

        // Style summary section
        $summaryRange = 'A' . ($summaryRow - 5) . ':B' . $summaryRow;
        $sheet->getStyle($summaryRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F5F5');
        $sheet->getStyle('A' . ($summaryRow - 5) . ':A' . $summaryRow)->getFont()->setBold(true);

        // Sheet name and filename
        $sheet->setTitle('Applications Export');
        $filename = 'Applications_' . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $jobRequisition->title) . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        // Output file
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
            $applications = JobApplication::with(['user', 'jobRequisition.department', 'score', 'interviews'])
                ->orderBy('created_at', 'desc')
                ->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('HR Management System')
                ->setTitle('All Job Applications Export')
                ->setDescription('Complete job applications export')
                ->setKeywords('job applications export all')
                ->setCategory('HR Reports');

            // Headers
            $headers = [
                'A' => '#',
                'B' => 'Job Title',
                'C' => 'Department',
                'D' => 'Applicant Name',
                'E' => 'Email',
                'F' => 'Status',
                'G' => 'Application Date',
                'H' => 'Application Score',
                'I' => 'Interview Score',
                'J' => 'Last Updated'
            ];

            $headerRow = 1;
            foreach ($headers as $column => $header) {
                $sheet->setCellValue($column . $headerRow, $header);
            }

            // Style headers
            $headerRange = 'A1:J1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2196F3');
            $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');

            // Add data
            $row = 2;
            $counter = 1;

            foreach ($applications as $application) {
                $sheet->setCellValue('A' . $row, $counter);
                $sheet->setCellValue('B' . $row, $application->jobRequisition->title ?? 'N/A');
                $sheet->setCellValue('C' . $row, $application->jobRequisition->department->name ?? 'General');
                $sheet->setCellValue('D' . $row, $application->user->name ?? 'N/A');
                $sheet->setCellValue('E' . $row, $application->user->email ?? 'N/A');
                $sheet->setCellValue('F' . $row, ucfirst($application->status));
                $sheet->setCellValue('G' . $row, $application->created_at->format('M j, Y'));
                
                $appScore = $application->score ? 
                    number_format($application->score->total_score, 2) . '/100' : 'Not Scored';
                $sheet->setCellValue('H' . $row, $appScore);
                
                $interviewScore = $application->interviews && $application->interviews->averageScore() !== null ? 
                    $application->interviews->averageScore() . '/5' : 'Not Conducted';
                $sheet->setCellValue('I' . $row, $interviewScore);
                
                $sheet->setCellValue('J' . $row, $application->updated_at->format('M j, Y'));
                
                $row++;
                $counter++;
            }

            // Auto-size columns
            foreach (range('A', 'J') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Add borders
            $tableRange = 'A1:J' . ($row - 1);
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

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



  /**
     * Auto shortlist applications for a job requisition based on requirements.
     *
     * @param  int|string  $jobRequisitionId
     * @return \Illuminate\Http\RedirectResponse
     */
 


}
