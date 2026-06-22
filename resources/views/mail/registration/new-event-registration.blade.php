<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Event Registration Confirmation</title>
</head>
<body>
    <h2>Thank You for Registering!</h2>
    <p>Hello {{ $name }},</p>
    <p>You have successfully registered for the event: <strong>{{ $title }}</strong>.</p>
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