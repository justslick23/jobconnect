<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $jobRequisition->title }} - Job Details</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333;
            margin: 25px;
        }

        /* Company Logo */
        .page-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .page-logo img {
            max-height: 80px;
            object-fit: contain;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 12px;
        }

        .header-left {
            max-width: 65%;
        }

        .header-left h1 {
            font-size: 22px;
            font-weight: 400;
            margin: 0 0 5px 0;
            color: #1a1a1a;
        }

        .header-left p {
            margin: 2px 0;
            font-size: 12px;
            color: #555;
        }

        .qr-code {
            text-align: center;
            width: 100px;
        }

        .qr-code img {
            border: 1px solid #ddd;
            padding: 5px;
        }

        .qr-code p {
            font-size: 9px;
            margin-top: 5px;
            color: #777;
        }

        /* Section Titles */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0056b3;
            margin-top: 25px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .content {
            margin: 10px 0 15px 0;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            width: 25%;
            font-weight: 600;
            color: #555;
        }

        tr:last-child td, tr:last-child th {
            border-bottom: none;
        }

        /* Badges for Skills and Areas of Study */
        .badge {
            display: inline-block;
            background-color: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            margin: 3px 3px 3px 0;
            font-size: 10px;
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
