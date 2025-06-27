@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">

        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h4 class="page-title">Welcome back, {{ auth()->user()->name }}! 👋</h4>
                <p class="text-muted">Here's what's happening with your job board today</p>
            </div>
            <div class="badge bg-primary text-white py-2 px-3">
                <i class="fas fa-user-circle me-2"></i>
                {{ auth()->user()->isHrAdmin() ? 'HR Admin' : (auth()->user()->isManager() ? 'Manager' : 'Job Seeker') }}
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            @if(auth()->user()->isHRAdmin() || auth()->user()->isManager())
                <div class="col-lg-3 col-md-6">
                    <div class="card card-stats card-primary">
                        <div class="card-body text-center">
                            <div class="stats-icon text-primary mb-2">
                                <i class="fas fa-briefcase fa-2x"></i>
                            </div>
                            <div class="stats-number fs-3">{{ $openPositionsCount ?? 0 }}</div>
                            <div class="stats-label text-muted">Active Positions</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-stats card-success">
                        <div class="card-body text-center">
                            <div class="stats-icon text-success mb-2">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                            <div class="stats-number fs-3">{{ $applicationsThisMonth ?? 0 }}</div>
                            <div class="stats-label text-muted">Applications This Month</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-stats card-warning">
                        <div class="card-body text-center">
                            <div class="stats-icon text-warning mb-2">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                            <div class="stats-number fs-3">{{ $interviewsThisWeek ?? 0 }}</div>
                            <div class="stats-label text-muted">Interviews This Week</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-stats card-info">
                        <div class="card-body text-center">
                            <div class="stats-icon text-info mb-2">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <div class="stats-number fs-3">{{ count($pipelineStats ?? []) }}</div>
                            <div class="stats-label text-muted">Pipeline Stages</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-4 col-md-6">
                    <div class="card card-stats card-primary">
                        <div class="card-body text-center">
                            <div class="stats-icon text-primary mb-2">
                                <i class="fas fa-paper-plane fa-2x"></i>
                            </div>
                            <div class="stats-number fs-3">{{ $userApplications->count() ?? 0 }}</div>
                            <div class="stats-label text-muted">My Applications</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card card-stats card-success">
                        <div class="card-body text-center">
                            <div class="stats-icon text-success mb-2">
                                <i class="fas fa-handshake fa-2x"></i>
                            </div>
                            <div class="stats-number fs-3">{{ $userApplications->where('status', 'shortlisted')->count() ?? 0 }}</div>
                            <div class="stats-label text-muted">Shortlisted</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card card-stats card-info">
                        <div class="card-body text-center">
                            <div class="stats-icon text-info mb-2">
                                <i class="fas fa-search fa-2x"></i>
                            </div>
                            <div class="stats-number fs-3">{{ $openPositionsCount ?? 0 }}</div>
                            <div class="stats-label text-muted">Available Jobs</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="row g-4">
            @if(auth()->user()->isHRAdmin() || auth()->user()->isManager())
                <!-- Recent Job Listings -->
                <div class="col-lg-8">
                    <div class="card card-modern">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list-ul me-2"></i>
                                Recent Job Listings
                            </h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-modern table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th class="d-none d-md-table-cell">Department</th>
                                        <th>Status</th>
                                        <th class="d-none d-lg-table-cell">Applications</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentJobs ?? [] as $job)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $job->title }}</div>
                                                <small class="text-muted">📍 {{ $job->location }}</small>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <span class="badge bg-light text-dark">{{ $job->department->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($job->job_status === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($job->job_status === 'closed')
                                                    <span class="badge bg-danger">Closed</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($job->job_status) }}</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <span class="fw-bold text-primary">{{ $job->applications->count() ?? 0 }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>
                                                No job listings found<br>
                                                Start by creating your first job posting
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Applications -->
                <div class="col-lg-4">
                    <div class="card card-modern">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-tie me-2"></i>
                                Recent Applications
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @forelse($recentApplications ?? [] as $application)
                                <div class="d-flex align-items-center px-3 py-2 border-bottom">
                                    <div class="avatar rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3" style="width: 38px; height: 38px; font-weight: 700;">
                                        {{ strtoupper(substr($application->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark">{{ $application->user->name }}</div>
                                        <small class="text-muted">{{ $application->jobRequisition->title }}</small>
                                    </div>
                                    <div>
                                        @if($application->status === 'submitted')
                                            <span class="badge bg-warning text-dark">Review</span>
                                        @elseif($application->status === 'shortlisted')
                                            <span class="badge bg-info text-white">Shortlisted</span>
                                        @elseif($application->status === 'hired')
                                            <span class="badge bg-success">Hired</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-users fa-3x mb-3"></i><br>
                                    No recent applications<br>
                                    Applications will appear here
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            @else
                <!-- My Applications (Job Seeker View) -->
                <div class="col-12">
                    <div class="card card-modern">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-paper-plane me-2"></i>
                                My Applications
                            </h5>
                            <a href="{{ route('job-requisitions.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-search me-2"></i>Browse Jobs
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-modern table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th class="d-none d-md-table-cell">Department</th>
                                        <th>Status</th>
                                        <th class="d-none d-lg-table-cell">Applied On</th>
                                        <th class="d-none d-lg-table-cell">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($userApplications ?? [] as $application)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $application->jobRequisition->title }}</div>
                                                <small class="text-muted">📍 {{ $application->jobRequisition->location }}</small>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <span class="badge bg-light text-dark">{{ $application->jobRequisition->department->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($application->status === 'submitted')
                                                    <span class="badge bg-warning text-dark">Under Review</span>
                                                @elseif($application->status === 'shortlisted')
                                                    <span class="badge bg-info text-white">Shortlisted</span>
                                                @elseif($application->status === 'offer sent')
                                                    <span class="badge bg-primary text-white">Offer Sent</span>
                                                @elseif($application->status === 'hired')
                                                    <span class="badge bg-success">Hired</span>
                                                @elseif($application->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <span class="text-muted">{{ $application->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <span class="text-muted">{{ $application->updated_at->diffForHumans() }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-rocket fa-2x mb-2"></i><br>
                                                Ready to start your journey?<br>
                                                Browse available positions and submit your first application<br>
                                                <a href="{{ route('job-requisitions.index') }}" class="btn btn-primary mt-3">
                                                    <i class="fas fa-search me-2"></i>Start Applying
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
