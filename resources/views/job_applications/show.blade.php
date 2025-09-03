@extends('layouts.app')

@section('content')
@section('title',  $application->jobRequisition->title .  ' Application Details')

<div class="container">
    <div class="page-inner">
        
        <!-- Minimal Header -->
        <div class="page-header">
            <h3 class="fw-bold mb-3">
                {{ $application->user->profile
                    ? $application->user->profile->first_name . ' ' . $application->user->profile->last_name 
                    : 'Unknown Applicant' 
                }}
            </h3>
            
                        <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('job-applications.index') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Application Details</a></li>
            </ul>
        </div>
        @include('partials.alerts')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                
                <!-- Single Comprehensive Card -->
                <div class="card card-round">
                    <div class="card-body">
                        
                        <!-- Application Meta Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-user ms-0">
                                    <div class="username">{{ $application->jobRequisition->title ?? 'Unknown Position' }}</div>
                                    <div class="status">
                                        @php
                                            $statusConfig = [
                                                'submitted' => ['class' => 'warning', 'text' => 'Submitted'],
                                                'review' => ['class' => 'warning', 'text' => 'Under Review'],
                                                'shortlisted' => ['class' => 'info', 'text' => 'Shortlisted'],
                                                'offer sent' => ['class' => 'primary', 'text' => 'Offer Sent'],
                                                'hired' => ['class' => 'success', 'text' => 'Hired'],
                                                'rejected' => ['class' => 'danger', 'text' => 'Rejected']
                                            ];
                                            $status = $statusConfig[$application->status] ?? ['class' => 'secondary', 'text' => 'Unknown'];
                                        @endphp
                                        <span class="badge badge-{{ $status['class'] }}">{{ $status['text'] }}</span>
                                        <span class="text-muted ms-2">{{ $application->jobRequisition->department->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">

                                @if($application->score)
                                <div class="score-display">
                                    <h2 class="fw-bold text-primary mb-0">{{ round($application->score->total_score, 1) }}%</h2>
                                    <small class="text-muted">Match Score</small>
                                </div>
                                @endif
                            </div>
                        </div>

                    
                        @if($application->score)
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Score Breakdown</h5>
                            <div class="row">
                                @php
                                    $skillsWeight = $settings->skills_weight ?: 1;
                                    $experienceWeight = $settings->experience_weight ?: 1;
                                    $educationWeight = $settings->education_weight ?: 1;
                                    $qualificationBonusMax = $settings->qualification_bonus ?: 1;
                    
                                    $skillsPercent = min(100, ($application->score->skills_score / $skillsWeight) * 100);
                                    $experiencePercent = min(100, ($application->score->experience_score / $experienceWeight) * 100);
                                    $educationPercent = min(100, ($application->score->education_score / $educationWeight) * 100);
                                    $qualificationPercent = min(100, ($application->score->qualification_bonus / $qualificationBonusMax) * 100);
                                @endphp
                    
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="progress-stat">
                                        <div class="progress-value text-primary">{{ $application->score->skills_score }}/{{ $settings->skills_weight }}</div>
                                        <div class="progress-label">Skills Match</div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary" style="width: {{ $skillsPercent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="progress-stat">
                                        <div class="progress-value text-info">{{ $application->score->experience_score }}/{{ $settings->experience_weight }}</div>
                                        <div class="progress-label">Experience</div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-info" style="width: {{ $experiencePercent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="progress-stat">
                                        <div class="progress-value text-warning">{{ $application->score->education_score }}/{{ $settings->education_weight }}</div>
                                        <div class="progress-label">Education</div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-warning" style="width: {{ $educationPercent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="progress-stat">
                                        <div class="progress-value text-success">{{ $application->score->qualification_bonus }}/{{ $settings->qualification_bonus }}</div>
                                        <div class="progress-label">Qualifications</div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-success" style="width: {{ $qualificationPercent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    

                        <div class="separator-dashed"></div>

                        <!-- Contact Information -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Contact Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="contact-item mb-2">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <a href="mailto:{{ $application->user->email }}" class="text-decoration-none">
                                            {{ $application->user->email }}
                                        </a>
                                    </div>
                                    @if($application->user->profile->phone ?? null)
                                    <div class="contact-item mb-2">
                                        <i class="fas fa-phone text-success me-2"></i>
                                        <a href="tel:{{ $application->user->profile->phone }}" class="text-decoration-none">
                                            {{ $application->user->profile->phone }}
                                        </a>
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    @if($application->user->profile->date_of_birth ?? null)
                                    <div class="contact-item mb-2">
                                        <i class="fas fa-calendar text-info me-2"></i>
                                        {{ \Carbon\Carbon::parse($application->user->profile->date_of_birth)->format('M d, Y') }}
                                    </div>
                                    @endif
                                    <div class="contact-item mb-2">
                                        <i class="fas fa-clock text-warning me-2"></i>
                                        Applied {{ $application->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Skills Section -->
                        @if($application->user->skills->count() > 0)
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Skills & Expertise</h5>
                            <div>
                                @foreach($application->user->skills as $skill)
                                <span class="badge bg-primary me-1 mb-1">{{ $skill->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Experience Section -->
                        @if($application->user->experiences->count() > 0)
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Professional Experience</h5>
                            @foreach($application->user->experiences as $exp)
                            <div class="experience-item mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 text-primary">{{ $exp->job_title }}</h6>
                                        <div class="text-muted">{{ $exp->company ?? 'Company Not Specified' }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('M Y') : 'N/A' }} – 
                                        {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : 'Present' }}
                                    </span>
                                </div>
                                @if($exp->description)
                                <p class="text-muted mt-2 mb-0 small">{!! $exp->description !!}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Education Section -->
                        @if($application->user->education->count() > 0)
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Education Background</h5>
                            @foreach($application->user->education as $edu)
                                <div class="education-item mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-info me-2">{{ $edu->education_level }}</span>
                                            <h6 class="mb-1 d-inline">{{ $edu->degree }}</h6>
                                            <div class="text-muted">
                                                {{ $edu->institution }}
                                                @if($edu->field_of_study)
                                                    <span class="text-primary"> – {{ $edu->field_of_study }}</span>
                                                @endif
                                            </div>
                    
                                            {{-- Education status --}}
                                            <br>
                                            @if($edu->status)
                                                <span class="badge bg-secondary">{{ ucfirst($edu->status) }}</span>
                                            @endif
                                        </div>
                                        <span class="badge bg-light text-dark">
                                            {{ $edu->start_date ? \Carbon\Carbon::parse($edu->start_date)->format('Y') : 'N/A' }} – 
                                            {{ $edu->end_date ? \Carbon\Carbon::parse($edu->end_date)->format('Y') : 'Present' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    

                        <!-- Qualifications Section -->
                        @if($application->user->qualifications->count() > 0)
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Certifications & Qualifications</h5>
                            @foreach($application->user->qualifications as $qualification)
                            <div class="qualification-item mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-warning me-2">{{ $qualification->type }}</span>
                                        <h6 class="mb-1 d-inline">{{ $qualification->title }}</h6>
                                        <div class="text-muted">{{ $qualification->institution }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        {{ $qualification->issued_date ? \Carbon\Carbon::parse($qualification->issued_date)->format('Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                
                <!-- Quick Actions Card -->
                <div class="card card-round">
                    <div class="card-body">
                        <div class="card-title fw-mediumbold">Application Actions</div>
                        
                        @php
                            $status = $application->status;
                            $hasInterview = $application->interviews ?? false;
                            $isManagerOrHr = (auth()->user()->isManager() ?? false) || (auth()->user()->isHrAdmin() ?? false);
                        @endphp

                        @if($isManagerOrHr)
                            @if($status === 'offer sent')
                                <form action="{{ route('applications.update-status', $application->id) }}" method="POST" class="mb-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="hired">
                                    <button class="btn btn-success btn-round w-100" type="submit">
                                        <i class="fas fa-check me-2"></i>Approve & Hire
                                    </button>
                                </form>

                                <form action="{{ route('applications.update-status', $application->id) }}" method="POST" class="mb-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-danger btn-round w-100" type="submit">
                                        <i class="fas fa-times me-2"></i>Reject Application
                                    </button>
                                </form>

                            @elseif($status !== 'rejected' && $status !== 'hired')
                            
                    
                                @if(!$hasInterview)
                                    <button class="btn btn-primary btn-round w-100 mb-2" data-bs-toggle="modal" data-bs-target="#scheduleInterviewModal">
                                        <i class="fas fa-calendar-plus me-2"></i>Schedule Interview
                                    </button>
                                @endif
                                </form>
                              

                                @if($hasInterview)
                                    <button class="btn btn-info btn-round w-100 mb-2" onclick="toggleSection('scoringSection')">
                                        <i class="fas fa-star me-2"></i>Score Interview
                                    </button>
                                    <form action="{{ route('applications.update-status', $application->id) }}" method="POST" class="mb-2">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="offer sent">
                                        <button class="btn btn-warning btn-round w-100" type="submit">
                                            <i class="fas fa-envelope-open-text me-2"></i>Send Offer
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('applications.update-status', $application->id) }}" method="POST" class="mb-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-outline-danger btn-round w-100" type="submit">
                                        <i class="fas fa-times me-2"></i>Reject Application
                                    </button>
                                </form>
                            @endif
                        @endif

                        <button class="btn btn-light btn-border btn-round w-100 mt-2" onclick="downloadResume()">
                            <i class="fas fa-download me-2"></i>Download Resume
                        </button>
                        
                        <a href="{{ route('job-applications.index') }}" class="btn btn-light btn-border btn-round w-100 mt-2">
                            <i class="fa fa-arrow-left me-2"></i>Back to Applications
                        </a>
                    </div>
                </div>

                <!-- Job Information Card -->
                <div class="card card-round">
                    <div class="card-body">
                        <div class="card-title fw-mediumbold">Job Information</div>
                        <div class="card-list">
                            <div class="item-list">
                                <div class="info-user ms-0">
                                    <div class="username">Reference Number</div>
                                    <div class="status">{{ $application->jobRequisition->reference_number ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="item-list">
                                <div class="info-user ms-0">
                                    <div class="username">Employment Type</div>
                                    <div class="status">{{ ucfirst($application->jobRequisition->employment_type ?? 'N/A') }}</div>
                                </div>
                            </div>
                            <div class="item-list">
                                <div class="info-user ms-0">
                                    <div class="username">Location</div>
                                    <div class="status">{{ $application->jobRequisition->location ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="item-list">
                                <div class="info-user ms-0">
                                    <div class="username">Required Experience</div>
                                    <div class="status">{{ $application->jobRequisition->min_experience ?? 0 }} Years</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Card -->
                @if($application->user->attachments->count() > 0)
                <div class="card card-round">
                    <div class="card-body">
                        <div class="card-title fw-mediumbold">Attached Documents</div>
                        @foreach($application->user->attachments as $attachment)
                            @php
                                $ext = strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION));
                                $iconClass = match($ext) {
                                    'pdf' => 'fas fa-file-pdf text-danger',
                                    'doc', 'docx' => 'fas fa-file-word text-primary',
                                    default => 'fas fa-file text-muted'
                                };
                            @endphp
                            <div class="d-flex align-items-center mb-2">
                                <i class="{{ $iconClass }} me-2"></i>
                                <a href="{{ route('attachments.download', $attachment->id) }}" target="_blank" class="text-decoration-none">
                                    {{ $attachment->type }}
                                </a>
                                
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Interview Scores Card -->
                @if((auth()->user()->isHrAdmin() ?? false) && ($application->interviews ?? null) && ($application->interviews->scores ?? collect())->count() > 0)
                <div class="card card-round">
                    <div class="card-body">
                        <div class="card-title fw-mediumbold">Interview Scores</div>
                        <p class="text-muted">{{ $application->interviews->interview_date ? \Carbon\Carbon::parse($application->interviews->interview_date)->format('M j, Y') : 'N/A' }}</p>
                        
                        @foreach($application->interviews->scores as $score)
                            <div class="mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>{{ $score->interviewer->name ?? 'Unknown' }}</strong>
                                    <span class="badge bg-primary">{{ $score->total_score }}/5.0</span>
                                </div>
                                <div class="row text-center small">
                                    <div class="col-3">
                                        <div class="text-muted">Tech</div>
                                        <div class="fw-bold">{{ $score->technical_skills }}</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-muted">Comm</div>
                                        <div class="fw-bold">{{ $score->communication }}</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-muted">Culture</div>
                                        <div class="fw-bold">{{ $score->cultural_fit }}</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="text-muted">Problem</div>
                                        <div class="fw-bold">{{ $score->problem_solving }}</div>
                                    </div>
                                </div>
                                @if($score->comments)
                                    <div class="mt-2">
                                        <small class="text-muted">{{ $score->comments }}</small>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if($application->interviews->scores->count() > 1)
                            <div class="text-center mt-3 pt-3 border-top">
                                <h4 class="text-primary mb-0">{{ $application->interviews->averageScore() }}/5.0</h4>
                                <small class="text-muted">Average Score</small>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>

        <!-- Schedule Interview Modal -->
        <div class="modal fade" id="scheduleInterviewModal" tabindex="-1" aria-labelledby="scheduleInterviewModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="scheduleInterviewModalLabel">
                            <i class="fas fa-calendar-plus me-2"></i>Schedule Interview
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('interviews.store') }}">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="job_application_id" value="{{ $application->id }}">
                            <input type="hidden" name="applicant_id" value="{{ $application->user_id }}">
                            
                            <div class="mb-3">
                                <label class="form-label">Interview Date & Time</label>
                                <input type="datetime-local" name="interview_date" class="form-control" 
                                       min="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Applicant:</strong> {{ $application->user->name }}<br>
                                <strong>Position:</strong> {{ $application->jobRequisition->title }}<br>
                                <strong>Email:</strong> {{ $application->user->email }}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-check me-2"></i>Schedule Interview
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Interview Scoring Section -->
        @if($hasInterview)
        <div id="scoringSection" class="card card-round" style="display: none;">
            <div class="card-body">
                <div class="card-title fw-mediumbold">Score Interview</div>
                @php
                    $latestInterview = $application->interviews;
                    $existingScore = $latestInterview->interviewScore ?? null;
                @endphp
                <form method="POST" action="{{ route('interviews.score.store', $latestInterview) }}">
                    @csrf
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Technical Skills (1-5)</label>
                            <input type="number" name="technical_skills" class="form-control"
                                   min="1" max="5" value="{{ old('technical_skills', $existingScore->technical_skills ?? '') }}" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Communication (1-5)</label>
                            <input type="number" name="communication" class="form-control"
                                   min="1" max="5" value="{{ old('communication', $existingScore->communication ?? '') }}" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Cultural Fit (1-5)</label>
                            <input type="number" name="cultural_fit" class="form-control"
                                   min="1" max="5" value="{{ old('cultural_fit', $existingScore->cultural_fit ?? '') }}" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Problem Solving (1-5)</label>
                            <input type="number" name="problem_solving" class="form-control"
                                   min="1" max="5" value="{{ old('problem_solving', $existingScore->problem_solving ?? '') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="comments" class="form-control" rows="3">{{ old('comments', $existingScore->comments ?? '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-round w-100">
                        <i class="fas fa-save me-2"></i>Save Interview Score
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
// Toggle sections
function toggleSection(id) {
    const element = document.getElementById(id);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

// Download resume
function downloadResume() {
    window.open(`{{ route('applications.download-resume', $application->id) }}`, '_blank');
}
</script>

<style>
.progress-stat {
    text-align: center;
}

.progress-value {
    font-size: 1.1rem;
    font-weight: bold;
    margin-bottom: 4px;
}

.progress-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 8px;
}

.progress-sm {
    height: 4px;
}

.contact-item {
    display: flex;
    align-items: center;
}

.experience-item, .education-item, .qualification-item {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.score-display {
    text-align: center;
}

.modal-content {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #bbdefb;
    color: #0d47a1;
}
</style>
@endsection