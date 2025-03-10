@component('mail::layout')
{{-- Header --}}
@slot('header')
<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center" style="padding: 20px 0;">
            <a href="{{ config('app.url') }}" style="text-decoration: none; color: #333;">
                <img src="{{ asset('images/PiggyBang.png') }}" alt="Ragst VIP" width="120" height="auto">
            </a>
        </td>
    </tr>
</table>
@endslot

{{-- Body --}}
<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center" style="padding: 30px; background-color: #ffffff; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);">
            <h2 style="color: #333; font-weight: 600; margin-bottom: 10px;">Welcome to Ragst VIP</h2>
            <p style="color: #555; font-size: 16px; line-height: 1.6;">
                Hi <strong>{{ $user->name }}</strong>, <br>
                Thank you for signing up! To get started, please verify your email address by clicking the button below.
            </p>

            <a href="{{ $verification_url }}"
                style="display: inline-block; background-color: #eab308; color: #ffffff; padding: 14px 24px; 
                font-size: 16px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-top: 20px;">
                Verify My Email
            </a>

            <p style="color: #777; font-size: 14px; margin-top: 20px;">
                If you didn't create an account, no action is required.
            </p>
        </td>
    </tr>
</table>

{{-- Footer --}}
@slot('footer')
<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center" style="padding: 20px 0; color: #888; font-size: 13px;">
            © {{ date('Y') }} Ragst. All rights reserved.
        </td>
    </tr>
</table>
@endslot
@endcomponent