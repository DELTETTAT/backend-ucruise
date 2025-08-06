<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OTP for Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;">
        <h2 style="color: #333;">Password Reset OTP</h2>
        <p>Dear {{ $emailData['name'] }},</p>
        <p>You have requested to reset your password. Use the following OTP to proceed:</p>

        <h1 style="text-align: center; color: #2d89ef; letter-spacing: 8px;">
            {{ $emailData['otp'] }}
        </h1>

        <p>This OTP is valid for 5 minutes. Do not share it with anyone.</p>
        <p>If you did not request a password reset, please ignore this email.</p>

        <br>
        <!-- <p>Regards,</p>
        <p><strong>Your App Name</strong></p> -->
    </div>
</body>
</html>
