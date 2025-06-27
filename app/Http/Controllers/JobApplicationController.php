<?php

namespace App\Http\Controllers;


use App\Models\JobRequisition;
use Illuminate\Support\Facades\Mail;
use App\Mail\OfferLetterMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
    
        $query = JobApplication::with(['user', 'jobRequisition']);
    
        // Filter by requisition if requested
        if ($request->filled('job_requisition_id')) {
            $query->where('job_requisition_id', $request->job_requisition_id);
        }
    
        // For applicants: show only their applications and related job requisitions
        if ($user->isApplicant()) {
            $query->where('user_id', $user->id);
        }
    
        $applications = $query->latest()->get();
    
        // Job requisitions to show (only ones this user applied for, if applicant)
        $jobRequisitions = $user->isApplicant()
            ? JobRequisition::whereIn('id', $applications->pluck('job_requisition_id'))->get()
            : JobRequisition::all();
    
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
    private function notifyApplicant(JobApplication $application, $action)
    {
        // Implement notification logic here
        // This could be email, SMS, or in-app notification
        
        try {
            $messages = [
                'shortlist' => 'Congratulations! Your application has been shortlisted.',
                'reject' => 'Thank you for your interest. Unfortunately, we will not be moving forward with your application.',
                'offer_sent' => 'Great news! We have sent you a job offer.',
                'hired' => 'Welcome to the team! Your application has been approved.'
            ];
            
            $message = $messages[$action] ?? 'Your application status has been updated.';
            
            // Example: Send email notification
            // Mail::to($application->user->email)->send(new ApplicationStatusNotification($application, $message));
            
            Log::info('Notification sent to applicant', [
                'application_id' => $application->id,
                'user_email' => $application->user->email,
                'action' => $action
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send notification to applicant', [
                'application_id' => $application->id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }



public function show($uuid)
{
    $user = auth()->user();

    $job_application = JobApplication::where('uuid', $uuid)->with([
        'jobRequisition.department',
        'user.skills',
        'user.experiences',
        'user.education',
        'user.qualifications',
        'user.attachments',
    ])->firstOrFail();

    $interview = $job_application->interview;

    if ($user->isApplicant() && $job_application->user_id !== $user->id) {
        abort(403, 'Unauthorized access.');
    }

    if ($user->isHrAdmin() && $job_application->user_id === $user->id) {
        abort(403, 'Unauthorized access.');
    }
    $job_application->jobRequisition->autoShortlistApplicants();

    return view('job_applications.show', [
        'application' => $job_application,
        'interview' => $interview,
    ]);
}


    

    public function store(Request $request)
    {
        $request->validate([
            'job_requisition_id' => 'required|exists:job_requisitions,id',
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

        // You can also attach or serialize other profile info if desired, but usually you link to user only.
    
        $application->save();
    

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
public function sendOfferLetter(Request $request, $applicationId)
{
    $request->validate([
        'offer_letter' => 'required|file|mimes:pdf,doc,docx|max:5120',
        'message' => 'nullable|string|max:1000',
    ]);

    $jobApplication = JobApplication::findOrFail($applicationId);
 
    // Store uploaded offer letter file in public disk
    $filePath = $request->file('offer_letter')->store('offer_letters', 'public');
    
    // Send Mailable - pass the relative path with public/ prefix
    Mail::to($jobApplication->user->email)->send(
        new OfferLetterMail($jobApplication->user, $request->message ?? '', 'public/' . $filePath)
    );

    $jobApplication->status = 'offer sent';
    $jobApplication->save();

    return redirect()->back()->with('success', 'Offer letter sent successfully.');
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



  /**
     * Auto shortlist applications for a job requisition based on requirements.
     *
     * @param  int|string  $jobRequisitionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function autoShortlistApplications($jobRequisitionId)
    {
        // Load job with required skills
        $job = JobRequisition::with('skills')->findOrFail($jobRequisitionId);

        // Fetch all pending applications for this job
        $applications = JobApplication::with([
            'user.skills',       // Applicant skills
            'user.education',    // Applicant education
            'user.experiences',  // Applicant experiences
        ])->where('job_requisition_id', $job->id)
          ->where('status', 'pending')
          ->get();

        $educationRank = [
            'certificate' => 1,
            'diploma' => 2,
            'bachelor' => 3,
            'honours' => 4,
            'masters' => 5,
            'phd' => 6,
        ];

        $shortlistedCount = 0;

        foreach ($applications as $application) {
            $user = $application->user;

            if (!$user) {
                continue; // Skip if no user linked
            }

            // 1) Check skills match - all required skills must be possessed
            $requiredSkillIds = $job->skills->pluck('id')->toArray();
            $applicantSkillIds = $user->skills->pluck('skill_id')->toArray();

            $hasAllSkills = empty(array_diff($requiredSkillIds, $applicantSkillIds));

            if (!$hasAllSkills) {
                continue; // missing some required skills
            }

            // 2) Check minimum education level
            $minEducation = strtolower($job->education_level ?? '');

            if ($minEducation && isset($educationRank[$minEducation])) {
                // Find highest education rank applicant has
                $maxApplicantEduRank = $user->education
                    ->map(fn($edu) => $educationRank[strtolower($edu->education_level)] ?? 0)
                    ->max();

                if ($maxApplicantEduRank < $educationRank[$minEducation]) {
                    continue; // education level too low
                }
            }

            // 3) Check minimum years of experience
            $minExperience = (int) $job->min_experience;

            if ($minExperience > 0) {
                $totalExperienceYears = 0;

                foreach ($user->experiences as $exp) {
                    $start = $exp->start_date ? \Carbon\Carbon::parse($exp->start_date) : null;
                    $end = $exp->end_date ? \Carbon\Carbon::parse($exp->end_date) : \Carbon\Carbon::now();

                    if ($start) {
                        $totalExperienceYears += $end->floatDiffInYears($start);
                    }
                }

                if ($totalExperienceYears < $minExperience) {
                    continue; // not enough experience
                }
            }

            // If all checks passed, shortlist
            $application->status = 'shortlisted';
            $application->save();
            $shortlistedCount++;
        }

        return redirect()->back()->with('success', "Auto-shortlisted {$shortlistedCount} applications.");
    }


}
