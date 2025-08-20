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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .pdf-header {
            border-bottom: 2px solid #000;
            padding-bottom: 8mm;
            margin-bottom: 12mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-logo img {
            max-height: 40px;
        }

        .job-info h1 {
            font-size: 18pt;
            margin: 0 0 3mm 0;
        }

        .job-meta {
            font-size: 10pt;
            color: #444;
        }

        .qr-code img {
            width: 70px;
            height: 70px;
        }

        /* Section Titles */
        .section-title {
            font-size: 13pt;
            font-weight: bold;
            margin: 15px 0 8px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        /* Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .details-table th,
        .details-table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            font-size: 10pt;
            vertical-align: top;
        }

        .details-table th {
            background: #f2f2f2;
            text-align: left;
            width: 30%;
        }

        /* Content */
        .section-content {
            font-size: 10.5pt;
            text-align: justify;
        }

        .section-content p {
            margin: 0 0 8px 0;
        }

        /* Tags (skills, areas) */
        .tags-wrapper {
            margin-top: 5px;
        }

        .tag {
            display: inline-block;
            border: 1px solid #ccc;
            padding: 3px 8px;
            margin: 2px;
            font-size: 9pt;
            border-radius: 4px;
            background: #f9f9f9;
        }

        .qr-code img {
    width: 150px;
    height: 150px;
}


        /* Print overrides */
        @media print {
            .pdf-header {
                border-bottom: 1px solid #000;
                margin-bottom: 10mm;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="pdf-header">
        <div class="company-logo">
            <img src="{{ public_path('assets/img/CBS logo.jpg') }}" alt="Company Logo" width="120">
        </div>
        <div class="job-info">
            <h1>{{ $jobRequisition->title }}</h1>
            <div class="job-meta">
                <strong>Department:</strong> {{ $jobRequisition->department->name ?? 'N/A' }} |
                <strong>Ref:</strong> {{ $jobRequisition->reference_number ?? 'N/A' }}
            </div>
        </div>
        <div class="qr-code" style="text-align: right;">
            <img src="{{ $qrCodeUrl }}" alt="QR Code" width="150"><br>
        </div>
        

    <!-- Key Details -->
    <h2 class="section-title">Position Information</h2>
    <table class="details-table">
        <tr>
            <th>Available Positions</th>
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
            <th>Posted Date</th>
            <td>{{ $jobRequisition->created_at->format('M j, Y') }}</td>
        </tr>
        @if($jobRequisition->application_deadline)
        <tr>
            <th>Application Deadline</th>
            <td>{{ $jobRequisition->application_deadline->format('M j, Y') }}</td>
        </tr>
        @endif
    </table>

    <!-- Description -->
    @if($jobRequisition->description)
    <h2 class="section-title">About This Role</h2>
    <div class="section-content">{!! $jobRequisition->description !!}</div>
    @endif

    <!-- Requirements -->
    @if($jobRequisition->requirements)
    <h2 class="section-title">Requirements</h2>
    <div class="section-content">{!! $jobRequisition->requirements !!}</div>
    @endif

    <!-- Education -->
    @if($jobRequisition->education_level)
    <h2 class="section-title">Education Requirements</h2>
    <p><strong>{{ $jobRequisition->education_level }}</strong></p>
    @endif

    <!-- Skills -->
    @if($jobRequisition->skills && $jobRequisition->skills->count())
    <h2 class="section-title">Required Skills</h2>
    <div class="tags-wrapper">
        @foreach($jobRequisition->skills as $skill)
            <span class="tag">{{ $skill->name }}</span>
        @endforeach
    </div>
    @endif

    <!-- Areas of Study -->
    @if($jobRequisition->required_areas_of_study && !empty($jobRequisition->required_areas_of_study))
    <h2 class="section-title">Areas of Study</h2>
    <div class="tags-wrapper">
        @foreach($jobRequisition->required_areas_of_study as $area)
            <span class="tag">{{ is_array($area) ? ($area['name'] ?? $area['title'] ?? 'Unknown') : $area }}</span>
        @endforeach
    </div>
    @endif
</body>
</html>
