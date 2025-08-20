<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $jobRequisition->title }} - Job Details</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20mm;
            background: #fff;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        /* Company Logo */
        .page-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-logo img {
            max-height: 70px;
            object-fit: contain;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .header-left {
            max-width: 65%;
        }

        .header-left h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #1a1a1a;
            line-height: 1.3;
        }

        .header-left p {
            margin: 3px 0;
            font-size: 12px;
            color: #666;
        }

        .qr-code {
            text-align: center;
            width: 100px;
        }

        .qr-code img {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }

        .qr-code p {
            font-size: 10px;
            margin-top: 8px;
            color: #666;
            font-weight: 500;
        }

        /* Section Titles */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 30px 0 15px 0;
        }

        .content {
            margin: 0 0 20px 0;
            color: #444;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: #fff;
            page-break-inside: avoid;
        }

        th, td {
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        th {
            background-color: #f8f8f8;
            width: 25%;
            font-weight: 600;
            color: #333;
            font-size: 11px;
        }

        td {
            font-size: 11px;
            color: #333;
        }

        /* Education Level Section */
        .education-level {
            background: #f8f8f8;
            padding: 12px 15px;
            border: 1px solid #ddd;
            margin: 10px 0;
            page-break-inside: avoid;
        }

        .education-level strong {
            color: #333;
            font-weight: 600;
        }

        /* Badges */
        .badge {
            display: inline-block;
            background-color: #f0f0f0;
            color: #333;
            padding: 4px 8px;
            border: 1px solid #ccc;
            margin: 2px 2px 2px 0;
            font-size: 10px;
            font-weight: normal;
        }

        /* Section Management */
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 20px 0 10px 0;
            page-break-after: avoid;
        }

        .content {
            margin: 0 0 15px 0;
            color: #444;
            page-break-inside: avoid;
            orphans: 3;
            widows: 3;
        }

        /* PDF Page Control */
        .page-break-before {
            page-break-before: always;
        }

        .page-break-after {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
        @media print {
            body {
                margin: 20px;
            }
            
            table {
                background: white;
            }
            
            th {
                background-color: #f8f8f8;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
            }
            
            .header-left {
                max-width: 100%;
                margin-bottom: 20px;
            }
            
            .qr-code {
                align-self: center;
            }
        }
    </style>
</head>
<body>
    <!-- Company Logo -->
    <div class="page-logo">
        <img src="{{ asset('images/company-logo.png') }}" alt="Company Logo">
    </div>

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h1>{{ $jobRequisition->title }}</h1>
            <p><strong>Department:</strong> {{ $jobRequisition->department->name ?? 'N/A' }}</p>
            <p><strong>Reference:</strong> {{ $jobRequisition->reference_number ?? 'N/A' }}</p>
        </div>
        <div class="qr-code">
            <img src="{{ $qrCodeUrl }}" alt="QR Code" width="100" height="100">
            <p>Scan to Apply</p>
        </div>
    </div>

    <!-- Key Details Table -->
    <div class="no-break">
    <table>
        <tr>
            <th>Positions</th>
            <td>{{ $jobRequisition->vacancies ?? 1 }}</td>
        </tr>
        <tr>
            <th>Employment Type</th>
            <td>{{ $jobRequisition->employment_type ?? 'Full-time' }}</td>
        </tr>
        <tr>
            <th>Location</th>
            <td>{{ $jobRequisition->location ?? 'Remote' }}</td>
        </tr>
        <tr>
            <th>Posted</th>
            <td>{{ $jobRequisition->created_at->format('M j, Y') }}</td>
        </tr>
        @if($jobRequisition->application_deadline)
        <tr>
            <th>Deadline</th>
            <td>{{ $jobRequisition->application_deadline->format('M j, Y') }}</td>
        </tr>
        @endif
    </table>
    </div>

    <!-- About This Role -->
    @if($jobRequisition->description)
    <div class="section-title">About This Role</div>
    <div class="content">{!! $jobRequisition->description !!}</div>
    @endif

    <!-- Requirements -->
    @if($jobRequisition->requirements)
    <div class="section-title">Requirements</div>
    <div class="content">{!! $jobRequisition->requirements !!}</div>
    @endif

    <!-- Education Level -->
    @if($jobRequisition->education_level)
    <div class="section-title">Education Level</div>
    <div class="education-level">
        <strong>Required:</strong> {{ $jobRequisition->education_level }}
    </div>
    @endif

    <!-- Skills -->
    @if($jobRequisition->skills && $jobRequisition->skills->count())
    <div class="section-title">Required Skills</div>
    <div class="content">
        @foreach($jobRequisition->skills as $skill)
            <span class="badge">{{ $skill->name }}</span>
        @endforeach
    </div>
    @endif

    <!-- Areas of Study -->
    @if($jobRequisition->required_areas_of_study && !empty($jobRequisition->required_areas_of_study))
    <div class="section-title">Areas of Study</div>
    <div class="content">
        @foreach($jobRequisition->required_areas_of_study as $area)
            <span class="badge">{{ is_array($area) ? ($area['name'] ?? $area['title'] ?? 'Unknown') : $area }}</span>
        @endforeach
    </div>
    @endif
</body>
</html>