<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subscription Renewed</title>
</head>
<body>
    <h2>Your Subscription Has Been Renewed</h2>
    <p>Hello {{ $tenantName }},</p>
    <p>Your subscription has been successfully renewed.</p>
    <table style="border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 8px 16px 8px 0; font-weight: bold;">Period:</td>
            <td style="padding: 8px 0">{{ $startsAt }} to {{ $endsAt }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 16px 8px 0; font-weight: bold;">Amount:</td>
            <td style="padding: 8px 0">{{ $amount }} {{ $currency }}</td>
        </tr>
    </table>
    <p>Thank you for your continued subscription.</p>
    <p>Best regards,<br>The Team</p>
</body>
</html>
