<?php

namespace App\Http\Controllers;
use App\Models\JobRequisition;
use App\Models\Skill;
use App\Models\Department;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd; // or PngImageBackEnd if available
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class JobRequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = Auth::user();

        if ($user->isHrAdmin() || $user->isManager()) {
            $requisitions = JobRequisition::latest()->get();
        } else {
            $requisitions = JobRequisition::where('job_status', 'active')->get();
        }

        return view('job_requisitions.index', compact('requisitions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $skills = Skill::all(); // returns a collection of skill names


        return view('job_requisitions.create', compact('departments', 'skills'));

    }
    public function downloadPdf($id)
{
    $jobRequisition = JobRequisition::with('department','skills')->findOrFail($id);

    $renderer = new ImageRenderer(
        new RendererStyle(100),
        new SvgImageBackEnd()
    );

    $writer = new Writer($renderer);

    // Generate SVG string
    $svg = $writer->writeString(route('job-requisitions.show', $jobRequisition->slug_uuid));

    // Convert SVG string into a data URI (for <img src="...">)
    $qrCodeUrl = 'data:image/svg+xml;base64,' . base64_encode($svg);

    // Pass requisition + QR code URL to view
    $pdf = Pdf::loadView('pdf.requisition', [
                'jobRequisition' => $jobRequisition,
                'qrCodeUrl' => $qrCodeUrl
            ])
            ->setPaper('a4', 'portrait');

    $filename = Str::title($jobRequisition->title) . '-' . $jobRequisition->uuid . '-Details.pdf';

    return $pdf->download($filename);
}


    public function edit(JobRequisition $jobRequisition)
{
    // Check authorization (optional - adjust based on your authorization logic)
    // $this->authorize('update', $jobRequisition);

    // Get all departments for the dropdown
    $departments = Department::orderBy('name')->get();

    // Get all skills for the multi-select
    $skills = Skill::orderBy('name')->get();

    // Load the job requisition with its relationships
    $jobRequisition->load(['department', 'skills']);

    return view('job_requisitions.edit', compact('jobRequisition', 'departments', 'skills'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'vacancies' => 'required|integer|min:1',
            'location' => 'nullable|string',
            'employment_type' => 'required|in:full-time,part-time,contract,temporary',
            'application_deadline' => 'nullable|date',
            'department_id' => 'required|exists:departments,id',
            'min_experience' => 'required|integer|min:0',
            'education_level' => 'required|string|max:50',
            'required_skills' => 'required|array|min:1',
            'required_skills.*' => 'integer|exists:skills,id',
            'area_of_study' => 'required|array|min:1',
            'area_of_study.*' => 'string|max:100',
        ]);
    
        $job = new JobRequisition($validated);
        $job->uuid = Str::uuid();
        $job->created_by = Auth::id();
        $job->reference_number = 'JOB-' . str_pad(JobRequisition::count() + 1, 5, '0', STR_PAD_LEFT);
        if(auth()->user()->isHrAdmin()) {
            $job->approval_status = 'approved'; // auto-approve for HR Admins
            $job->approved_by = Auth::id();
            $job->approved_at = now();
            $job->job_status = 'active'; // enable job status when approved
        } else {
            $job->approval_status = 'pending'; // default status for others
            $job->job_status = 'inactive'; // default job status
        }
        $job->save();
    
        // Attach skills via pivot
        $job->skills()->sync($request->required_skills);
        $job->update([
            'required_areas_of_study' => $request->area_of_study, 
        ]);

        $users = \App\Models\User::all()->filter(function ($user) {
            return $user->isApplicant();
        });
        
        foreach ($users as $user) {
            \Mail::to($user->email)->queue(new \App\Mail\NewJobPosted($job));
        }
    
        return redirect()->route('job-requisitions.index')->with('success', 'Job Requisition Created.');
    }
    /**
     * Display the specified resource.
     */
    public function show($slugUuid)
    {
        // Match a UUID at the end of the string
        if (preg_match('/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/i', $slugUuid, $matches)) {
            $uuid = $matches[1];
        } else {
            abort(404); // invalid format
        }
    
        $jobRequisition = JobRequisition::where('uuid', $uuid)->firstOrFail();
    
        return view('job_requisitions.show', compact('jobRequisition'));
    }
    
    

    /**
     * Show the form for editing the specified resource.
     */
  

   

    /**
     * Remove the specified resource from storage.
     */
   
    /**
     * Update the specified job requisition in storage.
     */
    public function update(Request $request, JobRequisition $jobRequisition)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'vacancies' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'employment_type' => 'required|in:full-time,part-time,contract,temporary',
            'application_deadline' => 'nullable|date|after:now',
            'min_experience' => 'required|integer|min:0',
            'education_level' => 'required|string|max:255',
            'required_skills' => 'required|array|min:1',
            'required_skills.*' => 'exists:skills,id',
            'area_of_study' => 'required|array|min:1',
            'area_of_study.*' => 'string|max:255',
            'job_status' => 'nullable|in:active,inactive,closed', // Made nullable and added inactive
        ]);
    
        try {
            // Convert datetime-local format to Carbon instance
            if ($validatedData['application_deadline']) {
                $validatedData['application_deadline'] = Carbon::parse($validatedData['application_deadline']);
            }
    
            // Remove area_of_study from validated data as we'll handle it separately
            $areaOfStudy = $validatedData['area_of_study'];
            unset($validatedData['area_of_study']);
    
            // Update the job requisition (without area_of_study)
            $jobRequisition->update($validatedData);
    
            // Update areas of study separately - use the same field name as in store method
            $jobRequisition->update([
                'required_areas_of_study' => $areaOfStudy, // Keep as array if your model casts it
                // OR if you want to store as JSON consistently:
                // 'required_areas_of_study' => json_encode($areaOfStudy),
            ]);
    
            // Sync the skills relationship
            $jobRequisition->skills()->sync($validatedData['required_skills']);
    
            return redirect()
                ->route('job-requisitions.index')
                ->with('success', 'Job requisition updated successfully!');
    
        } catch (\Exception $e) {
            Log::error('Job Requisition Update Error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'There was an error updating the job requisition. Please try again.');
        }
    }

    /**
     * Remove the specified job requisition from storage.
     */
    public function destroy(JobRequisition $jobRequisition)
    {
        try {
            // Detach all skills before deleting
            $jobRequisition->skills()->detach();
            
            // Delete the job requisition
            $jobRequisition->delete();

            return redirect()
                ->route('job-requisitions.index')
                ->with('success', 'Job requisition deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Job Requisition Deletion Error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'There was an error deleting the job requisition. Please try again.');
        }
    }

    public function approve(JobRequisition $jobRequisition)
    {
        $jobRequisition->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'job_status' => 'active', // enable job status when approved
        ]);
    
        return redirect()->back()->with('success', 'Job approved and opened.');
    }
    

    public function reject(JobRequisition $jobRequisition)
    {
        $jobRequisition->update([
            'approval_status' => 'rejected',
        ]);

        return redirect()->back()->with('success', 'Job rejected.');
    }
}
