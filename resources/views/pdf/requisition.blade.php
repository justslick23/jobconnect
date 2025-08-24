<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $jobRequisition->title }} - Job Details</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            position: relative;
        }

        /* Simple Clean Header */
        .pdf-header {
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 2px solid #34495e;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-left {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .company-logo {
            margin-right: 25px;
        }

        .company-logo img {
            max-height: 120px;  /* was 60px */
            max-width: 240px;   /* was 120px */
            display: block;
        }


        .job-info h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .job-meta {
            font-size: 12px;
            color: #34495e;
            line-height: 1.4;
        }

        .job-meta strong {
            color: #2c3e50;
        }

        /* Content Sections */
        .content-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }

        .section {
            margin-bottom: 25px;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .section h2 {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #ecf0f1;
            position: relative;
        }

        .section h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 40px;
            height: 2px;
            background: #3498db;
        }

        .section p {
            margin: 0 0 12px;
            text-align: justify;
            color: #34495e;
        }

        .section ul {
            margin: 0 0 12px;
            padding-left: 20px;
        }

        .section li {
            margin-bottom: 6px;
            color: #34495e;
        }

        /* Enhanced Tags */
        .tag {
            display: inline-block;
            background: #ecf0f1;
            color: #2c3e50;
            padding: 6px 12px;
            margin: 3px 3px 3px 0;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 500;
            border: 1px solid #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tag.skill-tag {
            background: #e8f6f3;
            color: #16a085;
            border-color: #16a085;
        }

        .tag.study-tag {
            background: #fef9e7;
            color: #d68910;
            border-color: #d68910;
        }

        /* QR Code Section */
        .qr-section {
            background: #f8f9fa;
            border: 2px dashed #bdc3c7;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin-top: 20px;
        }

        .qr-section h3 {
            font-size: 14px;
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        .qr-section img {
            width: 100px;
            height: 100px;
            border: 3px solid #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .qr-caption {
            font-size: 11px;
            color: #7f8c8d;
            margin-top: 10px;
            font-weight: 500;
        }

        /* Professional Footer */
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            background: #2c3e50;
            color: white;
            padding: 12px 20px;
            font-size: 9px;
            text-align: center;
            margin: 0 -15mm;
        }

        /* Subtle Watermark */
        .watermark {
            position: fixed;
            top: 40%;
            left: 30%;
            width: 40%;
            text-align: center;
            z-index: -1;
            opacity: 0.3;
        }

        .watermark img {
            width: 100%;
            height: auto;
            max-width: 300px;
        }

        /* Info Boxes */
        .info-box {
            border: 1px solid #3498db;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }

        .info-box-header {
            font-weight: 600;
            color: #2980b9;
            margin-bottom: 8px;
            font-size: 12px;
        }

        /* Deadline Highlight */
        .deadline-highlight {
            background: #fff5f5;
            border: 1px solid #e74c3c;
            color: #c0392b;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
            margin-top: 8px;
            font-size: 11px;
        }

        /* Typography Improvements */
        strong {
            font-weight: 600;
            color: #2c3e50;
        }

        em {
            font-style: italic;
            color: #7f8c8d;
        }

        /* Print Optimizations */
        @media print {
            .section {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            .pdf-header {
                break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Subtle Watermark -->
    <div class="watermark">
<img src="{{ public_path('assets/img/logo.png') }}" alt="Company Logo">
    </div>
    
    <!-- Simple Clean Header -->
    <div class="pdf-header">
        <div class="header-left">
            <div class="company-logo">
                <img src="{{ public_path('assets/img/logo.png') }}" alt="CBS Logo">
            </div>
            
            <div class="job-info">
                <h1>{{ $jobRequisition->title }}</h1>
                <div class="job-meta">
                    <div><strong>Department:</strong> {{ $jobRequisition->department->name ?? 'N/A' }}</div>
                    <div><strong>Education Level:</strong> {{ $jobRequisition->education_level ?? 'N/A' }}</div>
                    <div><strong>Required Areas of Study:</strong>    
                        {{ collect($jobRequisition->required_areas_of_study)
                            ->map(fn($area) => is_array($area) ? ($area['name'] ?? $area['title'] ?? $area) : $area)
                            ->implode(' | ') }}
                        
                </div></div>
                    <div class="deadline-highlight">
                        Application Deadline: {{ $jobRequisition->application_deadline->format('M j, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <!-- Job Description -->
        <div class="section">
            <h2>Job Description</h2>
            <p>{!! $jobRequisition->description !!}</p>
        </div>

        <!-- Requirements -->
        <div class="section">
            <h2>Requirements & Qualifications</h2>
            <p>{!! nl2br(e($jobRequisition->requirements)) !!}</p>
        </div>

     

        <!-- Skills -->
        @if(!empty($jobRequisition->skills) && $jobRequisition->skills->count())
        <div class="section">
            <h2>Required Skills & Competencies</h2>
            <div style="margin-top: 10px;">
                @foreach($jobRequisition->skills as $skill)
                    <span class="tag skill-tag">{{ $skill->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- How to Apply -->
        <div class="section">
            <h2>Application Process</h2>
            <p>Ready to join our team? Submit your application through our online portal for immediate processing and confirmation.</p>
            
            <div class="info-box">
                <div class="info-box-header">Quick Application Steps:</div>
                <p style="margin: 0;">
                    1. Scan the QR code below or visit our careers page<br>
                    2. Complete your profile with relevant experience<br>
                    3. Upload your CV and supporting documents<br>
                    4. Submit before the deadline for consideration
                </p>
            </div>
            
            <div class="qr-section">
                <h3>Scan to Apply Online</h3>
                <img src="{{ $qrCodeUrl }}" alt="Application QR Code">
            </div>
        </div>
    </div>

    <!-- Professional Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} CBS Recruitment • All Rights Reserved • Generated {{ date('M j, Y g:i A') }}
    </div>
</body>
</html>