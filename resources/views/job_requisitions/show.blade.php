@extends('layouts.app')
@section('title', $jobRequisition->title )

@section('content')
<div class="container">
    <div class="page-inner">
        
        <!-- Enhanced Breadcrumb -->
        <div class="page-header">
            <h3 class="fw-bold mb-3">{{ $jobRequisition->title ?? 'Job Details' }}</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('job-requisitions.index') }}">Jobs</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <span>Job Details</span>
                </li>
            </ul>
        </div>

        @include('partials.alerts')

            <div class="card card-round">
            <div class="card-body py-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-title rounded bg-primary">
                                    <i class="fas fa-briefcase text-white"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h1 class="h3 fw-bold mb-2">{{ $jobRequisition->title ?? 'Untitled Position' }}</h1>
                                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                    <span class="text-muted">
                                        <i class="fas fa-building me-1"></i>{{ $jobRequisition->department->name ?? 'Department' }}
                                    </span>
                                    @if($jobRequisition->reference_number)
                                    <span class="text-muted">
                                        <i class="fas fa-tag me-1"></i>{{ $jobRequisition->reference_number }}
                                    </span>
                                    @endif
                                </div>
                                
                                <!-- Status Badges -->
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge badge-{{ $jobRequisition->approval_status === 'approved' ? 'success' : 'warning' }}">
                                        <i class="fas fa-{{ $jobRequisition->approval_status === 'approved' ? 'check-circle' : 'clock' }} me-1"></i>
                                        {{ $jobRequisition->approval_status === 'approved' ? 'Now Hiring' : 'Under Review' }}
                                    </span>
                                    @if($jobRequisition->job_status === 'closed')
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle me-1"></i>Applications Closed
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        @php
                            $isClosed = $jobRequisition->job_status === 'closed';
                            $isGuest = !auth()->check();
                            $isApplicant = auth()->check() && method_exists(auth()->user(), 'isApplicant') && auth()->user()->isApplicant();
                        @endphp
                        
                        <div class="d-flex flex-column flex-lg-row gap-2 justify-content-lg-end">
                            @if(!$isClosed)
                                @if($isGuest)
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-round">
                                        <i class="fas fa-sign-in-alt me-1"></i>Login to Apply
                                    </a>
                                @elseif($isApplicant)
                                    <a href="{{ route('job-applications.create', ['job_requisition' => $jobRequisition->id]) }}" 
                                       class="btn btn-primary btn-round">
                                        <i class="fas fa-paper-plane me-1"></i>Apply Now
                                    </a>
                                @endif
                            @endif
                            
                            <a href="{{ route('job-requisitions.download-pdf', $jobRequisition->id) }}" 
                               class="btn btn-secondary btn-round">
                                <i class="fas fa-download me-1"></i>Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    

        <!-- Job Stats Row -->
        <div class="row">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-primary bubble-shadow-small">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Positions</p>
                                    <h4 class="card-title">{{ $jobRequisition->vacancies ?? 1 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-warning bubble-shadow-small">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Experience</p>
                                    <h4 class="card-title">{{ $jobRequisition->min_experience ?? 0 }}+ Years</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-info bubble-shadow-small">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Type</p>
                                    <h4 class="card-title">{{ ucfirst(Str::limit($jobRequisition->employment_type ?? 'Full-time', 8)) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-success bubble-shadow-small">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Location</p>
                                    <h4 class="card-title">{{ Str::limit($jobRequisition->location ?? 'Remote', 8) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                
                <!-- Job Description -->
                @if($jobRequisition->description)
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="card-title">
                                <i class="fas fa-file-text text-primary me-2"></i>About This Role
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="content-text">
                            {!! $jobRequisition->description !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Requirements -->
                @if($jobRequisition->requirements)
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="card-title">
                                <i class="fas fa-check-circle text-success me-2"></i>Requirements
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="content-text">
                            {!! $jobRequisition->requirements !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Skills -->
                @if($jobRequisition->skills && $jobRequisition->skills->count() > 0)
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="card-title">
                                <i class="fas fa-cogs text-info me-2"></i>Required Skills
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($jobRequisition->skills as $skill)
                            <span class="badge badge-primary badge-round">{{ $skill->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                
                <!-- Application Status Card -->
                <div class="card card-round">
                    <div class="card-header card-header-{{ $isClosed ? 'danger' : ($hasApplied ?? false ? 'success' : 'primary') }}">
                        <div class="card-title text-white">
                            <i class="fas fa-paper-plane me-2"></i>Application Status
                        </div>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $hasApplied = auth()->check() && $jobRequisition->applications()->where('user_id', auth()->id())->exists();
                        @endphp

                        @if($isClosed)
                            <div class="avatar avatar-xxl mb-3">
                                <span class="avatar-title rounded-circle bg-danger">
                                    <i class="fas fa-times fa-2x text-white"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold text-danger mb-2">Applications Closed</h5>
                            <p class="text-muted">Unfortunately, this job is no longer accepting applications.</p>
                            
                        @elseif($hasApplied)
                            <div class="avatar avatar-xxl mb-3">
                                <span class="avatar-title rounded-circle bg-success">
                                    <i class="fas fa-check fa-2x text-white"></i>
                                </span>
                            </div>
                            <h5 class="text-success fw-bold mb-2">Application Submitted!</h5>
                            <p class="text-muted">We've received your application and will be in touch soon.</p>
                            
                        @elseif($isGuest)
                            <div class="avatar avatar-xxl mb-3">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="fas fa-user-plus fa-2x text-white"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">Interested in This Role?</h5>
                            <p class="text-muted mb-3">Join our platform to apply for this position and discover more opportunities.</p>
                            
                            <div class="d-grid gap-2">
                                <a href="{{ route('login') }}" class="btn btn-primary btn-round">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Apply
                                </a>
                                
                                <div class="separator-dashed my-3">
                                    <span>New to our platform?</span>
                                </div>
                                
                                <a href="{{ route('register') }}" class="btn btn-secondary btn-round">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </a>
                            </div>
                            
                        @elseif($isApplicant)
                            <div class="avatar avatar-xxl mb-3">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="fas fa-paper-plane fa-2x text-white"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">Ready to Join Us?</h5>
                            <p class="text-muted mb-3">Take the next step in your career journey.</p>
                            <a href="{{ route('job-applications.create', ['job_requisition' => $jobRequisition->id]) }}" 
                               class="btn btn-primary btn-round w-100 mb-3">
                                <i class="fas fa-paper-plane me-2"></i>Submit Application
                            </a>
                        @endif

                        <a href="{{ route('job-requisitions.index') }}" class="btn btn-outline-secondary btn-round w-100">
                            <i class="fas fa-arrow-left me-2"></i>Browse More Jobs
                        </a>
                    </div>
                </div>

                <!-- Job Information Card -->
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-info-circle me-2"></i>Job Information
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="info-user">
                            <div class="row mb-3">
                                <div class="col-5">
                                    <small class="text-muted fw-bold">Education</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold">{{ $jobRequisition->education_level ?? 'Not specified' }}</span>
                                </div>
                            </div>

                            @if($jobRequisition->required_areas_of_study && !empty($jobRequisition->required_areas_of_study))
                            <div class="row mb-3">
                                <div class="col-12">
                                    <small class="text-muted fw-bold d-block mb-2">Areas of Study</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($jobRequisition->required_areas_of_study as $area)
                                            <span class="badge badge-secondary">
                                                {{ is_array($area) ? ($area['name'] ?? $area['title'] ?? 'Unknown') : $area }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-5">
                                    <small class="text-muted fw-bold">Posted</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold">{{ $jobRequisition->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>

                            @if($jobRequisition->application_deadline)
                            <div class="row">
                                <div class="col-5">
                                    <small class="text-muted fw-bold">Deadline</small>
                                </div>
                                <div class="col-7">
                                    <span class="fw-semibold text-danger">{{ $jobRequisition->application_deadline->format('M j, Y') }}</span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $jobRequisition->application_deadline->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Share Job Card -->
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-share-alt me-2"></i>Share This Job
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Help us find the right candidate</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary btn-round" 
                                    onclick="navigator.clipboard.writeText(window.location.href); 
                                             this.innerHTML='<i class=\'fas fa-check me-1\'></i>Link Copied!'; 
                                             this.classList.remove('btn-outline-secondary'); 
                                             this.classList.add('btn-success');
                                             setTimeout(() => {
                                                 this.innerHTML='<i class=\'fas fa-link me-1\'></i>Copy Link';
                                                 this.classList.remove('btn-success');
                                                 this.classList.add('btn-outline-secondary');
                                             }, 2000);">
                                <i class="fas fa-link me-1"></i>Copy Link
                            </button>
                            
                            <div class="row g-2 mt-1">
                                <div class="col-4">
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->fullUrl()) }}" 
                                       target="_blank" class="btn btn-primary btn-sm w-100 btn-round" 
                                       data-bs-toggle="tooltip" title="Share on LinkedIn">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($jobRequisition->title) }}" 
                                       target="_blank" class="btn btn-info btn-sm w-100 btn-round"
                                       data-bs-toggle="tooltip" title="Share on Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" 
                                       target="_blank" class="btn btn-secondary btn-sm w-100 btn-round"
                                       data-bs-toggle="tooltip" title="Share on Facebook">
                                        <i class="fab fa-facebook-f"></i>
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
/* Enhanced Kaiadmin styling */
.card-header-primary {
    background: linear-gradient(135deg, #1572e8 0%, #0b5ed7 100%);
    border: none;
}

.card-header-success {
    background: linear-gradient(135deg, #31ce36 0%, #198754 100%);
    border: none;
}

.card-header-danger {
    background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%);
    border: none;
}

.separator-dashed {
    position: relative;
    text-align: center;
    color: #aaa;
    font-size: 0.875rem;
}

.separator-dashed::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: repeating-linear-gradient(
        to right,
        #ddd 0px,
        #ddd 5px,
        transparent 5px,
        transparent 10px
    );
}

.separator-dashed span {
    background: #fff;
    padding: 0 1rem;
    position: relative;
    z-index: 1;
}

.content-text {
    line-height: 1.7;
}

.content-text h1,
.content-text h2,
.content-text h3,
.content-text h4,
.content-text h5,
.content-text h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.content-text ul,
.content-text ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.content-text li {
    margin-bottom: 0.5rem;
}

.content-text p {
    margin-bottom: 1rem;
    color: #6c757d;
}

.content-text strong,
.content-text b {
    font-weight: 600;
    color: #212529;
}

/* Enhanced responsive design */
@media (max-width: 768px) {
    .card-title {
        font-size: 1.1rem;
    }
    
    .card-stats .card-title {
        font-size: 1.5rem;
    }
    
    .avatar-xxl {
        width: 4rem;
        height: 4rem;
    }
    
    .avatar-xxl .fa-2x {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .card-body {
        padding: 1rem;
    }
    
    .card-header {
        padding: 1rem;
    }
    
    .row.g-2 > .col-4 {
        margin-bottom: 0.5rem;
    }
}

/* Improved button hover effects for Kaiadmin */
.btn-round:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card-round:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}

/* Enhanced badge styling */
.badge-round {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Custom tooltip styling */
.tooltip .tooltip-inner {
    background-color: #1572e8;
    color: white;
    border-radius: 0.375rem;
}

.tooltip .tooltip-arrow::before {
    border-top-color: #1572e8;
}
</style>

<script>
// Initialize tooltips if using Bootstrap 5
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
@endsection