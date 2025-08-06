<!DOCTYPE html>
<html>
<head>
    <title>{{ $mailData['subject'] }}</title>
</head>
<body>
    <h1>{{ $mailData['title'] }}</h1>
    <p>{!! $mailData['body'] !!}</p>
</body>
</html>
