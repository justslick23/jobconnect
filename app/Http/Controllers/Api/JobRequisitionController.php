<?php

// File: app/Http/Controllers/Api/JobRequisitionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobRequisition;
use Illuminate\Http\Request;

class JobRequisitionController extends Controller
{
    /**
     * Get all active job requisitions for public display
     */
    public function index(Request $request)
    {
        $query = JobRequisition::with(['department', 'creator'])
            ->where('approval_status', 'approved')
            ->where('job_status', 'active')
            ->where('application_deadline', '>=', now())
            ->orderByDesc('created_at');
        
        // Optional filtering by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Optional filtering by employment type
        if ($request->has('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }
        
        // Optional filtering by location
        if ($request->has('location')) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }
        
        $jobRequisitions = $query->get();
        
        // Transform the data to include only necessary fields
        $transformedData = $jobRequisitions->map(function ($job) {
            return [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'slug' => $job->slug,
                'reference_number' => $job->reference_number,
                'title' => $job->title,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'required_areas_of_study' => $job->required_areas_of_study,
                'vacancies' => $job->vacancies,
                'location' => $job->location,
                'employment_type' => $job->employment_type,
                'min_experience' => $job->min_experience,
                'education_level' => $job->education_level,
                'application_deadline' => $job->application_deadline->toISOString(),
                'job_status' => $job->job_status,
                'approval_status' => $job->approval_status,
                'created_at' => $job->created_at->toISOString(),
                'department' => [
                    'id' => $job->department->id ?? null,
                    'name' => $job->department->name ?? 'N/A'
                ],
                'creator' => [
                    'id' => $job->creator->id ?? null,
                    'name' => $job->creator->name ?? 'N/A'
                ]
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $transformedData,
            'total' => $transformedData->count(),
            'message' => 'Job requisitions retrieved successfully'
        ]);
    }
    
    /**
     * Get a specific job requisition by slug-uuid
     */
    public function show($slugUuid)
    {
        // Extract UUID from slug-uuid format
        $parts = explode('-', $slugUuid);
        $uuid = array_pop($parts);
        
        $jobRequisition = JobRequisition::with(['department', 'creator', 'skills'])
            ->where('uuid', $uuid)
            ->where('approval_status', 'approved')
            ->where('job_status', 'active')
            ->first();
        
        if (!$jobRequisition) {
            return response()->json([
                'success' => false,
                'message' => 'Job requisition not found or not available'
            ], 404);
        }
        
        // Check if application deadline has passed
        if ($jobRequisition->application_deadline < now()) {
            return response()->json([
                'success' => false,
                'message' => 'This job requisition is no longer accepting applications'
            ], 410); // Gone
        }
        
        // Transform the data
        $transformedData = [
            'id' => $jobRequisition->id,
            'uuid' => $jobRequisition->uuid,
            'slug' => $jobRequisition->slug,
            'reference_number' => $jobRequisition->reference_number,
            'title' => $jobRequisition->title,
            'description' => $jobRequisition->description,
            'requirements' => $jobRequisition->requirements,
            'required_areas_of_study' => $jobRequisition->required_areas_of_study,
            'vacancies' => $jobRequisition->vacancies,
            'location' => $jobRequisition->location,
            'employment_type' => $jobRequisition->employment_type,
            'min_experience' => $jobRequisition->min_experience,
            'education_level' => $jobRequisition->education_level,
            'application_deadline' => $jobRequisition->application_deadline->toISOString(),
            'job_status' => $jobRequisition->job_status,
            'approval_status' => $jobRequisition->approval_status,
            'created_at' => $jobRequisition->created_at->toISOString(),
            'department' => [
                'id' => $jobRequisition->department->id ?? null,
                'name' => $jobRequisition->department->name ?? 'N/A'
            ],
            'creator' => [
                'id' => $jobRequisition->creator->id ?? null,
                'name' => $jobRequisition->creator->name ?? 'N/A'
            ],
            'skills' => $jobRequisition->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name
                ];
            }),
            'total_applications' => $jobRequisition->applications()->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $transformedData,
            'message' => 'Job requisition retrieved successfully'
        ]);
    }
    
    /**
     * Get departments with active job requisitions
     */
    public function departments()
    {
        $departments = JobRequisition::with('department')
            ->where('approval_status', 'approved')
            ->where('job_status', 'active')
            ->where('application_deadline', '>=', now())
            ->get()
            ->pluck('department')
            ->filter()
            ->unique('id')
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $departments,
            'total' => $departments->count(),
            'message' => 'Departments retrieved successfully'
        ]);
    }
    
    /**
     * Get statistics about active job requisitions
     */
    public function statistics()
    {
        $activeJobs = JobRequisition::where('approval_status', 'approved')
            ->where('job_status', 'active')
            ->where('application_deadline', '>=', now());
        
        $stats = [
            'total_active_jobs' => $activeJobs->count(),
            'total_vacancies' => $activeJobs->sum('vacancies'),
            'by_employment_type' => $activeJobs->groupBy('employment_type')
                ->map(function ($jobs) {
                    return $jobs->count();
                }),
            'by_department' => $activeJobs->with('department')
                ->get()
                ->groupBy('department.name')
                ->map(function ($jobs) {
                    return $jobs->count();
                }),
            'expiring_soon' => $activeJobs->where('application_deadline', '<=', now()->addDays(7))
                ->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully'
        ]);
    }
}