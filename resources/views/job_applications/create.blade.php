@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            {{-- Header Section --}}
            <div class="bg-gradient-primary text-white rounded-4 p-4 mb-5 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h3 mb-2 fw-bold">Apply for Position</h1>
                        <h2 class="h4 mb-0 opacity-90">{{ $jobRequisition->title }}</h2>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-briefcase fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('job-applications.store') }}" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="job_requisition_id" value="{{ $jobRequisition->id }}">

                {{-- Profile Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <h3 class="h5 mb-0 text-dark fw-semibold">Personal Information</h3>
                            </div>
                            <a href="{{ route('applicant.profile.update') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="fas fa-edit me-1"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user-circle text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 bg-light" 
                                           value="{{ $profile->first_name ?? 'Not provided' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user-circle text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 bg-light" 
                                           value="{{ $profile->last_name ?? 'Not provided' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 bg-light" 
                                           value="{{ auth()->user()->email }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-phone text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 bg-light" 
                                           value="{{ $profile->phone ?? 'Not provided' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Skills Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-cogs text-success"></i>
                            </div>
                            <h3 class="h5 mb-0 text-dark fw-semibold">Skills & Expertise</h3>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        @if($skills->count())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($skills as $skill)
                                    <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 p-2 rounded-pill">
                                        <span class="fw-medium">{{ $skill->name }}</span>
                                        <span class="badge bg-primary ms-2 text-capitalize">{{ $skill->proficiency }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle text-warning mb-2"></i>
                                <p class="text-muted mb-0">No skills have been added to your profile yet.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Education Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-graduation-cap text-info"></i>
                            </div>
                            <h3 class="h5 mb-0 text-dark fw-semibold">Education Background</h3>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        @if($education->count())
                            <div class="timeline">
                                @foreach($education as $index => $edu)
                                    <div class="timeline-item {{ $index === 0 ? 'active' : '' }}">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="fw-semibold text-dark mb-1">{{ $edu->degree }}</h6>
                                                <small class="badge bg-light text-dark">{{ $edu->start_date }} - {{ $edu->end_date }}</small>
                                            </div>
                                            <p class="text-primary fw-medium mb-1">{{ $edu->institution }}</p>
                                            <p class="text-muted small mb-0">{{ $edu->field_of_study }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle text-warning mb-2"></i>
                                <p class="text-muted mb-0">No education records found in your profile.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Experience Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-briefcase text-warning"></i>
                            </div>
                            <h3 class="h5 mb-0 text-dark fw-semibold">Work Experience</h3>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        @if($experience->count())
                            <div class="timeline">
                                @foreach($experience as $index => $exp)
                                    <div class="timeline-item {{ $index === 0 ? 'active' : '' }}">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="fw-semibold text-dark mb-1">{{ $exp->position }}</h6>
                                                <small class="badge bg-light text-dark">{{ $exp->start_date }} - {{ $exp->end_date }}</small>
                                            </div>
                                            <p class="text-primary fw-medium mb-2">{{ $exp->company }}</p>
                                            <p class="text-muted small mb-0">{{ $exp->description }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle text-warning mb-2"></i>
                                <p class="text-muted mb-0">No work experience records found in your profile.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- References Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-users text-secondary"></i>
                            </div>
                            <h3 class="h5 mb-0 text-dark fw-semibold">Professional References</h3>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        @if($references->count())
                            <div class="row g-3">
                                @foreach($references as $ref)
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 h-100 bg-light bg-opacity-50">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-secondary bg-opacity-10 rounded-circle p-1 me-2">
                                                    <i class="fas fa-user text-secondary small"></i>
                                                </div>
                                                <h6 class="fw-semibold mb-0">{{ $ref->name }}</h6>
                                            </div>
                                            <p class="text-muted small mb-1">{{ $ref->relationship }}</p>
                                            <p class="text-primary small mb-0">{{ $ref->contact }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle text-warning mb-2"></i>
                                <p class="text-muted mb-0">No references have been added to your profile.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Qualifications Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-purple bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-certificate text-purple"></i>
                            </div>
                            <h3 class="h5 mb-0 text-dark fw-semibold">Additional Qualifications</h3>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        @if($qualifications->count())
                            <div class="list-group list-group-flush">
                                @foreach($qualifications as $qual)
                                    <div class="list-group-item border-0 px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-award text-purple me-3"></i>
                                                <span class="fw-medium">{{ $qual->title }}</span>
                                            </div>
                                            @if($qual->date_obtained)
                                                <small class="badge bg-purple bg-opacity-10 text-purple">{{ $qual->date_obtained }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle text-warning mb-2"></i>
                                <p class="text-muted mb-0">No additional qualifications have been added.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Attachments Section --}}
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-paperclip text-danger"></i>
                            </div>
                            <h3 class="h5 mb-0 text-dark fw-semibold">Supporting Documents</h3>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        @if($attachments->count())
                            <div class="list-group list-group-flush">
                                @foreach($attachments as $attachment)
                                    <div class="list-group-item border-0 px-0 py-2">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt text-danger me-3"></i>
                                            <a href="{{ route('job-applications.downloadAttachment', $attachment->id) }}" 
                                               target="_blank" rel="noopener noreferrer"
                                               class="text-decoration-none fw-medium text-dark hover-primary">
                                                {{ $attachment->type }}
                                            </a>
                                            <i class="fas fa-external-link-alt text-muted ms-2 small"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle text-warning mb-2"></i>
                                <p class="text-muted mb-0">No supporting documents have been uploaded.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Submit Section --}}
                <div class="text-center">
                    <div class="bg-gradient-success rounded-4 p-4 mb-4">
                        <h4 class="text-white mb-2">Ready to Submit Your Application?</h4>
                        <p class="text-white opacity-90 mb-0">Please review all information above before submitting.</p>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg px-5 py-3 rounded-pill shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i>
                        Confirm & Submit Application
                    </button>
                    
                    <p class="text-muted small mt-3 mb-0">
                        By submitting this application, you confirm that all information provided is accurate and complete.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.text-purple {
    color: #6f42c1 !important;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #007bff, #6c757d);
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -1.25rem;
    top: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    background: #6c757d;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #6c757d;
}

.timeline-item.active .timeline-marker {
    background: #007bff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
}

.timeline-item.active .timeline-content {
}

.hover-primary:hover {
    color: #007bff !important;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
@endsection