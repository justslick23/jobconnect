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
        font-size: 12px;
        line-height: 1.4;
        background: white;
        color: #333;
    }
    .resume-container {
        max-width: 100%;
        padding: 30px;
    }
    .header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #333;
    }
    .name {
        font-size: 26px;
        font-weight: bold;
        margin-bottom: 5px;
        text-transform: uppercase;
        color: #333;
    }
    .job-title {
        font-size: 16px;
        color: #666;
        margin-bottom: 15px;
    }
    .contact-info {
        font-size: 11px;
        color: #666;
    }
    .contact-row {
        margin-bottom: 3px;
    }
    .section {
        margin-bottom: 25px;
    }
    .section-title {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 12px;
        color: #333;
        border-bottom: 1px solid #333;
        padding-bottom: 3px;
    }
    .job-entry, .education-entry, .qualification-entry {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .job-entry:last-child, .education-entry:last-child, .qualification-entry:last-child {
        border-bottom: none;
    }
    .job-title-entry {
        font-weight: bold;
        font-size: 13px;
        margin-bottom: 2px;
    }
    .company-name {
        font-style: italic;
        color: #666;
        margin-bottom: 2px;
    }
    .date-range {
        color: #888;
        font-size: 11px;
        margin-bottom: 5px;
    }
    .description {
        margin-top: 5px;
        color: #555;
    }
    .skills-list {
        line-height: 1.6;
    }
    .skill-item {
        display: inline;
        margin-right: 15px;
    }
    .skill-item:after {
        content: "•";
        margin-left: 15px;
        color: #ccc;
    }
    .skill-item:last-child:after {
        content: "";
    }
    .two-column {
        display: flex;
        gap: 30px;
    }
    .column {
        flex: 1;
    }
    .reference-item {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .reference-item:last-child {
        border-bottom: none;
    }
    .reference-name {
        font-weight: bold;
        margin-bottom: 3px;
    }
    .reference-details {
        font-size: 11px;
        color: #666;
        line-height: 1.3;
    }
    .status-info {
        text-align: right;
        font-size: 11px;
        color: #666;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .status-badge {
        background: #333;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        text-transform: capitalize;
        font-size: 10px;
    }
    .footer {
        text-align: center;
        font-size: 10px;
        color: #999;
        margin-top: 30px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    .profile-summary {
        text-align: justify;
        color: #555;
        line-height: 1.5;
    }
    @media print {
        .resume-container {
            padding: 0;
        }
    }
</style>
</head>
<body>
<div class="resume-container">
    <!-- Header Section -->
    <div class="header">
        <div class="name">{{ strtoupper($application->user->name ?? 'Applicant Name') }}</div>
        <div class="job-title">{{ $application->jobRequisition->title ?? 'Job Title' }}</div>
        <div class="contact-info">
            <div class="contact-row">Email: {{ $application->user->email }}</div>
            @if($application->user->profile && $application->user->profile->phone)
                <div class="contact-row">Phone: {{ $application->user->profile->phone }}</div>
            @endif
        </div>
    </div>

    <!-- Application Status -->
    <div class="status-info">
        <strong>Applied:</strong> {{ $application->created_at->format('M d, Y') }} | 
        <strong>Reference:</strong> {{ $application->jobRequisition->reference_number ?? 'N/A' }} | 
        @auth
        @if(auth()->user()->isHrAdmin())
            <span class="status-badge">{{ ucfirst($application->status) }}</span>
        @endif
    @endauth
        </div>


    <!-- Experience Section -->
    @if($application->user->experiences && $application->user->experiences->count())
    <div class="section">
        <div class="section-title">Professional Experience</div>
        @foreach($application->user->experiences as $exp)
        <div class="job-entry">
            <div class="job-title-entry">{{ $exp->job_title ?? 'Job Title' }}</div>
            <div class="company-name">{{ $exp->company ?? 'Company Name' }}</div>
            <div class="date-range">{{ \Carbon\Carbon::parse($exp->start_date)->format('M Y') ?? '' }} - {{ \Carbon\Carbon::parse($exp->end_date)->format('M Y') ?? 'Present' }}</div>
            <div class="description">{{ $exp->description ?? 'Key responsibilities and achievements in this role.' }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Education Section -->
    @if($application->user->education && $application->user->education->count())
    <div class="section">
        <div class="section-title">Education</div>
        @foreach($application->user->education as $edu)
        <div class="education-entry">
            <div class="job-title-entry">{{ $edu->degree }}</div>
            <div class="company-name">{{ $edu->institution }}</div>
            <div class="date-range">{{ \Carbon\Carbon::parse($edu->start_date)->format('Y') ?? '' }} - {{ \Carbon\Carbon::parse($edu->end_date)->format('Y') ?? 'Present' }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Skills Section -->
    @if($application->user->skills && $application->user->skills->count())
    <div class="section">
        <div class="section-title">Skills</div>
        <div class="skills-list">
            @foreach($application->user->skills as $skill)
                <span class="skill-item">{{ $skill->name }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Qualifications/Certifications -->
    @if($application->user->qualifications && $application->user->qualifications->count())
    <div class="section">
        <div class="section-title">Certifications</div>
        @foreach($application->user->qualifications as $qualification)
        <div class="qualification-entry">
            <div class="job-title-entry">{{ $qualification->title ?? 'Qualification Title' }}</div>
            <div class="company-name">{{ $qualification->institution ?? 'Institution' }}</div>
            <div class="date-range">{{ $qualification->issued_date ? \Carbon\Carbon::parse($qualification->issued_date)->format('M Y') : 'Year' }}</div>
        </div>
        @endforeach
    </div>
    @endif

  

    <!-- References -->
    @if($application->user->references && $application->user->references->count())
    <div class="section">
        <div class="section-title">References</div>
        <div class="two-column">
            @foreach($application->user->references as $ref)
            <div class="column">
                <div class="reference-item">
                    <div class="reference-name">{{ $ref->name }}</div>
                    <div class="reference-details">
                        {{ $ref->relationship }}<br>
                        {{ $ref->company }}<br>
                        Email: {{ $ref->email }}<br>
                        Phone: {{ $ref->phone }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated on {{ now()->format('F j, Y \a\t g:i A') }} — CBS Recruitment
    </div>
</div>
</body>
</html>