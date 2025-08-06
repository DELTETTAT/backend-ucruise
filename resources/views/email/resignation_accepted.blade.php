<!DOCTYPE html>
<html>
<head>
    <title>Resignation Accepted</title>
</head>
<body>
    <p>Dear {{ $emailData['employee_name'] }},</p>
    
    <p>We would like to inform you that your resignation submitted for {{ $emailData['resignation_date'] }} 
    has been accepted on {{ $emailData['processed_date'] }} by {{ $emailData['admin'] }}.</p>
    
    <p>Please complete any pending formalities before your last working day.</p>
    
    <p>Best Regards,<br>{{ $fromName }}<br>
    HR Department</p>
</body>
</html>