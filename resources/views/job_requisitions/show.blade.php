@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h3">{{ $jobRequisition->title ?? 'Untitled Position' }}</h1>
                    <div class="text-muted small">
                        <span class="me-3"><i class="fas fa-building"></i> {{ $jobRequisition->department->name ?? 'Not Specified' }}</span>
                        @if($jobRequisition->reference_number)
                        <span class="me-3"><i class="fas fa-hashtag"></i> {{ $jobRequisition->reference_number }}</span>
                        @endif
                        <span><i class="fas fa-calendar"></i> Created {{ $jobRequisition->created_at->format('M j, Y') }}</span>
                    </div>
                </div>
                <a href="{{ route('job-requisitions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Jobs
                </a>
            </div>
            <div>
                <span class="badge bg-info text-uppercase me-2">{{ $jobRequisition->approval_status ?? 'Pending' }}</span>
                @if($jobRequisition->job_status)
                    <span class="badge bg-secondary text-uppercase">{{ $jobRequisition->job_status }}</span>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Position Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 col-md-4 mb-3">
                                <div class="fw-bold">Vacancies</div>
                                <div>{{ $jobRequisition->vacancies ?? 1 }}</div>
                            </div>
                            <div class="col-6 col-md-4 mb-3">
                                <div class="fw-bold">Location</div>
                                <div>{{ $jobRequisition->location ?? 'Not specified' }}</div>
                            </div>
                            <div class="col-6 col-md-4 mb-3">
                                <div class="fw-bold">Employment Type</div>
                                <div>{{ ucfirst($jobRequisition->employment_type ?? 'Full-time') }}</div>
                            </div>
                            <div class="col-6 col-md-4 mb-3">
                                <div class="fw-bold">Application Deadline</div>
                                <div>
                                    @if($jobRequisition->application_deadline)
                                        {{ $jobRequisition->application_deadline->format('M j, Y g:i A') }}
                                    @else
                                        Open
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mb-3">
                                <div class="fw-bold">Min. Experience</div>
                                <div>
                                    {{ $jobRequisition->min_experience ?? 0 }} 
                                    {{ ($jobRequisition->min_experience ?? 0) == 1 ? 'year' : 'years' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mb-3">
                                <div class="fw-bold">Education Level</div>
                                <div>{{ $jobRequisition->education_level ?? 'Not specified' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Job Description</h5>
                    </div>
                    <div class="card-body">
                        @if($jobRequisition->description)
                            {!! $jobRequisition->description !!}
                        @else
                            <p class="text-muted fst-italic">No description provided.</p>
                        @endif
                    </div>
                </div>



{{-- Skills Summary in Sidebar - Also Fixed --}}
@if(($jobRequisition->skills && $jobRequisition->skills->count() > 0) )
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Key Skills</h5>
    </div>
    <div class="card-body">
        @if($jobRequisition->skills && $jobRequisition->skills->count() > 0)
            @foreach($jobRequisition->skills->take(6) as $skill)
                <span class="badge bg-primary me-1 mb-1">{{ $skill->name }}</span>
            @endforeach
         
       
        @endif
    </div>
</div>
@endif

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Requirements & Notes</h5>
                    </div>
                    <div class="card-body">
                        @if($jobRequisition->requirements)
                            {!! $jobRequisition->requirements !!}
                        @else
                            <p class="text-muted fst-italic">No specific requirements listed.</p>
                        @endif
                    </div>
                </div>

                @if((auth()->check() && auth()->user()->role !== 'applicant') || ($jobRequisition->approved_by ?? false))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Team Information</h5>
                    </div>
                    <div class="card-body d-flex gap-4 flex-wrap">
                        @if(auth()->check() && auth()->user()->role !== 'applicant' && $jobRequisition->creator)
                        <div class="text-center">
                            <div class="mb-2 fs-1 text-primary"><i class="fas fa-user-plus"></i></div>
                            <div>{{ $jobRequisition->creator->name }}</div>
                            <small class="text-muted">Job Creator</small><br>
                            <small class="text-muted">{{ $jobRequisition->created_at->format('M j, Y') }}</small>
                        </div>
                        @endif

                        @if($jobRequisition->approved_by && $jobRequisition->approver)
                        <div class="text-center">
                            <div class="mb-2 fs-1 text-success"><i class="fas fa-user-check"></i></div>
                            <div>{{ $jobRequisition->approver->name }}</div>
                            <small class="text-muted">Approved By</small><br>
                            <small class="text-muted">{{ $jobRequisition->approved_at->format('M j, Y') }}</small>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            <div class="col-lg-4">

                @if(auth()->check() && method_exists(auth()->user(), 'isApplicant') && auth()->user()->isApplicant())
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="mb-3">Ready to Apply?</h5>
                        <p>Join our team and take the next step in your career.</p>
                        <a href="{{ route('job-applications.create', ['job_requisition' => $jobRequisition->id]) }}" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Apply Now
                        </a>
                    </div>
                </div>
                @endif

              
                

                <div class="card sticky-top">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Job Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-flex align-items-center">
                            <i class="fas fa-users fs-4 me-3 text-secondary"></i>
                            <div>
                                <div class="fw-bold">Open Positions</div>
                                <div>{{ $jobRequisition->vacancies ?? 1 }}</div>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center">
                            <i class="fas fa-map-marker-alt fs-4 me-3 text-secondary"></i>
                            <div>
                                <div class="fw-bold">Location</div>
                                <div>{{ $jobRequisition->location ?? 'Remote' }}</div>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center">
                            <i class="fas fa-briefcase fs-4 me-3 text-secondary"></i>
                            <div>
                                <div class="fw-bold">Employment Type</div>
                                <div>{{ ucfirst($jobRequisition->employment_type ?? 'Full-time') }}</div>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center">
                            <i class="fas fa-chart-line fs-4 me-3 text-secondary"></i>
                            <div>
                                <div class="fw-bold">Experience Required</div>
                                <div>
                                    {{ $jobRequisition->min_experience ?? 0 }} 
                                    {{ ($jobRequisition->min_experience ?? 0) == 1 ? 'year' : 'years' }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center">
                            <i class="fas fa-graduation-cap fs-4 me-3 text-secondary"></i>
                            <div>
                                <div class="fw-bold">Education Level</div>
                                <div>{{ $jobRequisition->education_level ?? 'Not specified' }}</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt fs-4 me-3 text-secondary"></i>
                            <div>
                                <div class="fw-bold">Apply By</div>
                                <div>
                                    @if($jobRequisition->application_deadline)
                                        {{ $jobRequisition->application_deadline->format('M j, Y') }}
                                    @else
                                        Open
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection