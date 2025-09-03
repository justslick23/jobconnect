<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Export</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        .details {
            margin: 20px 0;
            padding: 15px;
            background: #f4f6f8;
            border-radius: 6px;
        }
        .details p {
            margin: 6px 0;
        }
        .footer {
            margin-top: 25px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h2>📄 Application Export Ready</h2>

        <p>Hello,</p>

        <p>The job applications for the requisition below have been exported successfully.  
           Please find the Excel file attached.</p>

        <div class="details">
            <p><strong>Job Title:</strong> {{ $jobTitle }}</p>
            <p><strong>Reference:</strong> {{ $jobReference }}</p>
            <p><strong>Total Applications:</strong> {{ $applicationCount }}</p>
            <p><strong>Export Date:</strong> {{ $exportDate }}</p>
        </div>

        <p>If you have any issues opening the file, please contact the system administrator.</p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
