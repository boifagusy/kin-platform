<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>KIN Password Reset OTP</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #1A5632; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 28px; font-weight: bold; color: #1A5632; }
        .otp-code { font-size: 42px; font-weight: bold; text-align: center; letter-spacing: 8px; color: #1A5632; background: #f0f7f2; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; font-size: 12px; color: #888; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .button { display: inline-block; background-color: #1A5632; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🛡️ KIN Safety</div>
        </div>

        <h2 style="color: #333;">Password Reset Request</h2>

        <p>Hello {{ $userName }},</p>

        <p>We received a request to reset your KIN account password. Use the OTP below to complete the process:</p>

        <div class="otp-code">
            {{ $otp }}
        </div>

        <p>This OTP is valid for <strong>10 minutes</strong> and can only be used once.</p>

        <p>If you didn't request this, please ignore this email. Your account remains secure.</p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} KIN Safety. All rights reserved.</p>
            <p>Keeping you and your loved ones safe.</p>
        </div>
    </div>
</body>
</html>
