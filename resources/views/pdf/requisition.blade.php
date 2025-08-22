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

        /* Header */
        .pdf-header {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-logo img {
            max-height: 80px;
            display: block;
            margin: 0 auto 10px auto;
        }

        .job-info {
            text-align: center;
        }

        .job-info h1 {
            font-size: 22px;
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .job-meta {
            font-size: 11px;
            color: #555;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h2 {
            font-size: 15px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 10px;
            color: #2980b9;
        }

        .section p, .section ul {
            margin: 0 0 10px;
        }

        ul {
            padding-left: 20px;
        }

        li {
            margin-bottom: 4px;
        }

        .tag {
            display: inline-block;
            background-color: #d6eaf8;
            color: #1b4f72;
            padding: 3px 8px;
            margin: 2px 2px 2px 0;
            border-radius: 4px;
            font-size: 10pt;
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
 /* Watermark */
.watermark {
    position: fixed;
    top: 30%;
    left: 20%;
    width: 60%;
    text-align: center;
    z-index: -1;
    opacity: 0.1; /* faint effect */
}

.watermark img {
    width: 100%;
    height: auto;
}

    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">      <img src="{{ asset('assets/img/CBS logo.png') }}" alt="Company Logo">
    </div>
<!-- Header -->
<div class="pdf-header" style="justify-content: flex-start; text-align: left;">
    <div>
        <div class="company-logo" style="text-align: left;">
            <img src="{{ asset('assets/img/CBS logo.png') }}" alt="Company Logo">
        </div>

        <div class="job-info" style="text-align: left;">
            <h1 style="margin-top: 5px;">{{ $jobRequisition->title }}</h1>
            <div class="job-meta">
                <strong>Department:</strong> {{ $jobRequisition->department->name ?? 'N/A' }} |
                <strong>Deadline:</strong> {{ $jobRequisition->application_deadline->format('M j, Y H:i') }} |
                <strong>Education Level:</strong> {{ $jobRequisition->education_level ?? 'N/A' }}
            </div>
        </div>
    </div>
</div>


    <!-- Job Description -->
    <div class="section">
        <h2>Job Description</h2>
        <p>{!! $jobRequisition->description !!}</p>
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
        @foreach($jobRequisition->required_areas_of_study as $area)
            <span class="tag">{{ is_array($area) ? ($area['name'] ?? $area['title'] ?? $area) : $area }}</span>
        @endforeach
    </div>
    @endif

    <!-- Skills -->
    @if(!empty($jobRequisition->skills) && $jobRequisition->skills->count())
    <div class="section">
        <h2>Skills</h2>
        @foreach($jobRequisition->skills as $skill)
            <span class="tag">{{ $skill->name }}</span>
        @endforeach
    </div>
    @endif

    <!-- How to Apply -->
    <div class="section">
        <h2>How to Apply</h2>
        <p>Scan the QR code below or visit our careers page to submit your application.</p>
        <div style="text-align:center; margin-top:15px;">
            <img src="{{ $qrCodeUrl }}" alt="QR Code" style="width:120px; height:120px;">
            <div style="font-size:10pt; color:#555;">Scan to Apply</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} CBS Recruitment. All rights reserved.
    </div>
</body>
</html>
