<?php

namespace App\Http\Controllers;

use App\Models\JobRequisition;
use App\Models\JobApplication;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request) 
    {
        // Get filter parameters
        $dateRange = $request->get('date_range', 'last_30_days');
        $departmentId = $request->get('department_id');
        $jobStatus = $request->get('job_status');
        
        // Calculate date range
        $dateFilter = $this->getDateRange($dateRange);
        
        $data = [
            // KPI Calculations
            'totalApplications' => $this->getTotalApplications($dateFilter, $departmentId),
            'averageTimeToHire' => $this->calculateAverageTimeToHire($dateFilter, $departmentId),
            'conversionRate' => $this->calculateConversionRate($dateFilter, $departmentId),
            'activeRecruiters' => User::whereIn('role_id', [1, 3, 4])->count(),
            'sourceEffectiveness' => $this->calculateSourceEffectiveness($dateFilter),
            
            // Funnel Analysis
            'funnelData' => $this->getFunnelAnalysis($dateFilter, $departmentId),
            
            // Department Performance
            'departmentStats' => $this->getDepartmentPerformance($dateFilter),
            
            // Top Performing Jobs
            'topPerformingJobs' => $this->getTopPerformingJobs($dateFilter, $departmentId),
            
            // Trends Data
            'applicationTrends' => $this->getApplicationTrends($dateFilter),
            'sourcesData' => $this->getApplicationSources($dateFilter),
            
            // Additional metrics
            'recentApplications' => $this->getRecentApplications(10),
            'filters' => [
                'date_range' => $dateRange,
                'department_id' => $departmentId,
                'job_status' => $jobStatus
            ],
            'departments' => Department::all()
        ];
        
        return view('reports.index', $data);
    }

    private function getDateRange($range)
    {
        switch ($range) {
            case 'last_7_days':
                return ['start' => Carbon::now()->subDays(7), 'end' => Carbon::now()];
            case 'last_30_days':
                return ['start' => Carbon::now()->subDays(30), 'end' => Carbon::now()];
            case 'last_90_days':
                return ['start' => Carbon::now()->subDays(90), 'end' => Carbon::now()];
            case 'last_6_months':
                return ['start' => Carbon::now()->subMonths(6), 'end' => Carbon::now()];
            case 'last_year':
                return ['start' => Carbon::now()->subYear(), 'end' => Carbon::now()];
            default:
                return ['start' => Carbon::now()->subDays(30), 'end' => Carbon::now()];
        }
    }

    private function getTotalApplications($dateFilter, $departmentId = null)
    {
        $query = JobApplication::whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        
        if ($departmentId) {
            $query->whereHas('jobRequisition', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        return $query->count();
    }

    private function calculateAverageTimeToHire($dateFilter, $departmentId = null)
    {
        $query = JobApplication::where('status', 'hired')
            ->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']])
            ->whereNotNull('updated_at');
        
        if ($departmentId) {
            $query->whereHas('jobRequisition', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $applications = $query->get();
        
        if ($applications->isEmpty()) {
            return 0;
        }
        
        $totalDays = $applications->sum(function($application) {
            return $application->created_at->diffInDays($application->updated_at);
        });
        
        return round($totalDays / $applications->count());
    }

    private function calculateConversionRate($dateFilter, $departmentId = null)
    {
        $totalApplications = $this->getTotalApplications($dateFilter, $departmentId);
        
        if ($totalApplications == 0) {
            return 0;
        }
        
        $query = JobApplication::where('status', 'hired')
            ->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        
        if ($departmentId) {
            $query->whereHas('jobRequisition', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $hiredCount = $query->count();
        
        return round(($hiredCount / $totalApplications) * 100, 1);
    }

    private function calculateSourceEffectiveness($dateFilter)
    {
        // This is a placeholder - you'd need to track application sources
        // For now, return a sample percentage
        return 68;
    }

    private function getFunnelAnalysis($dateFilter, $departmentId = null)
    {
        $baseQuery = JobApplication::whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        
        if ($departmentId) {
            $baseQuery->whereHas('jobRequisition', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $total = $baseQuery->count();
        
        return [
            [
                'stage' => 'Applications Received',
                'count' => $total,
                'percentage' => 100
            ],
            [
                'stage' => 'Initial Screening',
                'count' => (clone $baseQuery)->where('status', '!=', 'submitted')->count(),
                'percentage' => $total > 0 ? round(((clone $baseQuery)->where('status', '!=', 'submitted')->count() / $total) * 100) : 0
            ],
            [
                'stage' => 'Shortlisted',
                'count' => (clone $baseQuery)->where('status', 'shortlisted')->count(),
                'percentage' => $total > 0 ? round(((clone $baseQuery)->where('status', 'shortlisted')->count() / $total) * 100) : 0
            ],
            [
                'stage' => 'Offer Extended',
                'count' => (clone $baseQuery)->where('status', 'offer sent')->count(),
                'percentage' => $total > 0 ? round(((clone $baseQuery)->where('status', 'offer sent')->count() / $total) * 100) : 0
            ],
            [
                'stage' => 'Hired',
                'count' => (clone $baseQuery)->where('status', 'hired')->count(),
                'percentage' => $total > 0 ? round(((clone $baseQuery)->where('status', 'hired')->count() / $total) * 100) : 0
            ]
        ];
    }

    private function getDepartmentPerformance($dateFilter)
    {
        return Department::with(['jobRequisitions' => function($query) use ($dateFilter) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }, 'jobRequisitions.applications' => function($query) use ($dateFilter) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }])
        ->get()
        ->map(function($dept) {
            $applications = $dept->jobRequisitions->flatMap->applications;
            $hiredApplications = $applications->where('status', 'hired');
            
            return [
                'name' => $dept->name,
                'open_positions' => $dept->jobRequisitions->where('job_status', 'active')->count(),
                'total_applications' => $applications->count(),
                'applications_per_position' => $dept->jobRequisitions->count() > 0 ? 
                    round($applications->count() / $dept->jobRequisitions->count(), 1) : 0,
                'avg_time_to_hire' => $this->calculateAvgTimeToHire($hiredApplications),
                'hire_rate' => $this->calculateHireRate($applications),
                'budget_utilization' => rand(45, 90) // Placeholder - implement based on your budget tracking
            ];
        });
    }

    private function calculateHireRate($applications)
    {
        if ($applications->count() == 0) {
            return 0;
        }
        
        $hiredCount = $applications->where('status', 'hired')->count();
        return round(($hiredCount / $applications->count()) * 100, 1);
    }

    private function calculateAvgTimeToHire($applications)
    {
        $hiredApplications = $applications->where('status', 'hired');
        
        if ($hiredApplications->isEmpty()) {
            return 0;
        }
        
        $totalDays = $hiredApplications->sum(function($application) {
            return $application->created_at->diffInDays($application->updated_at);
        });
        
        return round($totalDays / $hiredApplications->count()) . ' days';
    }

    private function getTopPerformingJobs($dateFilter, $departmentId = null)
    {
        $query = JobRequisition::with(['applications' => function($q) use ($dateFilter) {
            $q->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }, 'department'])
        ->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        return $query->get()
            ->map(function($job) {
                $applications = $job->applications;
                $hiredCount = $applications->where('status', 'hired')->count();
                
                return [
                    'title' => $job->title,
                    'department' => $job->department->name ?? 'N/A',
                    'applications_count' => $applications->count(),
                    'conversion_rate' => $applications->count() > 0 ? 
                        round(($hiredCount / $applications->count()) * 100, 1) : 0,
                    'status' => $job->job_status
                ];
            })
            ->sortByDesc('applications_count')
            ->take(5)
            ->values();
    }

    private function getApplicationTrends($dateFilter)
    {
        // Get monthly data for the last 6 months
        $trends = [];
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        
        for ($i = 0; $i < 6; $i++) {
            $monthStart = $startDate->copy()->addMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $applications = JobApplication::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $hires = JobApplication::where('status', 'hired')
                ->whereBetween('created_at', [$monthStart, $monthEnd])->count();
            
            $trends[] = [
                'month' => $monthStart->format('M'),
                'applications' => $applications,
                'hires' => $hires
            ];
        }
        
        return $trends;
    }

    private function getApplicationSources($dateFilter)
    {
        // Placeholder data - implement based on your source tracking
        return [
            ['name' => 'Company Website', 'count' => 89, 'percentage' => 36],
            ['name' => 'LinkedIn', 'count' => 67, 'percentage' => 27],
            ['name' => 'Indeed', 'count' => 45, 'percentage' => 18],
            ['name' => 'Employee Referral', 'count' => 28, 'percentage' => 11],
            ['name' => 'Job Boards', 'count' => 16, 'percentage' => 8]
        ];
    }

    private function getRecentApplications($limit = 10)
    {
        return JobApplication::with(['user', 'jobRequisition'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // API endpoint for AJAX filtering
    public function getData(Request $request)
    {
        $dateRange = $request->get('date_range', 'last_30_days');
        $departmentId = $request->get('department_id');
        
        $dateFilter = $this->getDateRange($dateRange);
        
        return response()->json([
            'totalApplications' => $this->getTotalApplications($dateFilter, $departmentId),
            'conversionRate' => $this->calculateConversionRate($dateFilter, $departmentId),
            'funnelData' => $this->getFunnelAnalysis($dateFilter, $departmentId),
            'departmentStats' => $this->getDepartmentPerformance($dateFilter),
            'topPerformingJobs' => $this->getTopPerformingJobs($dateFilter, $departmentId)
        ]);
    }

    // Export functionality
    public function exportPdf(Request $request)
    {
        // Implement PDF export using libraries like DomPDF or similar
        // This would generate a PDF version of the reports
        
        return response()->json(['message' => 'PDF export functionality to be implemented']);
    }

    public function exportCsv(Request $request)
    {
        // Implement CSV export
        $data = $this->getDepartmentPerformance($this->getDateRange('last_30_days'));
        
        return response()->json(['message' => 'CSV export functionality to be implemented']);
    }
}