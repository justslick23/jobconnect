<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Offer Letter</title>
</head>
<body>
    <p>Dear {{ $user->name }},</p>

    <p>We are pleased to inform you that you have successfully passed the interview process for the position you applied for.</p>

    @if(!empty($messageBody))
        <p>{{ $messageBody }}</p>
    @endif

    <p>Please find attached your official offer letter with the details of your employment.</p>

    <p>If you have any questions or need further information, feel free to reach out.</p>

    <p>Best regards,<br>
    {{ config('app.name') }} Recruitment Team</p>
</body>
</html>
