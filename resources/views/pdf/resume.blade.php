<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Resume</title>
<style>
    @page {
        size: A4 portrait;
        margin: 15mm;
    }
    html, body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 0;
        font-family: 'Roboto', sans-serif;
        font-size: 11px;
        line-height: 1.5;
        background: white;
        color: #2c3e50;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .resume-container {
        max-width: 100%;
        padding: 25px;
        position: relative;
    }
    
    /* Accent color strip */
    .accent-strip {
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(180deg, #3498db 0%, #2980b9 100%);
    }
    
    .header {
        margin-bottom: 35px;
        padding: 25px 0 25px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #e3f2fd 100%);
        border-radius: 8px;
        position: relative;
    }
    
    .name {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #2c3e50;
        letter-spacing: 0.5px;
    }
    
    .job-title {
        font-size: 16px;
        color: #3498db;
        margin-bottom: 15px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .contact-info {
        font-size: 11px;
        color: #5d6d7e;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .contact-item {
        position: relative;
        padding-left: 18px;
    }
    
    .contact-item::before {
        position: absolute;
        left: 0;
        color: #3498db;
        font-weight: bold;
    }
    
    .section {
        margin-bottom: 30px;
        padding-left: 20px;
    }
    
    .section-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 18px;
        color: #2c3e50;
        position: relative;
        padding-bottom: 8px;
        letter-spacing: 0.8px;
    }
    
    .section-title::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, #3498db, #85c1e9);
        border-radius: 2px;
    }
    
    .job-entry, .education-entry, .qualification-entry {
        margin-bottom: 20px;
        padding: 15px;
        background: #fafbfc;
        border-radius: 6px;
        border-left: 3px solid #ecf0f1;
        position: relative;
    }
    
    .job-entry:hover, .education-entry:hover, .qualification-entry:hover {
    }
    
    .entry-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    
    .job-title-entry {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 3px;
        color: #2c3e50;
    }
    
    .company-name {
        font-style: italic;
        color: #7f8c8d;
        margin-bottom: 5px;
        font-size: 12px;
    }
    
    .date-range {
        color: #5d6d7e;
        font-size: 10px;
        font-weight: 500;
        background: #e8f4f8;
        padding: 4px 8px;
        border-radius: 12px;
        white-space: nowrap;
        border: 1px solid #d6eaf8;
    }
    
    .description {
        margin-top: 8px;
        color: #34495e;
        line-height: 1.6;
        text-align: justify;
    }
    
    .skills-container {
        background: #f8fafc;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e8f4f8;
    }
    
    .skills-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        line-height: 1.8;
    }
    
    .skill-item {
        background: #3498db;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: none;
        display: inline-block;
    }
    
    .two-column {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }
    
    .reference-item {
        background: #f8fafc;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #e8f4f8;
        margin-bottom: 15px;
    }
    
    .reference-name {
        font-weight: 600;
        margin-bottom: 8px;
        color: #2c3e50;
        font-size: 12px;
    }
    
    .reference-details {
        font-size: 10px;
        color: #5d6d7e;
        line-height: 1.5;
    }
    
    .reference-details > div {
        margin-bottom: 3px;
    }
    
    .status-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 10px;
        color: #7f8c8d;
        margin-bottom: 25px;
        padding: 12px 20px;
        background: #f8fafc;
        border-radius: 6px;
        border: 1px solid #e8f4f8;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .status-left, .status-right {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .status-badge {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        text-transform: capitalize;
        font-size: 9px;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .footer {
        text-align: center;
        font-size: 9px;
        color: #95a5a6;
        margin-top: 35px;
        padding-top: 20px;
        border-top: 2px solid #ecf0f1;
        position: relative;
    }
    
    .footer::before {
        content: "";
        position: absolute;
        top: -1px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 2px;
        background: #3498db;
        border-radius: 1px;
    }
    
    .profile-summary {
        text-align: justify;
        color: #34495e;
        line-height: 1.6;
        background: #f8fafc;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    /* Enhanced typography */
    strong {
        color: #2c3e50;
        font-weight: 600;
    }
    
    /* Print optimizations */
    @media print {
        .resume-container {
            padding: 15px;
        }
        .job-entry, .education-entry, .qualification-entry, .reference-item {
            break-inside: avoid;
        }
        .section {
            break-inside: avoid-column;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    
    /* Responsive adjustments for smaller screens */
    @media screen and (max-width: 768px) {
        .two-column {
            grid-template-columns: 1fr;
        }
        .contact-info {
            flex-direction: column;
            gap: 8px;
        }
        .status-info {
            flex-direction: column;
            align-items: flex-start;
        }
        .entry-header {
            flex-direction: column;
        }
        .date-range {
            align-self: flex-start;
            margin-top: 5px;
        }
    }
</style>
</head>
<body>
<div class="resume-container">
    <div class="accent-strip"></div>
    
    <!-- Header Section -->
    <div class="header">
        <div class="name">{{ strtoupper($application->user->name ?? 'Applicant Name') }}</div>
        <div class="job-title">{{ $application->jobRequisition->title ?? 'Job Title' }}</div>
        <div class="contact-info">
            <div class="contact-item">Email: {{ $application->user->email }}</div>
            @if($application->user->profile && $application->user->profile->phone)
                <div class="contact-item">Phone: {{ $application->user->profile->phone }}</div>
            @endif
        </div>
    </div>

   

    <!-- Experience Section -->
    @if($application->user->experiences && $application->user->experiences->count())
    <div class="section">
        <div class="section-title">Professional Experience</div>
        @foreach($application->user->experiences as $exp)
        <div class="job-entry">
            <div class="entry-header">
                <div>
                    <div class="job-title-entry">{{ $exp->job_title ?? 'Job Title' }}</div>
                    <div class="company-name">{{ $exp->company ?? 'Company Name' }}</div>
                </div>
                <div class="date-range">
                    {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('M Y') : '' }} - 
                    {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : 'Present' }}
                </div>
            </div>
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
            <div class="entry-header">
                <div>
                    <div class="job-title-entry">{{ $edu->degree }}</div>
                    <div class="company-name">{{ $edu->institution }}</div>
                </div>
                <div class="date-range">
                    {{ $edu->start_date ? \Carbon\Carbon::parse($edu->start_date)->format('Y') : '' }} - 
                    {{ $edu->end_date ? \Carbon\Carbon::parse($edu->end_date)->format('Y') : 'Present' }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Skills Section -->
    @if($application->user->skills && $application->user->skills->count())
    <div class="section">
        <div class="section-title">Skills</div>
        <div class="skills-container">
            <div class="skills-list">
                @foreach($application->user->skills as $skill)
                    <span class="skill-item">{{ $skill->name }}</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Qualifications/Certifications -->
    @if($application->user->qualifications && $application->user->qualifications->count())
    <div class="section">
        <div class="section-title">Certifications</div>
        @foreach($application->user->qualifications as $qualification)
        <div class="qualification-entry">
            <div class="entry-header">
                <div>
                    <div class="job-title-entry">{{ $qualification->title ?? 'Qualification Title' }}</div>
                    <div class="company-name">{{ $qualification->institution ?? 'Institution' }}</div>
                </div>
                <div class="date-range">
                    {{ $qualification->issued_date ? \Carbon\Carbon::parse($qualification->issued_date)->format('M Y') : 'Year' }}
                </div>
            </div>
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
            <div class="reference-item">
                <div class="reference-name">{{ $ref->name }}</div>
                <div class="reference-details">
                    <div><strong>Position:</strong> {{ $ref->relationship }}</div>
                    <div><strong>Company:</strong> {{ $ref->company }}</div>
                    <div><strong>Email:</strong> {{ $ref->email }}</div>
                    <div><strong>Phone:</strong> {{ $ref->phone }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated on {{ now()->format('F j, Y \a\t g:i A') }} — CBS Recruitment System
    </div>
</div>
</body>
</html>