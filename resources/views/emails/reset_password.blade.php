<!DOCTYPE html>
<html>

<head>
    <title>Reset Your Password</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 40px; text-align: center;">
    <div style="background: #fff; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto;">
        <img src="{{ asset('images/PiggyBang.png') }}" alt="PiggyBang" width="100" style="margin-bottom: 20px;" />
        <h2 style="color: #333;">Reset Your Password</h2>
        <p>Click the button below to set a new password:</p>
        <a href="{{ $resetUrl }}" style="background: #eab308; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a>
        <p style="color: #888; font-size: 14px; margin-top: 20px;">If you didn't request a password reset, please ignore this email.</p>
    </div>
</body>

</html>