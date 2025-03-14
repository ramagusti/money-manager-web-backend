<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Member Joined</title>
</head>

<body style="background-color: #f9f9f9; font-family: Arial, sans-serif; padding: 40px; text-align: center;">

    <table align="center" width="100%" cellspacing="0" cellpadding="0"
        style="max-width: 600px; background: #ffffff; border-radius: 8px; 
               box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); padding: 20px;">
        <tr>
            <td style="text-align: center;">
                <h2 style="color: #333; font-weight: 600;">New Member Joined</h2>
                <p style="color: #555; font-size: 16px;">
                    Hi <strong>{{ $group->users()->wherePivot('role', 'owner')->first()->name }}</strong>, <br>
                    <strong>{{ $user->name }}</strong> has joined your group: <strong>{{ $group->name }}</strong>.
                </p>
                <a href="{{ env('FRONTEND_URL') }}/groups/{{ $group->id }}"
                    style="display: inline-block; background-color: #eab308; color: #ffffff; 
                          padding: 14px 24px; font-size: 16px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                    View Group
                </a>
            </td>
        </tr>
    </table>

    <p style="color: #999; font-size: 12px; margin-top: 20px;">
        © {{ date('Y') }} PiggyBang. All rights reserved.
    </p>

</body>

</html>