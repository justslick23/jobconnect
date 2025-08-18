<!DOCTYPE html>
<html>
<head>
    <title>Shortlisted for Interview</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Congratulations {{ $application->user->name ?? 'Candidate' }},</h2>

    <p>You have been <strong>shortlisted</strong> for the position of <strong>{{ $application->jobRequisition->title ?? 'N/A' }}</strong>.</p>

    <p>Your interview is scheduled as follows:</p>

    <ul>
        <li><strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($interview->interview_date)->format('l, F j, Y h:i A') }}</li>
        <li><strong>Location:</strong> {{ $application->jobRequisition->location ?? 'To be communicated' }}</li>
    </ul>

    <p>Please make necessary preparations and ensure you are available.</p>

    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
