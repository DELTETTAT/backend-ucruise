<!DOCTYPE html>
<html>
<head>
    <title>New Hiring Template Created</title>
</head>
<body>
    <h1>New Hiring Template Created</h1>

    <p><strong>Template Name:</strong> {{ $hiringTemplate->template_name }}</p>
    <p><strong>Title:</strong> {{ $hiringTemplate->title }}</p>
    <p><strong>Name:</strong> {{ $hiringTemplate->name }}</p>
    <p><strong>Email:</strong> {{ $hiringTemplate->email }}</p>
    <p><strong>Phone:</strong> {{ $hiringTemplate->phone }}</p>
    <p><strong>Date of Issue:</strong> {{ $hiringTemplate->date_of_issue }}</p>
    <p><strong>Content:</strong> {!! $hiringTemplate->content !!}</p>
</body>
</html>
