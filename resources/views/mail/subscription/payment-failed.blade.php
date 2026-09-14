<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subscription Payment Failed</title>
</head>
<body>
    <h2>Action Required: Payment Failed</h2>
    <p>Hello {{ $tenantName }},</p>
    <p>Your subscription renewal payment has failed.</p>
    <p><strong>Failure Reason:</strong> {{ $failureReason }}</p>
    <p>Please update your payment method or contact us to restore your subscription.</p>
    <p>If you believe this is an error, please contact <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a> for assistance.</p>
    <p>Best regards,<br>The Team</p>
</body>
</html>
