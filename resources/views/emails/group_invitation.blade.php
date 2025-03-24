<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Invitation</title>
</head>

<body style="background-color: #f9f9f9; font-family: Arial, sans-serif; padding: 40px; text-align: center;">

    <table align="center" width="100%" cellspacing="0" cellpadding="0"
        style="max-width: 600px; background: #ffffff; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); padding: 20px;">
        <tr>
            <td style="text-align: center;">
                <img src="{{ asset('images/PiggyBang.png') }}" alt="PiggyBang Logo" width="120" style="margin-bottom: 20px;">
                <h2 style="color: #333; font-weight: 600;">You've Been Invited to a Group</h2>
                <p style="color: #555; font-size: 16px;">
                    Hi there, <br>
                    You have been invited to join the <strong>{{ $group->name }}</strong> group on PiggyBang.
                </p>

                @if($isExistingUser)
                <a href="{{ $url }}"
                    style="display: inline-block; background-color: #eab308; color: #ffffff; padding: 14px 24px; font-size: 16px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-top: 20px;">
                    Join Group
                </a>
                @else
                <a href="{{ $url }}"
                    style="display: inline-block; background-color: #eab308; color: #ffffff; padding: 14px 24px; font-size: 16px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-top: 20px;">
                    Sign Up & Join
                </a>
                @endif

                <p style="color: #777; font-size: 14px; margin-top: 20px;">
                    If you weren’t expecting this invitation, you can safely ignore this email.
                </p>
            </td>
        </tr>
    </table>

    <p style="color: #999; font-size: 12px; margin-top: 20px;">
        © {{ date('Y') }} PiggyBang. All rights reserved.
    </p>

</body>

</html>