<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $jobRequisition->title }} - Job Details</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0066cc;
        }

        .tagline {
            font-size: 12pt;
            font-style: italic;
            color: #333333;
            margin-bottom: 5px;
        }

        .company-logo {
            margin: 15px 0;
        }

        .company-logo img {
            max-height: 80px;
            max-width: 200px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }

        .company-info {
            font-size: 9pt;
            color: #333333;
            line-height: 1.4;
            margin-top: 10px;
        }

        /* Title Section */
        .job-title-section {
            background-color: #e6f2ff;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #0066cc;
        }

        .job-title-section h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #000000;
        }

        .job-meta {
            font-size: 10pt;
            line-height: 1.5;
        }

        .job-meta strong {
            font-weight: bold;
        }

        /* Content Sections */
        .section {
            margin-bottom: 20px;
        }

        .section h2 {
            font-size: 12pt;
            font-weight: bold;
            color: #0066cc;
            margin: 0 0 10px 0;
            text-decoration: underline;
        }

        .section p {
            margin: 0 0 10px 0;
            text-align: justify;
        }

        .section ul {
            margin: 0 0 10px 0;
            padding-left: 25px;
        }

        .section li {
            margin-bottom: 5px;
        }

        /* Info Box */
        .info-box {
            background-color: #f9f9f9;
            border: 1px solid #cccccc;
            padding: 15px;
            margin: 15px 0;
        }

        .info-box h3 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0 0 10px 0;
        }

        /* Tags */
        .tags-section {
            margin-top: 10px;
        }

        .tag {
            display: inline-block;
            background: #ffffff;
            border: 1px solid #000000;
            padding: 4px 10px;
            margin: 3px 3px 3px 0;
            font-size: 9pt;
        }

        /* QR Code */
        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #0066cc;
            background-color: #f0f8ff;
        }

        .qr-section h3 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 15px 0;
        }

        .qr-section img {
            width: 120px;
            height: 120px;
            border: 2px solid #0066cc;
        }

        .qr-caption {
            font-size: 9pt;
            margin-top: 10px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 9pt;
            padding: 10px;
            border-top: 2px solid #0066cc;
            background-color: #ffffff;
        }

        /* Deadline Box */
        .deadline-box {
            background-color: #fff3cd;
            border: 2px solid #ff9800;
            padding: 10px;
            margin: 15px 0;
            font-weight: bold;
            text-align: center;
            color: #856404;
        }

        /* Print Optimizations */
        @media print {
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        
        <div class="company-logo">
            <img src="{{ public_path('assets/img/logo.png') }}" alt="CBS Logo">
        </div>
        
        <div class="company-name">Computer Business Solutions</div>
        
        <div class="company-info">
            HEAD-OFFICE: 4th Floor, Post Office Building, Kingsway, Maseru, Lesotho<br>
            P.O. Box 10659, Maseru 100, Lesotho<br>
            www.cbs.co.ls
        </div>
    </div>

    <!-- Job Title Section -->
    <div class="job-title-section">
        <h1>Career Opportunity - {{ $jobRequisition->title }}</h1>
        <div class="job-meta">
            <div><strong>Company:</strong> Computer Business Solutions (CBS)</div>
            <div><strong>Location:</strong> {{ $jobRequisition->location ?? 'Maseru, Lesotho' }}</div>
            <div><strong>Department:</strong> {{ $jobRequisition->department->name ?? 'N/A' }}</div>
            <div><strong>Employment Type:</strong> {{ ucwords(str_replace('-', ' ', $jobRequisition->employment_type)) }}</div>
        </div>
    </div>

    <!-- About CBS -->
    <div class="section">
        <h2>About CBS</h2>
        <p>Computer Business Solutions (CBS) is a leading IT company in Lesotho with a 27-year legacy of innovation and reliability. We are entering an exciting new phase as a digital transformation partner of choice, offering cutting-edge software development, digital infrastructure solutions, and corporate training services.</p>
    </div>

    <!-- What We Offer -->
    <div class="section">
        <h2>What We Offer</h2>
        <ul>
            <li>A chance to shape the future of digital business solutions in Lesotho and beyond.</li>
            <li>An inclusive, value-driven work environment.</li>
            <li>Competitive compensation package including performance incentives and potential participation in shareholding schemes.</li>
        </ul>
    </div>

    <!-- Key Responsibilities -->
    <div class="section">
        <h2>Key Responsibilities</h2>
        <p>{!! $jobRequisition->description !!}</p>
    </div>

    <!-- Requirements -->
    @if($jobRequisition->requirements)
    <div class="section">
        <h2>Requirements</h2>
        <p>{!! $jobRequisition->requirements !!}</p>
    </div>
    @endif

    <!-- Qualifications -->
    <div class="section">
        <h2>Qualifications</h2>
        <ul>
            <li><strong>Education Level:</strong> {{ $jobRequisition->education_level ?? 'N/A' }}</li>
            @if(!empty($jobRequisition->required_areas_of_study))
            <li><strong>Required Areas of Expertise:</strong> 
                {{ collect($jobRequisition->required_areas_of_study)
                    ->map(fn($area) => is_array($area) ? ($area['name'] ?? $area['title'] ?? $area) : $area)
                    ->implode(', ') }}
            </li>
            @endif
        </ul>
    </div>

    <!-- Skills -->
    @if(!empty($jobRequisition->skills) && $jobRequisition->skills->count())
    <div class="section">
        <h2>Required Skills & Competencies</h2>
        <div class="tags-section">
            @foreach($jobRequisition->skills as $skill)
                <span class="tag">{{ $skill->name }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Experience -->
    @if($jobRequisition->min_experience)
    <div class="section">
        <h2>Experience</h2>
        <p>Minimum of {{ $jobRequisition->min_experience }} year(s) of relevant professional experience.</p>
    </div>
    @endif

    <!-- How to Apply -->
    <div class="section">
        <h2>How to Apply</h2>
        <p>Interested candidates should follow the link below, and attach application letter, CV, certified copies of certificates & transcripts (please attach them as one PDF document):</p>
        
        <div class="qr-section">
            <h3>Scan QR Code to Apply Online</h3>
            <img src="{{ $qrCodeUrl }}" alt="Application QR Code">
            <div class="qr-caption">Or visit: {{ route('job-applications.create', $jobRequisition->id) }}</div>
        </div>

        <p>For further clarification, please e-mail <strong>recruitment@cbs.co.ls</strong></p>
        
        @if($jobRequisition->application_deadline)
        <div class="deadline-box">
            The deadline for submission of applications is {{ $jobRequisition->application_deadline->format('jS F Y') }}.
        </div>
        @endif

        <div class="info-box">
            <p style="margin: 0;"><strong>NOTE:</strong> Only shortlisted candidates will be contacted. If you have not heard from us within 5 working days after deadline, please consider your application unsuccessful.</p>
        </div>

        <p style="margin-top: 15px;"><em>CBS is an equal opportunity employer. We encourage applications from qualified individuals regardless of gender, race, or background.</em></p>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} Computer Business Solutions • All Rights Reserved • Generated {{ date('jS F Y') }}
    </div>
</body>
</html>