<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Job Opportunity</title>
</head>
<body>
    <h2>New Job Opportunity</h2>

    <p>A new job position has been posted:</p>

    <p><strong>{{ $job->title }}</strong></p>

    <p>
        <strong>Department:</strong> {{ $job->department->name ?? 'N/A' }}<br>
        <strong>Employment Type:</strong> {{ ucfirst($job->employment_type) }}<br>
        <strong>Application Deadline:</strong>
        {{ $job->application_deadline ? $job->application_deadline->format('M d, Y') : 'Open until filled' }}
    </p>

    <p>
        <a href="{{ route('job-requisitions.show', $job->id) }}">Click here to view and apply</a>
    </p>

    <p>Thanks,<br>CBS Recruitment</p>
</body>
</html>
