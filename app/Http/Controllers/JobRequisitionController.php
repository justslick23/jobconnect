<?php

namespace App\Http\Controllers;
use App\Models\JobRequisition;
use App\Models\Skill;
use App\Models\Department;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
    
        return redirect()->route('job-requisitions.index')->with('success', 'Job Requisition Created.');
    }
    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        $jobRequisition = JobRequisition::where('uuid', $uuid)->firstOrFail();
        return view('job_requisitions.show', compact('jobRequisition'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobRequisition $jobRequisition)
    {
        return view('job_requisitions.edit', compact('jobRequisition'));
    }

    public function update(Request $request, JobRequisition $jobRequisition)
    {
        $this->authorize('update', $jobRequisition);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'vacancies' => 'required|integer|min:1',
            'location' => 'nullable|string',
            'employment_type' => 'required|in:full-time,part-time,contract,temporary',
            'application_deadline' => 'nullable|date',
            'department_id' => 'required|exists:departments,id',
        ]);

        $jobRequisition->update($validated);

        return redirect()->route('job-requisitions.index')->with('success', 'Job Requisition Updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobRequisition $jobRequisition)
    {
        $this->authorize('delete', $jobRequisition);
        $jobRequisition->delete();

        return redirect()->route('job-requisitions.index')->with('success', 'Job Requisition Deleted.');
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
