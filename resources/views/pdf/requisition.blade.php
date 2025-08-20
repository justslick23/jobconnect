<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $jobRequisition->title }} - Job Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* A4 PDF Optimized Styles */
        @page {
            size: A4 !important;
            margin: 15mm !important;
        }

        * {
            box-sizing: border-box !important;
        }

        body {
            font-family: 'Roboto', 'Arial', sans-serif !important;
            font-size: 10pt !important;
            line-height: 1.4 !important;
            color: #2c3e50 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            width: 210mm !important;
            min-height: 297mm !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Header Section - Compact A4 Design */
        .pdf-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
            color: white !important;
            padding: 15mm 0 8mm 0 !important;
            margin: -15mm -15mm 8mm -15mm !important;
            position: relative !important;
        }

        .header-content {
            padding: 0 15mm !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }

        .company-logo {
            text-align: center !important;
            margin-bottom: 10mm !important;
            padding: 0 15mm !important;
        }

        .company-logo img {
            max-height: 12mm !important;
            max-width: 80mm !important;
            object-fit: contain !important;
            filter: brightness(0) invert(1) !important;
        }

        .job-info {
            flex: 1 !important;
            margin-right: 10mm !important;
        }

        .job-title {
            font-size: 18pt !important;
            font-weight: 700 !important;
            margin: 0 0 3mm 0 !important;
            line-height: 1.2 !important;
        }

        .job-meta {
            font-size: 9pt !important;
            opacity: 0.9 !important;
            margin: 2mm 0 !important;
        }

        .qr-code-section {
            text-align: center !important;
            background: rgba(255, 255, 255, 0.95) !important;
            padding: 6mm !important;
            border-radius: 4mm !important;
            color: #2c3e50 !important;
            min-width: 25mm !important;
        }

        .qr-code-section img {
            width: 20mm !important;
            height: 20mm !important;
            border-radius: 2mm !important;
        }

        .qr-code-section p {
            font-size: 8pt !important;
            font-weight: 600 !important;
            margin: 2mm 0 0 0 !important;
            color: #3498db !important;
        }

        /* Main Content Area */
        .content-wrapper {
            padding: 0 15mm !important;
        }

        /* Key Details Section */
        .key-details {
            background: #f8f9fa !important;
            border: 1pt solid #dee2e6 !important;
            border-radius: 2mm !important;
            margin-bottom: 8mm !important;
            overflow: hidden !important;
        }

        .details-header {
            background: #495057 !important;
            color: white !important;
            padding: 3mm 5mm !important;
            font-size: 10pt !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5pt !important;
        }

        .details-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 0 !important;
        }

        .detail-item {
            padding: 4mm 5mm !important;
            border-bottom: 0.5pt solid #dee2e6 !important;
            display: flex !important;
            justify-content: space-between !important;
        }

        .detail-item:nth-child(odd) {
            background: #ffffff !important;
            border-right: 0.5pt solid #dee2e6 !important;
        }

        .detail-item:nth-child(even) {
            background: #f8f9fa !important;
        }

        .detail-label {
            font-weight: 600 !important;
            color: #495057 !important;
            font-size: 9pt !important;
        }

        .detail-value {
            color: #212529 !important;
            font-size: 9pt !important;
            font-weight: 400 !important;
        }

        /* Content Sections */
        .section {
            margin-bottom: 8mm !important;
            page-break-inside: avoid !important;
        }

        .section-title {
            font-size: 14pt !important;
            font-weight: 700 !important;
            color: #2c3e50 !important;
            margin-bottom: 3mm !important;
            padding-bottom: 1mm !important;
            border-bottom: 2pt solid #3498db !important;
            position: relative !important;
        }

        .section-title::after {
            content: '' !important;
            position: absolute !important;
            bottom: -2pt !important;
            left: 0 !important;
            width: 15mm !important;
            height: 2pt !important;
            background: #2c3e50 !important;
        }

        .section-content {
            font-size: 10pt !important;
            line-height: 1.5 !important;
            color: #495057 !important;
            text-align: justify !important;
        }

        .section-content p {
            margin: 0 0 3mm 0 !important;
        }

        .section-content ul,
        .section-content ol {
            margin: 0 0 3mm 5mm !important;
            padding: 0 !important;
        }

        .section-content li {
            margin-bottom: 1.5mm !important;
        }

        /* Education Level */
        .education-highlight {
            background: linear-gradient(135deg, #3498db, #2c3e50) !important;
            color: white !important;
            padding: 5mm !important;
            border-radius: 2mm !important;
            margin: 3mm 0 !important;
            text-align: center !important;
        }

        .education-label {
            font-size: 8pt !important;
            text-transform: uppercase !important;
            letter-spacing: 1pt !important;
            opacity: 0.8 !important;
            margin-bottom: 1mm !important;
        }

        .education-value {
            font-size: 12pt !important;
            font-weight: 700 !important;
        }

        /* Tags for Skills and Areas */
        .tags-wrapper {
            margin-top: 3mm !important;
        }

        .tag {
            display: inline-block !important;
            background: #e9ecef !important;
            color: #495057 !important;
            padding: 2mm 3mm !important;
            margin: 0 2mm 2mm 0 !important;
            border-radius: 10pt !important;
            font-size: 8pt !important;
            font-weight: 500 !important;
            border: 0.5pt solid #ced4da !important;
        }

        .tag:nth-child(3n+1) {
            background: #d4edda !important;
            border-color: #c3e6cb !important;
            color: #155724 !important;
        }

        .tag:nth-child(3n+2) {
            background: #d1ecf1 !important;
            border-color: #bee5eb !important;
            color: #0c5460 !important;
        }

        .tag:nth-child(3n) {
            background: #f8d7da !important;
            border-color: #f5c6cb !important;
            color: #721c24 !important;
        }

        /* A4 Print Specific */
        @media print {
            body {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            .pdf-header {
                margin: 0 0 8mm 0 !important;
                padding: 10mm 15mm 6mm 15mm !important;
            }

            .section {
                page-break-inside: avoid !important;
            }

            .section-title {
                page-break-after: avoid !important;
            }

            .key-details,
            .education-highlight {
                page-break-inside: avoid !important;
            }

            @page {
                margin: 0 !important;
            }
        }

        /* Mobile fallback */
        @media (max-width: 600px) {
            body {
                width: 100% !important;
                min-height: auto !important;
            }

            .header-content {
                flex-direction: column !important;
                text-align: center !important;
            }

            .job-info {
                margin-right: 0 !important;
                margin-bottom: 5mm !important;
            }

            .details-grid {
                grid-template-columns: 1fr !important;
            }

            .detail-item:nth-child(odd) {
                border-right: none !important;
            }
        }

        /* Full Width Table Alternative for Details */
        .details-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 0 !important;
        }

        .details-table tr:nth-child(even) {
            background: #f8f9fa !important;
        }

        .details-table tr:nth-child(odd) {
            background: #ffffff !important;
        }

        .details-table th,
        .details-table td {
            padding: 3mm 4mm !important;
            text-align: left !important;
            border: 0.5pt solid #dee2e6 !important;
            vertical-align: top !important;
            font-size: 9pt !important;
        }

        .details-table th {
            font-weight: 600 !important;
            color: #495057 !important;
            background: #e9ecef !important;
            width: 30% !important;
        }

        .details-table td {
            color: #212529 !important;
        }
    </style>
</head>
<body>
    <!-- PDF Header -->
    <div class="pdf-header">
        <!-- Company Logo -->
        <div class="company-logo">
            <img src="{{ asset('assets/img/CBS logo.jpg') }}" alt="Company Logo">
        </div>

        <!-- Header Content -->
        <div class="header-content">
            <div class="job-info">
                <h1 class="job-title">{{ $jobRequisition->title }}</h1>
                <div class="job-meta">
                    <strong>Department:</strong> {{ $jobRequisition->department->name ?? 'N/A' }} | 
                    <strong>Ref:</strong> {{ $jobRequisition->reference_number ?? 'N/A' }}
                </div>
            </div>
            <div class="qr-code-section">
                <img src="{{ $qrCodeUrl }}" alt="QR Code">
                <p>Scan to Apply</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Key Details -->
        <div class="key-details">
            <div class="details-header">Position Information</div>
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
        </div>

        <!-- About This Role -->
        @if($jobRequisition->description)
        <div class="section">
            <h2 class="section-title">About This Role</h2>
            <div class="section-content">{!! $jobRequisition->description !!}</div>
        </div>
        @endif

        <!-- Requirements -->
        @if($jobRequisition->requirements)
        <div class="section">
            <h2 class="section-title">Requirements</h2>
            <div class="section-content">{!! $jobRequisition->requirements !!}</div>
        </div>
        @endif

        <!-- Education Level -->
        @if($jobRequisition->education_level)
        <div class="section">
            <h2 class="section-title">Education Requirements</h2>
            <div class="education-highlight">
                <div class="education-label">Minimum Required Level</div>
                <div class="education-value">{{ $jobRequisition->education_level }}</div>
            </div>
        </div>
        @endif

        <!-- Skills -->
        @if($jobRequisition->skills && $jobRequisition->skills->count())
        <div class="section">
            <h2 class="section-title">Required Skills</h2>
            <div class="tags-wrapper">
                @foreach($jobRequisition->skills as $skill)
                    <span class="tag">{{ $skill->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Areas of Study -->
        @if($jobRequisition->required_areas_of_study && !empty($jobRequisition->required_areas_of_study))
        <div class="section">
            <h2 class="section-title">Areas of Study</h2>
            <div class="tags-wrapper">
                @foreach($jobRequisition->required_areas_of_study as $area)
                    <span class="tag">{{ is_array($area) ? ($area['name'] ?? $area['title'] ?? 'Unknown') : $area }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</body>
</html>