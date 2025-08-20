@extends('layouts.app')
@section('title', $jobRequisition->title )

@section('content')
<div class="container">
    <div class="page-inner">
        
        <!-- Header Section -->
        <div class="card mb-4 border-0 bg-light">
            <div class="card-body p-4">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('job-requisitions.index') }}" class="text-decoration-none">
                                <i class="fas fa-briefcase me-1"></i>Jobs
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Job Details</li>
                    </ol>
                </nav>
                @include('partials.alerts')

                
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="fw-bold mb-2">{{ $jobRequisition->title ?? 'Untitled Position' }}</h2>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-building text-primary me-2"></i>
                            <span class="text-muted me-3">{{ $jobRequisition->department->name ?? 'Department' }}</span>
                            @if($jobRequisition->reference_number)
                                <span class="badge bg-secondary">ID: {{ $jobRequisition->reference_number }}</span>
                            @endif
                        </div>
                        <span class="badge bg-{{ $jobRequisition->approval_status === 'approved' ? 'success' : 'warning' }} px-3 py-2">
                            {{ $jobRequisition->approval_status === 'approved' ? 'Now Hiring' : 'Under Review' }}
                        </span>
                    </div>
                    <div class="col-lg-4 text-end">
                        @if(auth()->user()->isApplicant())
                        <a href="{{ route('job-applications.create', ['job_requisition' => $jobRequisition->id]) }}" 
                           class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </a>
                        @endif

                        <a href="{{ route('job-requisitions.download-pdf', $jobRequisition->id) }}" 
                            class="btn btn-outline-primary w-100 mb-3">
                             <i class="fas fa-download me-2"></i>Download Job Details (PDF)
                         </a>
                         
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body py-3">
                        <i class="fas fa-users text-primary fs-4 mb-2"></i>
                        <h5 class="fw-bold mb-1">{{ $jobRequisition->vacancies ?? 1 }}</h5>
                        <small class="text-muted">Positions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body py-3">
                        <i class="fas fa-star text-warning fs-4 mb-2"></i>
                        <h5 class="fw-bold mb-1">{{ $jobRequisition->min_experience ?? 0 }}+</h5>
                        <small class="text-muted">Years Exp.</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body py-3">
                        <i class="fas fa-clock text-info fs-4 mb-2"></i>
                        <h5 class="fw-bold mb-1">{{ ucfirst(substr($jobRequisition->employment_type ?? 'Full-time', 0, 10)) }}</h5>
                        <small class="text-muted">Type</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body py-3">
                        <i class="fas fa-map-marker-alt text-success fs-4 mb-2"></i>
                        <h5 class="fw-bold mb-1">{{ Str::limit($jobRequisition->location ?? 'Remote', 10) }}</h5>
                        <small class="text-muted">Location</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                
                <!-- Job Description -->
                @if($jobRequisition->description)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="fw-bold text-dark">
                            <i class="fas fa-file-text text-primary me-2"></i>About This Role
                        </h5>
                    </div>
                    <div class="card-body pt-3">
                        <div class="content-text">
                            {!! $jobRequisition->description !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Requirements -->
                @if($jobRequisition->requirements)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="fw-bold text-dark">
                            <i class="fas fa-check-circle text-success me-2"></i>Requirements
                        </h5>
                    </div>
                    <div class="card-body pt-3">
                        <div class="content-text">
                            {!! $jobRequisition->requirements !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Skills -->
                @if($jobRequisition->skills && $jobRequisition->skills->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="fw-bold text-dark">
                            <i class="fas fa-cogs text-info me-2"></i>Required Skills
                        </h5>
                    </div>
                    <div class="card-body pt-3">
                        @foreach($jobRequisition->skills as $skill)
                        <span class="badge bg-primary text-white me-2 mb-2 px-3 py-2">{{ $skill->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                
                <!-- Application Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        @php
                            $hasApplied = auth()->check() && $jobRequisition->applications()->where('user_id', auth()->id())->exists();
                        @endphp
                        
                        @if($hasApplied)
                            <div class="text-center">
                                <i class="fas fa-check-circle text-success fs-1 mb-3"></i>
                                <h5 class="text-success fw-bold mb-2">Application Submitted!</h5>
                                <p class="text-muted">We've received your application and will be in touch soon.</p>
                            </div>
                        @elseif((!auth()->check() || (method_exists(auth()->user(), 'isApplicant') && auth()->user()->isApplicant())))
                            <div class="text-center mb-3">
                                <i class="fas fa-paper-plane text-primary fs-1 mb-3"></i>
                                <h5 class="fw-bold mb-2">Ready to Join Us?</h5>
                                <p class="text-muted mb-3">Take the next step in your career journey.</p>
                            </div>
                            <a href="{{ route('job-applications.create', ['job_requisition' => $jobRequisition->id]) }}" 
                               class="btn btn-primary w-100 py-3 mb-3">
                                <i class="fas fa-paper-plane me-2"></i>Submit Application
                            </a>
                        @endif
                        
                        <a href="{{ route('job-requisitions.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Browse More Jobs
                        </a>
                    </div>
                </div>

                <!-- Job Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-info-circle me-2"></i>Job Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4">
                                <small class="text-muted">Education</small>
                            </div>
                            <div class="col-8">
                                <span class="fw-semibold">{{ $jobRequisition->education_level ?? 'Not specified' }}</span>
                            </div>
                        </div>
                        
                        <!-- Areas of Study Section -->
                        @if($jobRequisition->required_areas_of_study && !empty($jobRequisition->required_areas_of_study))
                        <div class="row mb-3">
                            <div class="col-4">
                                <small class="text-muted">Areas of Study</small>
                            </div>
                            <div class="col-8">
                                @foreach($jobRequisition->required_areas_of_study as $area)
                                    <span class="badge bg-light text-dark me-1 mb-1">
                                        {{ is_array($area) ? ($area['name'] ?? $area['title'] ?? 'Unknown') : $area }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <small class="text-muted">Posted</small>
                            </div>
                            <div class="col-8">
                                <span class="fw-semibold">{{ $jobRequisition->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                        
                        @if($jobRequisition->application_deadline)
                        <div class="row">
                            <div class="col-4">
                                <small class="text-muted">Deadline</small>
                            </div>
                            <div class="col-8">
                                <span class="fw-semibold text-danger">{{ $jobRequisition->application_deadline->format('M j, Y') }}</span>
                                <br><small class="text-muted">{{ $jobRequisition->application_deadline->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Share Job -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-share-alt text-primary me-2"></i>Share This Job
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Help us find the right candidate</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary btn-sm" 
                                    onclick="navigator.clipboard.writeText(window.location.href); this.innerHTML='<i class=\'fas fa-check me-1\'></i>Link Copied!'; setTimeout(() => this.innerHTML='<i class=\'fas fa-link me-1\'></i>Copy Link', 2000);">
                                <i class="fas fa-link me-1"></i>Copy Link
                            </button>
                            <div class="row g-2">
                                <div class="col-4">
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->fullUrl()) }}" 
                                       target="_blank" class="btn btn-primary btn-sm w-100">
                                        <i class="fab fa-linkedin me-1"></i>LinkedIn
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($jobRequisition->title) }}" 
                                       target="_blank" class="btn btn-info btn-sm w-100">
                                        <i class="fab fa-twitter me-1"></i>Twitter
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" 
                                       target="_blank" class="btn btn-primary btn-sm w-100" style="background-color:#3b5998; border-color:#3b5998;">
                                        <i class="fab fa-facebook-f me-1"></i>Facebook
                                    </a>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.content-text {
    line-height: 1.6;
    color: #495057;
}

.content-text p {
    margin-bottom: 1rem;
}

.content-text ul {
    padding-left: 1.25rem;
}

.content-text li {
    margin-bottom: 0.5rem;
}

.badge {
    font-size: 0.875rem;
    font-weight: 500;
}

.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

.fs-1 {
    font-size: 3rem !important;
}

.fs-4 {
    font-size: 1.5rem !important;
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .fs-1 {
        font-size: 2.5rem !important;
    }
}
</style>
@endsection