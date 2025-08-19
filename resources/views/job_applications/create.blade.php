@extends('layouts.app')

@section('content')
@section('title', 'Apply for ' . $job->title)

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Job Application</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="#"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Careers</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Apply</a></li>
        </ul>
    </div>
    
    @include('partials.alerts')

    <div class="row">
        <div class="col-md-12">
            {{-- Job Position Header --}}
            <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-briefcase fa-2x text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="mb-1 text-white">{{ $jobRequisition->title }}</h2>
                            <p class="mb-0 text-white-50">Complete your application by reviewing the information below</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-white text-primary fs-6 px-3 py-2">
                                <i class="fas fa-clock me-1"></i>
                                In Progress
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('job-applications.store') }}" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="job_requisition_id" value="{{ $jobRequisition->id }}">

                {{-- Application Progress Steps --}}
                <div class="card mb-4">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="step-indicator active me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="fw-medium">Personal Info</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="step-indicator active me-3">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <span class="fw-medium">Skills</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="step-indicator active me-3">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <span class="fw-medium">Education</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="step-indicator active me-3">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <span class="fw-medium">Experience</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="step-indicator me-3">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <span class="fw-medium">Submit</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        {{-- Personal Information --}}
                        <div class="card mb-4">
                            <div class="card-header bg-primary">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center text-white">
                                        <i class="fas fa-user me-2"></i>
                                        <h5 class="mb-0">Personal Information</h5>
                                    </div>
                                    <a href="{{ route('applicant.profile.update') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-edit me-1"></i> Update Profile
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">First Name</label>
                                        <div class="info-display">
                                            <i class="fas fa-user text-primary me-2"></i>
                                            <span class="fw-medium">{{ $profile->first_name ?? 'Not provided' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Last Name</label>
                                        <div class="info-display">
                                            <i class="fas fa-user text-primary me-2"></i>
                                            <span class="fw-medium">{{ $profile->last_name ?? 'Not provided' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Email Address</label>
                                        <div class="info-display">
                                            <i class="fas fa-envelope text-success me-2"></i>
                                            <span class="fw-medium">{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Phone Number</label>
                                        <div class="info-display">
                                            <i class="fas fa-phone text-info me-2"></i>
                                            <span class="fw-medium">{{ $profile->phone ?? 'Not provided' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Skills & Expertise --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-cogs text-warning me-2"></i>
                                    <h5 class="mb-0">Skills & Expertise</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($skills->count())
                                    <div class="skill-grid">
                                        @foreach($skills as $skill)
                                            <div class="skill-item">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="skill-name">{{ $skill->name }}</span>
                                                    <span class="badge badge-{{ $skill->proficiency === 'expert' ? 'success' : ($skill->proficiency === 'intermediate' ? 'warning' : 'info') }}">
                                                        {{ ucfirst($skill->proficiency) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No skills added to your profile yet</p>
                                        <a href="{{ route('applicant.profile.update') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i> Add Skills
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Education Background --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-graduation-cap text-success me-2"></i>
                                    <h5 class="mb-0">Education Background</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($education->count())
                                    <div class="timeline-modern">
                                        @foreach($education as $edu)
                                            <div class="timeline-item-modern">
                                                <div class="timeline-marker bg-success">
                                                    <i class="fas fa-graduation-cap"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0">{{ $edu->degree }}</h6>
                                                        <small class="text-muted">{{ $edu->start_date }} - {{ $edu->end_date }}</small>
                                                    </div>
                                                    <p class="text-primary mb-1">{{ $edu->institution }}</p>
                                                    <p class="text-muted small mb-0">{{ $edu->field_of_study }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No education records found</p>
                                        <a href="{{ route('applicant.profile.update') }}" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-plus me-1"></i> Add Education
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Work Experience --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-briefcase text-info me-2"></i>
                                    <h5 class="mb-0">Work Experience</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($experience->count())
                                    <div class="timeline-modern">
                                        @foreach($experience as $exp)
                                            <div class="timeline-item-modern">
                                                <div class="timeline-marker bg-info">
                                                    <i class="fas fa-briefcase"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0">{{ $exp->position }}</h6>
                                                        <small class="text-muted">{{ $exp->start_date }} - {{ $exp->end_date }}</small>
                                                    </div>
                                                    <p class="text-primary mb-1">{{ $exp->company }}</p>
                                                    <p class="text-muted small mb-0">{{ $exp->description }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No work experience records found</p>
                                        <a href="{{ route('applicant.profile.update') }}" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-plus me-1"></i> Add Experience
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        {{-- Application Summary --}}
                        <div class="card mb-4 sticky-top" style="top: 100px;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Application Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="summary-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user text-primary me-2"></i>
                                        <span class="small">Personal Info</span>
                                        <i class="fas fa-check text-success ms-auto"></i>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-cogs text-warning me-2"></i>
                                        <span class="small">Skills ({{ $skills->count() }})</span>
                                        @if($skills->count())
                                            <i class="fas fa-check text-success ms-auto"></i>
                                        @else
                                            <i class="fas fa-exclamation-triangle text-warning ms-auto"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-graduation-cap text-success me-2"></i>
                                        <span class="small">Education ({{ $education->count() }})</span>
                                        @if($education->count())
                                            <i class="fas fa-check text-success ms-auto"></i>
                                        @else
                                            <i class="fas fa-exclamation-triangle text-warning ms-auto"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-briefcase text-info me-2"></i>
                                        <span class="small">Experience ({{ $experience->count() }})</span>
                                        @if($experience->count())
                                            <i class="fas fa-check text-success ms-auto"></i>
                                        @else
                                            <i class="fas fa-exclamation-triangle text-warning ms-auto"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-users text-secondary me-2"></i>
                                        <span class="small">References ({{ $references->count() }})</span>
                                        @if($references->count())
                                            <i class="fas fa-check text-success ms-auto"></i>
                                        @else
                                            <i class="fas fa-minus text-muted ms-auto"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="summary-item border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-paperclip text-danger me-2"></i>
                                        <span class="small">Documents ({{ $attachments->count() }})</span>
                                        @if($attachments->count())
                                            <i class="fas fa-check text-success ms-auto"></i>
                                        @else
                                            <i class="fas fa-minus text-muted ms-auto"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Professional References --}}
                        @if($references->count())
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-users text-secondary me-2"></i>
                                    References
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($references as $ref)
                                    <div class="reference-item">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light me-3">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                            <div>
                                                <p class="mb-1 fw-medium">{{ $ref->name }}</p>
                                                <p class="mb-0 small text-muted">{{ $ref->relationship }}</p>
                                                <p class="mb-0 small text-primary">{{ $ref->contact }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Additional Qualifications --}}
                        @if($qualifications->count())
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-certificate text-warning me-2"></i>
                                    Qualifications
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($qualifications as $qual)
                                    <div class="qualification-item">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="fas fa-award text-warning me-2"></i>
                                                <span class="small fw-medium">{{ $qual->title }}</span>
                                            </div>
                                            @if($qual->date_obtained)
                                                <span class="badge badge-light">{{ $qual->date_obtained }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                       {{-- Supporting Documents --}}
{{-- Supporting Documents --}}
@if($attachments)
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-paperclip text-danger me-2"></i>
                Documents
            </h6>
        </div>
        <div class="card-body">
            @foreach($attachments as $type => $docs)
                <h6 class="text-muted">{{ ucfirst($type) }}</h6>

                {{-- Ensure $docs is always iterable --}}
                @php
                    $docsIterable = is_iterable($docs) ? $docs : [$docs];
                @endphp

                @foreach($docsIterable as $doc)
                    @if(is_object($doc))
                        <div class="document-item">
                            <a href="{{ route('job-applications.downloadAttachment', $doc->id) }}" 
                               class="d-flex align-items-center text-decoration-none">
                                <i class="fas fa-file-pdf text-danger me-3"></i>
                                <span class="flex-grow-1">{{ $doc->type }}</span>
                                <i class="fas fa-external-link-alt text-muted"></i>
                            </a>
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
@endif


                {{-- Submit Application --}}
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-paper-plane fa-3x text-success mb-3"></i>
                            <h4 class="mb-2">Ready to Submit Your Application?</h4>
                            <p class="text-muted">Please review all information above before submitting your application.</p>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg px-5 py-3">
                            <i class="fas fa-paper-plane me-2"></i>
                            Submit Application
                        </button>
                        
                        <p class="text-muted small mt-4 mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            By submitting, you confirm all information is accurate and complete.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Step Indicator */
.step-indicator {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.step-indicator.active {
    background: #1572E8;
    color: white;
}

/* Info Display */
.info-display {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    margin-top: 5px;
}

/* Skill Grid */
.skill-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
}

.skill-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 16px;
    transition: all 0.3s ease;
}

.skill-item:hover {
    border-color: #1572E8;
    background: #f0f7ff;
}

.skill-name {
    font-weight: 500;
    color: #495057;
}

/* Modern Timeline */
.timeline-modern {
    position: relative;
    padding-left: 30px;
}

.timeline-modern:before {
    content: '';
    position: absolute;
    left: 12px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #1572E8, #e9ecef);
}

.timeline-item-modern {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -18px;
    top: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 10px;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    margin-left: 15px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

/* Summary Items */
.summary-item {
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
}

/* Reference Items */
.reference-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.reference-item:last-child {
    border-bottom: none;
}

/* Qualification Items */
.qualification-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.qualification-item:last-child {
    border-bottom: none;
}

/* Document Items */
.document-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.document-item:last-child {
    border-bottom: none;
}

.document-item:hover {
    background: #f8f9fa;
    margin: 0 -15px;
    padding-left: 15px;
    padding-right: 15px;
    border-radius: 4px;
}

/* Avatar */
.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

/* Card Hover Effects */
.card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .skill-grid {
        grid-template-columns: 1fr;
    }
    
    .timeline-content {
        margin-left: 10px;
    }
    
    .sticky-top {
        position: relative !important;
        top: auto !important;
    }
}
</style>
@endsection