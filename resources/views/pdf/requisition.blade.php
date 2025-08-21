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
        }

        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-logo img {
            max-width: 120px;
            height: auto;
        }

        .job-info {
            flex: 1;
            margin-left: 15px;
        }

        .job-info h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #0056b3;
        }

        .job-meta {
            font-size: 10pt;
            color: #555;
        }

        .qr-code-header {
            text-align: center;
            flex: 0 0 150px;
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

        h2 {
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 10px;
            color: #0056b3;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        p {
            margin: 5px 0;
        }

        ul {
            margin: 5px 0 10px 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table td, table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table th {
            background: #f5f5f5;
            text-align: left;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="pdf-header">
        <div class="company-logo">
            <img src="{{ public_path('assets/img/CBS logo.png') }}" alt="Company Logo">
        </div>

        <div class="job-info">
            <h1>{{ $jobRequisition->title }}</h1>
            <div class="job-meta">
                <strong>Department:</strong> {{ $jobRequisition->department->name ?? 'N/A' }} |
                <strong>Ref:</strong> {{ $jobRequisition->reference_number ?? 'N/A' }}
            </div>
        </div>

        <div class="qr-code-header">
            <img src="{{ $qrCodeUrl }}" alt="QR Code">
            <div class="qr-label">Scan to Apply</div>
        </div>
    </div>

    <!-- Job Details -->
    <h2>Job Information</h2>
    <p><strong>Location:</strong> {{ $jobRequisition->location ?? 'N/A' }}</p>
    <p><strong>Employment Type:</strong> {{ ucfirst($jobRequisition->employment_type ?? 'N/A') }}</p>
    <p><strong>Application Deadline:</strong> 
        {{ $jobRequisition->application_deadline 
            ? $jobRequisition->application_deadline->format('M j, Y H:i') 
            : 'N/A' }}
    </p>

    <!-- Description -->
    <h2>Job Description</h2>
    <p>{!! nl2br(e($jobRequisition->description)) !!}</p>

    <!-- Responsibilities -->
    @if(!empty($jobRequisition->responsibilities))
        <h2>Key Responsibilities</h2>
        <ul>
            @foreach(explode("\n", $jobRequisition->responsibilities) as $resp)
                @if(trim($resp) !== '')
                    <li>{{ trim($resp) }}</li>
                @endif
            @endforeach
        </ul>
    @endif

    <!-- Requirements -->
    @if(!empty($jobRequisition->requirements))
        <h2>Requirements</h2>
        <ul>
            @foreach(explode("\n", $jobRequisition->requirements) as $req)
                @if(trim($req) !== '')
                    <li>{{ trim($req) }}</li>
                @endif
            @endforeach
        </ul>
    @endif

    <!-- Qualifications -->
    @if(!empty($jobRequisition->qualifications))
        <h2>Qualifications</h2>
        <ul>
            @foreach(explode("\n", $jobRequisition->qualifications) as $qual)
                @if(trim($qual) !== '')
                    <li>{{ trim($qual) }}</li>
                @endif
            @endforeach
        </ul>
    @endif

</body>
</html>
