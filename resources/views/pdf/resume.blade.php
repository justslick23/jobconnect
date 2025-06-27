<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Resume</title>
<style>
       @page {
        size: A4 portrait;
        margin: 20mm;
    }
    html, body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 0;
        font-family: 'Arial', sans-serif;
        font-size: 13px;
        background: white;
    }
    .resume-container {
        display: flex;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }
    .resume-sidebar {
        width: 33%;
        background: #f8f9fa;
        padding: 20px;
        border-right: 1px solid #dee2e6;
    }
    .resume-main {
        width: 67%;
        padding: 30px;
    }
    .profile-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #dc3545;
        margin-bottom: 15px;
    }
    .section-title {
        font-weight: 600;
        border-bottom: 2px solid #dc3545;
        padding-bottom: 3px;
        margin-bottom: 10px;
        color: #dc3545;
        text-transform: uppercase;
    }
    .status-badge {
        font-size: 12px;
        padding: 2px 10px;
        border-radius: 20px;
        color: white;
        background-color: #198754;
        text-transform: capitalize;
    }
</style>
</head>
<body>
<div class="resume-container">
    <!-- Sidebar -->
    <div class="resume-sidebar text-center">
        <!-- Profile Image -->
        {{-- <img src="{{ asset('path/to/profile.jpg') }}" alt="Profile Image" class="profile-image"> --}}

        <!-- Name & Title -->
        <h3 class="text-danger fw-bold mb-0">{{ strtoupper($application->user->name ?? 'Applicant Name') }}</h3>
        <p class="text-muted">{{ $application->jobRequisition->title ?? 'Job Title' }}</p>

        <!-- Contact Info -->
        <div class="text-start mt-4">
            <div class="section-title">Contact</div>
            <p><strong>Email:</strong> {{ $application->user->email }}</p>
            @if($application->user->profile && $application->user->profile->phone)
                <p><strong>Phone:</strong> {{ $application->user->profile->phone }}</p>
            @endif
            <p><strong>LinkedIn:</strong> www.linkedin.com/in/profile</p>
            <p><strong>Location:</strong> {{ $application->jobRequisition->location ?? 'City, Country' }}</p>
        </div>

        <!-- Skills -->
        @if($application->user->skills && $application->user->skills->count())
        <div class="text-start mt-4">
            <div class="section-title">Skills</div>
            <ul class="ps-3 mb-0">
                @foreach($application->user->skills as $skill)
                    <li>{{ $skill->name }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Education -->
        @if($application->user->education && $application->user->education->count())
        <div class="text-start mt-4">
            <div class="section-title">Education</div>
            @foreach($application->user->education as $edu)
                <p class="mb-1"><strong>{{ $edu->degree }}</strong><br>
                {{ $edu->institution }}<br>
                {{ \Carbon\Carbon::parse($edu->start_date)->format('Y') ?? '' }} - {{ \Carbon\Carbon::parse($edu->end_date)->format('Y') ?? 'Present' }}</p>
            @endforeach
        </div>
        @endif

        <!-- Languages -->
        <div class="text-start mt-4">
            <div class="section-title">Languages</div>
            <p>English (Fluent)</p>
            <p>French (Fluent)</p>
            <p>German (Basic)</p>
            <p>Spanish (Intermediate)</p>
        </div>
    </div>

    <!-- Main Section -->
    <div class="resume-main">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="fw-bold mb-0">{{ strtoupper($application->user->name ?? 'Applicant Name') }}</h2>
                <p class="text-muted">{{ strtoupper($application->jobRequisition->title ?? 'Job Title') }}</p>
            </div>
            <div class="text-end">
                <p><strong>Applied:</strong> {{ $application->created_at->format('M d, Y') }}</p>
                <p><strong>Ref:</strong> {{ $application->jobRequisition->reference_number ?? 'N/A' }}</p>
                <span class="status-badge">{{ ucfirst($application->status) }}</span>
            </div>
        </div>

        <!-- Profile Summary -->
        <div class="mb-4">
            <div class="section-title">Profile</div>
            <p>
                {{ $application->user->profile->bio ?? 'Experienced professional with a strong background in ' . ($application->jobRequisition->department->name ?? 'business operations') . '.' }}
            </p>
        </div>

        <!-- Experience -->
        @if($application->user->experiences && $application->user->experiences->count())
        <div class="mb-4">
            <div class="section-title">Experience</div>
            @foreach($application->user->experiences as $exp)
            <div class="mb-3">
                <h5 class="mb-1">{{ $exp->job_title ?? 'Job Title' }}</h5>
                <p class="mb-1 text-muted">{{ $exp->company ?? 'Company Name' }}</p>
                <p class="mb-1 text-muted">{{ \Carbon\Carbon::parse($exp->start_date)->format('Y') ?? '' }} - {{ \Carbon\Carbon::parse($exp->end_date)->format('Y') ?? 'Present' }}</p>
                <p>{{ $exp->description ?? 'No description provided.' }}</p>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Qualifications -->
        @if($application->user->qualifications && $application->user->qualifications->count())
        <div class="mb-4">
            <div class="section-title">Qualifications</div>
            @foreach($application->user->qualifications as $qualification)
            <div class="mb-2">
                <h6 class="mb-1">{{ $qualification->title ?? 'Qualification Title' }}</h6>
                <p class="mb-0 text-muted">{{ $qualification->institution ?? 'Institution' }} - {{ $qualification->issued_date ? \Carbon\Carbon::parse($qualification->issued_date)->format('Y') : 'Year' }}</p>
            </div>
            @endforeach
        </div>
        @endif

        <!-- References -->
        @if($application->user->references && $application->user->references->count())
        <div>
            <div class="section-title">References</div>
            <div class="row">
                @foreach($application->user->references as $ref)
                <div class="col-md-6 mb-2">
                    <p class="mb-1"><strong>{{ $ref->name }}</strong></p>
                    <p class="mb-1 text-muted">{{ $ref->company }} / {{ $ref->relationship }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ $ref->email }}<br><strong>Phone:</strong> {{ $ref->phone }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center mt-4 text-muted small">
            Generated on {{ now()->format('F j, Y \a\t g:i A') }} — HR Management System
        </div>
    </div>
</div>
</body>
</html>