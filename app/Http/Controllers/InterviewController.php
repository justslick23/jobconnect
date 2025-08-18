<?php

namespace App\Http\Controllers;
use App\Models\JobApplication;
use App\Models\Interview;
use App\Models\InterviewReview;
use App\Models\InterviewScore;

use Illuminate\Http\Request;

class InterviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $role = $user->role;
    
        if ($user->isApplicant()) {
            // Applicant: show only their interviews
            $interviews = Interview::whereHas('jobApplication', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('jobApplication')->orderBy('interview_date', 'desc')->paginate(10);
    
        } elseif ($user->isHrAdmin() || $user->isManager()) {
            // Manager or HR/Admin: show all interviews, or filter based on business rules
            $interviews = Interview::with(['jobApplication.user'])->orderBy('interview_date', 'desc')->paginate(10);
        } else {
            // Other roles - no interviews to show
            $interviews = collect();
        }
    
        return view('interviews.index', compact('interviews'));
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        //

        $application = JobApplication::findOrFail($id);

        // Pass application to interview scheduling form
        return view('interviews.schedule', compact('application'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'job_application_id' => 'required|exists:job_applications,id',
            'interview_date' => 'required|date',
        ]);
    
        $application = JobApplication::findOrFail($request->job_application_id);
        
        $interview = Interview::create([
            'job_application_id' => $request->job_application_id,
            'interview_date' => $request->interview_date,
            'applicant_id' => $request->applicant_id,
        ]);
    
        $user = $application->user;
        if ($user && $user->email) {
            \Mail::to($user->email)->send(new \App\Mail\ShortlistedInterviewNotification($application, $interview));
        }
    
        return redirect()->back()->with('success', 'Interview scheduled and applicant notified.');
    }
    
    public function submitReview(Request $request)
    {
        $request->validate([
            'interview_id' => 'required|exists:interviews,id',
            'comments' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'recommendation' => 'required|in:proceed,hold,reject',
        ]);
    
        // Create or update review for this interview and user
        $review = InterviewReview::updateOrCreate(
            [
                'interview_id' => $request->interview_id,
                'user_id' => auth()->user()->id,
            ],
            [
                'comments' => $request->comments,
                'rating' => $request->rating,
                'recommendation' => $request->recommendation,
            ]
        );
    
        return redirect()->back()->with('success', 'Interview review submitted successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Interview $interview)
    {
        // Optionally, add authorization checks here
    
        $interview->load('jobApplication.user'); // eager load relations
    
        return view('interviews.show', compact('interview'));
    }


 

    // Store or update score
    public function submitScore(Request $request, Interview $interview)
    {
        $validated = $request->validate([
            'technical_skills' => 'required|integer|between:1,5',
            'communication' => 'required|integer|between:1,5',
            'cultural_fit' => 'required|integer|between:1,5',
            'problem_solving' => 'required|integer|between:1,5',
            'comments' => 'nullable|string|max:1000',
        ]);

        $validated['interviewer_id'] = auth()->user()->id;
        $validated['interview_id'] = $interview->id;

        InterviewScore::updateOrCreate(
            ['interview_id' => $interview->id, 'interviewer_id' => auth()->user()->id],
            $validated
        );

        return redirect()->back()->with('success', 'Interview score saved successfully!');
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
