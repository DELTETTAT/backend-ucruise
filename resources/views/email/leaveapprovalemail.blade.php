<!DOCTYPE html>
<html>
<head>
    <title>Leave Approved</title>
</head>
<body>
    <p>Dear {{ $emailData['name'] }},</p>

    <p>We are pleased to inform you that your leave request from {{ $emailData['start_date'] }} to {{ $emailData['end_date'] }} has been approved.</p>

    <p>Enjoy your time off!</p>

    <p>Best Regards,<br>{{ $emailData['admin'] }}</p>
</body>
</html>
