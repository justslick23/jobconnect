<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $jobRequisition->title }} - Job Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
            margin: 20px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        header img {
            max-height: 60px;
        }
        h1, h2 {
            margin: 0 0 10px;
            color: #222;
        }
        h1 {
            font-size: 16pt;
            margin-bottom: 15px;
        }
        h2 {
            font-size: 13pt;
            border-bottom: 1px solid #aaa;
            padding-bottom: 3px;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 6px 8px;
            border: 1px solid #ccc;
            font-size: 10pt;
        }
        th {
            background: #f5f5f5;
            text-align: left;
        }
        .section {
            margin-bottom: 15px;
        }
        .tags {
            margin-top: 5px;
        }
        .tag {
            display: inline-block;
            background: #eee;
            border: 1px solid #ccc;
            padding: 3px 6px;
            margin: 2px;
            font-size: 9pt;
            border-radius: 3px;
        }
        footer {
            margin-top: 30px;
            text-align: center;
        }
        footer img {
            margin-top: 10px;
            max-height: 120px;
        }
    </style>
</head>
<body>
    <!-- Header with Logo -->
    <header>
        <div>
            <h1>{{ $jobRequisition->title }}</h1>
            <p><small>Job Reference: {{ $jobRequisition->reference_number ?? 'N/A' }}</small></p>
        </div>
        <div>
            <img src="{{ asset('assets/img/CBS logo.jpg') }}" alt="Company Logo">
        </div>
    </header>

    <table>
        <tr>
            <th>Department</th>
            <td>{{ $jobRequisition->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Reference No</th>
            <td>{{ $jobRequisition->reference_number ?? 'N/A' }}</td>
        </tr>
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

    @if($jobRequisition->description)
    <div class="section">
        <h2>About This Role</h2>
        <div>{!! $jobRequisition->description !!}</div>
    </div>
    @endif

    @if($jobRequisition->requirements)
    <div class="section">
        <h2>Requirements</h2>
        <div>{!! $jobRequisition->requirements !!}</div>
    </div>
    @endif

    @if($jobRequisition->education_level)
    <div class="section">
        <h2>Education Level</h2>
        <p><strong>{{ $jobRequisition->education_level }}</strong></p>
    </div>
    @endif

    @if($jobRequisition->skills && $jobRequisition->skills->count())
    <div class="section">
        <h2>Required Skills</h2>
        <div class="tags">
            @foreach($jobRequisition->skills as $skill)
                <span class="tag">{{ $skill->name }}</span>
            @endforeach
        </div>
    </div>
    @endif

    @if($jobRequisition->required_areas_of_study && !empty($jobRequisition->required_areas_of_study))
    <div class="section">
        <h2>Areas of Study</h2>
        <div class="tags">
            @foreach($jobRequisition->required_areas_of_study as $area)
                <span class="tag">{{ is_array($area) ? ($area['name'] ?? $area['title'] ?? 'Unknown') : $area }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Footer with QR Code -->
    <footer>
        <p>Scan the QR code to view/apply online:</p>
        <img src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->size(150)->generate(route('jobs.show', $jobRequisition->uuid))) !!}" alt="QR Code">
    </footer>
</body>
</html>
