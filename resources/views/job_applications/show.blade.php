@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">
        <!-- Header with Status -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">{{ $application->user->name ?? 'N/A' }}</h2>
                <p class="text-muted">Applied for {{ $application->jobRequisition->title ?? 'N/A' }}</p>
            </div>
            @php
                $statusConfig = [
                    'submitted' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Submitted'],
                    'shortlisted' => ['class' => 'info', 'icon' => 'user-check', 'text' => 'Shortlisted'],
                    'offer sent' => ['class' => 'primary', 'icon' => 'envelope', 'text' => 'Offer Sent'],
                    'hired' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Hired'],
                    'rejected' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Rejected']
                ];
                $status = $statusConfig[$application->status] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
            @endphp
            <span class="badge badge-{{ $status['class'] }} badge-lg">
                <i class="fas fa-{{ $status['icon'] }} me-1"></i>{{ $status['text'] }}
            </span>
        </div>

        <!-- Score Card -->
        @if(isset($application->score_breakdown))
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="row text-center">
                            <div class="col-3">
                                <h4 class="text-primary mb-0">{{ $application->score_breakdown['skills'] }}/100</h4>
                                <small class="text-muted">Skills</small>
                            </div>
                            <div class="col-3">
                                <h4 class="text-info mb-0">{{ $application->score_breakdown['experience'] }}/50</h4>
                                <small class="text-muted">Experience</small>
                            </div>
                            <div class="col-3">
                                <h4 class="text-warning mb-0">{{ $application->score_breakdown['education'] }}/50</h4>
                                <small class="text-muted">Education</small>
                            </div>
                            <div class="col-3">
                                <h4 class="text-success mb-0">{{ $application->score_breakdown['qualifications'] }}/20</h4>
                                <small class="text-muted">Qualifications</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <h1 class="display-4 text-primary mb-0">{{ round($application->score, 2) }}%</h1>
                        <small class="text-muted">Final Score</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Job & Contact Info -->
            
                <style>
                    .info-box {
                        display: flex;
                        align-items: center;
                        padding: 1rem;
                        background-color: #f8f9fa;
                        border-radius: .75rem;
                        height: 100%;
                        gap: 1rem;
                    }
                    .info-icon {
                        width: 42px;
                        height: 42px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 50%;
                    }
                    .info-label {
                        font-size: .75rem;
                        color: #6c757d;
                        margin-bottom: 2px;
                    }
                    .info-value {
                        font-weight: 600;
                        color: #343a40;
                        font-size: 0.95rem;
                    }
                </style>
                
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Job Details -->
                            <div class="col-md-6">
                                <div class="mb-4 d-flex align-items-center">
                                    <div class="info-icon bg-primary bg-opacity-10 text-primary me-2">
                                        <i class="bi bi-briefcase-fill fs-5"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0 text-dark">Job Details</h5>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="info-box">
                                            <div class="info-icon bg-primary bg-opacity-10 text-primary">
                                                <i class="bi bi-hash fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="info-label">Reference Number</div>
                                                <div class="info-value">{{ $application->jobRequisition->reference_number ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-box">
                                            <div class="info-icon bg-info bg-opacity-10 text-info">
                                                <i class="bi bi-building fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="info-label">Department</div>
                                                <div class="info-value">{{ $application->jobRequisition->department->name ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-box">
                                            <div class="info-icon bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-person-badge fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="info-label">Type</div>
                                                <div class="info-value">{{ ucfirst($application->jobRequisition->employment_type ?? 'N/A') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-box">
                                            <div class="info-icon bg-warning bg-opacity-10 text-warning">
                                                <i class="bi bi-geo-alt-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="info-label">Location</div>
                                                <div class="info-value">{{ $application->jobRequisition->location ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-box">
                                            <div class="info-icon bg-danger bg-opacity-10 text-danger">
                                                <i class="bi bi-clock-history fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="info-label">Experience</div>
                                                <div class="info-value">{{ $application->jobRequisition->min_experience ?? 'N/A' }} Years</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-box">
                                            <div class="info-icon bg-secondary bg-opacity-10 text-secondary">
                                                <i class="bi bi-mortarboard-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="info-label">Education</div>
                                                <div class="info-value">{{ $application->jobRequisition->education_level ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                
                            <!-- Contact Info -->
                            <div class="col-md-6">
                                <div class="mb-4 d-flex align-items-center">
                                    <div class="info-icon bg-info bg-opacity-10 text-info me-2">
                                        <i class="bi bi-person-fill fs-5"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0 text-dark">Contact Information</h5>
                                </div>
                                @if($application->user->profile)
                                    @php $profile = $application->user->profile; @endphp
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="info-box">
                                                <div class="info-icon bg-primary bg-opacity-10 text-primary">
                                                    <i class="bi bi-envelope-fill fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="info-label">Email Address</div>
                                                    <a href="mailto:{{ $application->user->email }}" class="info-value text-decoration-none text-primary">
                                                        {{ $application->user->email ?? 'N/A' }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="info-box">
                                                <div class="info-icon bg-success bg-opacity-10 text-success">
                                                    <i class="bi bi-telephone-fill fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="info-label">Phone Number</div>
                                                    @if($profile->phone)
                                                        <a href="tel:{{ $profile->phone }}" class="info-value text-decoration-none text-success">
                                                            {{ $profile->phone }}
                                                        </a>
                                                    @else
                                                        <div class="info-value">N/A</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <div class="info-icon bg-warning bg-opacity-10 text-warning">
                                                    <i class="bi bi-cake2-fill fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="info-label">Date of Birth</div>
                                                    <div class="info-value">
                                                        {{ $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('M d, Y') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <div class="info-icon bg-info bg-opacity-10 text-info">
                                                    <i class="bi bi-calendar-check-fill fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="info-label">Applied On</div>
                                                    <div class="info-value">
                                                        {{ $application->created_at ? $application->created_at->format('M d, Y') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="bi bi-person-dash-fill text-muted fs-1 opacity-50"></i>
                                        <p class="text-muted mt-3 mb-0">No profile information available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                

                <!-- Skills & Experience -->
                @if($application->user->profile)
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Skills -->
                        <h5 class="mb-3"><i class="fas fa-star text-warning me-2"></i>Skills</h5>
                        <div class="mb-4">
                            @forelse($application->user->skills as $skill)
                                <span class="badge badge-primary me-1 mb-1">{{ $skill->name }}</span>
                            @empty
                                <span class="text-muted">No skills listed</span>
                            @endforelse
                        </div>

                        <!-- Experience -->
                        <h5 class="mb-3"><i class="fas fa-briefcase text-success me-2"></i>Experience</h5>
                        @forelse($application->user->experiences as $exp)
                            <div class="border-start border-3 border-primary ps-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $exp->job_title }}</h6>
                                        <div class="text-muted">{{ $exp->company ?? 'N/A' }}</div>
                                    </div>
                                    <span class="badge bg-secondary text-light fw-normal">
                                        {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('M Y') : 'N/A' }} – 
                                        {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : 'Present' }}
                                    </span>
                                    
                                </div>
                                @if($exp->description)
                                    <p class="text-muted mt-2 mb-0">{{ $exp->description }}</p>
                                @endif
                            </div>
                        @empty
                            <span class="text-muted">No experience listed</span>
                        @endforelse
                    </div>
                </div>

                <!-- Education & Qualifications -->
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Education -->
                        <h5 class="mb-3"><i class="fas fa-graduation-cap text-info me-2"></i>Education</h5>
                        @forelse($application->user->education as $edu)
                            <div class="border-start border-3 border-info ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge badge-info mb-1">{{ $edu->education_level }}</span>
                                        <h6 class="mb-1">{{ $edu->degree }}</h6>
                                        <div class="text-muted">{{ $edu->institution }}</div>
                                    </div>
                                    <span class="badge bg-secondary text-light fw-normal">
                                        {{ $edu->start_date ? \Carbon\Carbon::parse($edu->start_date)->format('Y') : 'N/A' }} – 
                                        {{ $edu->end_date ? \Carbon\Carbon::parse($edu->end_date)->format('Y') : 'Present' }}
                                    </span>
                                    
                                </div>
                            </div>
                        @empty
                            <span class="text-muted">No education listed</span>
                        @endforelse

                        <!-- Qualifications -->
                        <h5 class="mb-3 mt-4"><i class="fas fa-certificate text-warning me-2"></i>Additional Qualifications</h5>
                        @forelse($application->user->qualifications as $qualification)
                            <div class="border-start border-3 border-warning ps-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="badge badge-warning mb-1">{{ $qualification->type }}</span>
                                        <h6 class="mb-1">{{ $qualification->title }}</h6>
                                        <div class="text-muted">{{ $qualification->institution }}</div>
                                    </div>
                                    <span class="badge bg-secondary text-light fw-normal">{{ $qualification->issued_date ? \Carbon\Carbon::parse($qualification->issued_date)->format('Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        @empty
                            <span class="text-muted">No qualifications listed</span>
                        @endforelse

                        <!-- References -->
                        <h5 class="mb-3 mt-4"><i class="fas fa-users text-secondary me-2"></i>References</h5>
                        @forelse($application->user->references as $ref)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user-circle text-muted me-2"></i>
                                <div>
                                    <strong>{{ $ref->name }}</strong>
                                    <span class="text-muted">- {{ $ref->relationship }}</span>
                                </div>
                            </div>
                        @empty
                            <span class="text-muted">No references listed</span>
                        @endforelse
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->isManager() || auth()->user()->isHrAdmin())
                            @if($application->status == 'offer sent')
                                <form action="{{ route('applications.update-status', $application->id) }}" method="POST" class="mb-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="hired">
                                    <button class="btn btn-success w-100" type="submit">
                                        <i class="fas fa-check me-2"></i>Approve
                                    </button>
                                </form>
                            @endif

                            @if($application->status !== 'rejected')
                                <form action="{{ route('applications.update-status', $application->id) }}" method="POST" class="mb-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-danger w-100" type="submit">
                                        <i class="fas fa-times me-2"></i>Reject
                                    </button>
                                </form>
                            @endif
                                
                            @if(!$application->interviews)
                                <button id="scheduleBtn" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-calendar-plus me-2"></i>Schedule Interview
                                </button>
                            @endif
                        @endif

                        @if($application->interviews)
                            <button class="btn btn-info w-100 mb-2" onclick="toggleSection('scoringSection')">
                                <i class="fas fa-star me-2"></i>Score Interview
                            </button>
                            <button class="btn btn-warning w-100 mb-2" onclick="toggleSection('offerLetterForm')">
                                <i class="fas fa-envelope-open-text me-2"></i>Send Offer Letter
                            </button>
                        @endif

                        <button class="btn btn-outline-primary w-100" onclick="downloadResume()">
                            <i class="fas fa-download me-2"></i>Download Resume
                        </button>

                        <!-- Interview Scoring Form -->
                        @if($application->interviews)
                        <div id="scoringSection" class="mt-3" style="display: none;">
                            <hr>
                            @php
                                $latestInterview = $application->interviews;
                                $existingScore = $latestInterview ? $latestInterview->interviewScore : null;
                            @endphp
                            
                            <form method="POST" action="{{ route('interviews.score.store', $latestInterview) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <label class="form-label">Technical (1-5)</label>
                                        <input type="number" name="technical_skills" class="form-control form-control-sm" min="1" max="5" 
                                               value="{{ old('technical_skills', $existingScore->technical_skills ?? '') }}" required>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="form-label">Communication (1-5)</label>
                                        <input type="number" name="communication" class="form-control form-control-sm" min="1" max="5" 
                                               value="{{ old('communication', $existingScore->communication ?? '') }}" required>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="form-label">Cultural Fit (1-5)</label>
                                        <input type="number" name="cultural_fit" class="form-control form-control-sm" min="1" max="5" 
                                               value="{{ old('cultural_fit', $existingScore->cultural_fit ?? '') }}" required>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="form-label">Problem Solving (1-5)</label>
                                        <input type="number" name="problem_solving" class="form-control form-control-sm" min="1" max="5" 
                                               value="{{ old('problem_solving', $existingScore->problem_solving ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Comments</label>
                                    <textarea name="comments" class="form-control form-control-sm" rows="2">{{ old('comments', $existingScore->comments ?? '') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Save Score
                                </button>
                            </form>
                        </div>

                        <!-- Offer Letter Form -->
                        <div id="offerLetterForm" class="mt-3" style="display: none;">
                            <hr>
                            <form method="POST" action="{{ route('applications.offerLetter.send', $application->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Offer Letter (PDF)</label>
                                    <input type="file" name="offer_letter" accept="application/pdf" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control form-control-sm" rows="2">{{ old('message') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Send Offer
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Documents -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Documents</h5>
                    </div>
                    <div class="card-body">
                        @forelse($application->user->attachments ?? [] as $attachment)
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
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-decoration-none">
                                    {{ $attachment->original_name }}
                                </a>
                            </div>
                        @empty
                            <div class="text-center text-muted">
                                <i class="fas fa-file-times fa-2x mb-2"></i>
                                <p class="mb-0">No documents</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Interview Scores (HR Admin only) -->
                @if(auth()->user()->isHrAdmin() && $application->interviews && $application->interviews->scores && $application->interviews->scores->count() > 0)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Interview Scores</h5>
                        <small class="text-muted">{{ $application->interviews->interview_date ? \Carbon\Carbon::parse($application->interviews->interview_date)->format('M d, Y') : 'N/A' }}</small>
                    </div>
                    <div class="card-body">
                        @foreach($application->interviews->scores as $score)
                            <div class="mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>{{ $score->interviewer->name ?? 'Unknown' }}</strong>
                                    <span class="badge badge-primary">{{ $score->total_score }}/5.0</span>
                                </div>
                                <div class="row text-center">
                                    <div class="col-3">
                                        <small class="text-muted">Tech</small>
                                        <div class="fw-bold">{{ $score->technical_skills }}/5</div>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted">Comm</small>
                                        <div class="fw-bold">{{ $score->communication }}/5</div>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted">Culture</small>
                                        <div class="fw-bold">{{ $score->cultural_fit }}/5</div>
                                    </div>
                                    <div class="col-3">
                                        <small class="text-muted">Problem</small>
                                        <div class="fw-bold">{{ $score->problem_solving }}/5</div>
                                    </div>
                                </div>
                                @if($score->comments)
                                    <div class="mt-2">
                                        <small class="text-muted">Comments:</small>
                                        <p class="mb-0 small">{{ $score->comments }}</p>
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

                <!-- Schedule Interview Form -->
                <div id="scheduleForm" class="card" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Schedule Interview</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('interviews.store') }}">
                            @csrf
                            <input type="hidden" name="job_application_id" value="{{ $application->id }}">
                            <input type="hidden" name="applicant_id" value="{{ $application->user_id }}">
                            
                            <div class="mb-3">
                                <label class="form-label">Date & Time</label>
                                <input type="datetime-local" name="interview_date" class="form-control" 
                                       min="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-calendar-check me-2"></i>Schedule
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle sections
function toggleSection(id) {
    const element = document.getElementById(id);
    element.style.display = element.style.display === 'none' ? 'block' : 'none';
}

// Schedule form
document.getElementById('scheduleBtn')?.addEventListener('click', () => {
    document.getElementById('scheduleForm').style.display = 'block';
    document.getElementById('scheduleBtn').style.display = 'none';
});

document.getElementById('cancelBtn')?.addEventListener('click', () => {
    document.getElementById('scheduleForm').style.display = 'none';
    document.getElementById('scheduleBtn').style.display = 'block';
});

// Download resume
// Update your downloadResume function in the view
function downloadResume() {
    window.open(`{{ route('applications.download-resume', $application->id) }}`, '_blank');
}

@endsection