<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application Submitted Successfully</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Application Submitted Successfully</h2>

    <p>Hi {{ $application->user->name }},</p>

    <p>
        Thank you for applying for the position: <strong>{{ $application->jobRequisition->job_title }}</strong>.
    </p>

    <p>
        We’ve received your application and our team will review it shortly.
    </p>

    <p style="margin-top: 20px;">
        <a href="{{ route('job-applications.index') }}" 
           style="display: inline-block; padding: 10px 20px; background-color: #3490dc; color: white; text-decoration: none; border-radius: 5px;">
            View My Applications
        </a>
    </p>

    <p style="margin-top: 30px;">
        Best regards,<br>
        {{ config('app.name') }} Careers Team
    </p>
</body>
</html>
