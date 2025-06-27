<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\JobApplication;
use App\Models\Interview;

use Carbon\Carbon;  

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        // Apply auth middleware except for the home/landing page
        $this->middleware('auth')->except(['home']);
    }

    // Public landing page
    public function home()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $jobs = JobRequisition::where('approval_status', 'approved')
            ->where('job_status', 'active')
            ->latest()
            ->take(6)
            ->get();

        $departments = Department::all();

        return view('welcome', compact('jobs', 'departments'));
    }

    // Authenticated user dashboard
    public function dashboard()
    {
        $user = auth()->user();

        $openPositionsCount = JobRequisition::where('job_status', 'active')->count();

        $applicationsThisMonth = JobApplication::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $interviewsThisWeek = Interview::whereBetween('interview_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->count();

        $recentJobs = JobRequisition::with('department')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $data = compact(
            'openPositionsCount',
            'applicationsThisMonth', 
            'interviewsThisWeek',
            'recentJobs'
        );

        if ($user->isHRAdmin() || $user->isManager()) {
            $recentApplications = JobApplication::with(['user', 'jobRequisition'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $pipelineStats = JobApplication::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $data = array_merge($data, compact('recentApplications', 'pipelineStats'));
        } else {
            $userApplications = JobApplication::with(['jobRequisition', 'jobRequisition.department'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $data = array_merge($data, compact('userApplications'));
        }

        return view('home', $data);
    }
}
