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
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
            position: relative;
        }

        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-logo img {
            max-height: 80px;
        }

        .job-info {
            flex-grow: 1;
            padding: 0 15px;
        }

        .job-info h1 {
            font-size: 20px;
            margin: 0;
        }

        .job-meta {
            font-size: 11px;
            color: #555;
        }

        .qr-code-header {
            text-align: center;
            flex: 0 0 120px;
        }

        .qr-code-header img {
            width: 100px;
            height: 100px;
            margin-bottom: 5px;
        }

        .qr-code-header .qr-label {
            font-size: 8pt;
            color: #555;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h2 {
            font-size: 14px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
            color: #222;
        }

        .section p, .section ul {
            margin: 0 0 10px;
        }

        ul {
            padding-left: 20px;
        }

        .footer {
            position: absolute;
            bottom: 10mm;
            left: 0;
            right: 0;
            font-size: 10px;
            text-align: center;
            color: #777;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 35%;
            left: 20%;
            font-size: 60px;
            color: rgba(200,200,200,0.15);
            transform: rotate(-30deg);
            z-index: -1;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">CBS Recruitment</div>

    <!-- Header -->
    <div class="pdf-header">
        <div class="company-logo">
            <img src="{{ public_path('assets/img/CBS logo.png') }}" alt="Company Logo">
        </div>

        <div class="job-info">
            <h1>{{ $jobRequisition->title }}</h1>
            <div class="job-meta">
                <strong>Department:</strong> {{ $jobRequisition->department->name ?? 'N/A' }} |
                <strong>Ref:</strong> {{ $jobRequisition->reference_number ?? 'N/A' }} |
                <strong>Deadline:</strong> {{ $jobRequisition->application_deadline->format('M j, Y H:i') }}
            </div>
        </div>

        <div class="qr-code-header">
            <img src="{{ $qrCodeUrl }}" alt="QR Code">
            <div class="qr-label">Scan to Apply</div>
        </div>
    </div>

    <!-- Job Description -->
    <div class="section">
        <h2>Job Description</h2>
        <p>{!! nl2br(e($jobRequisition->description)) !!}</p>
    </div>

    <!-- Requirements -->
    <div class="section">
        <h2>Requirements</h2>
        <p>{!! nl2br(e($jobRequisition->requirements)) !!}</p>
    </div>

    <!-- Required Areas of Study -->
    @if(!empty($jobRequisition->required_areas_of_study))
    <div class="section">
        <h2>Required Areas of Study</h2>
        <ul>
            @foreach($jobRequisition->required_areas_of_study as $area)
                <li>{{ $area }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Skills -->
    @if(!empty($jobRequisition->skills))
    <div class="section">
        <h2>Skills</h2>
        <ul>
            @foreach($jobRequisition->skills as $skill)
                <li>{{ $skill }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} CBS Recruitment. All rights reserved.
    </div>
</body>
</html>
