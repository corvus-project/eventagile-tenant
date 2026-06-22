<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Event Registration Updated</title>
</head>
<body>
    <p>Hello {{ $name }},</p>
    <p>Your registration for the event <strong>{{ $title }}</strong> has been updated. The current status of your registration is: <strong>{{ $status }}</strong>.

    </p>
    <p>
        <strong>Event Details:</strong><br>
        Date: {{ $start_time }}<br>
        Location: {{ $location }}
    </p>
    <p>
        If you have any questions, feel free to contact us.<br>
        We look forward to seeing you!
    </p>
    <p>Best regards,<br>
    The Event Team</p>
</body>
</html>