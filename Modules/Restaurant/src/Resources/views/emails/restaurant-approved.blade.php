<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Restaurant registration approved — Dawat</title>
</head>
<body style="margin:0;padding:0;background-color:#090909;color:#ffffff;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">Your restaurant registration is approved. Log in to review your details and continue your Dawat onboarding.</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#090909" style="background-color:#090909;">
        <tr><td align="center" style="padding:32px 16px;">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">
                <tr><td style="padding:0 8px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="font-family:Georgia,'Times New Roman',serif;font-size:44px;line-height:1.1;color:#ffffff;">Dawat<span style="color:#ff6210;">.</span></td>
                            <td align="right" style="font-size:10px;line-height:18px;letter-spacing:2px;color:#a3a3a3;">GOOD FOOD.<br>GREAT CONNECTIONS.</td>
                        </tr>
                    </table>
                </td></tr>
                <tr><td bgcolor="#141414" style="background-color:#141414;border:1px solid #303030;border-top:3px solid #ff6210;border-radius:16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr><td style="padding:36px 28px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td bgcolor="#2c1a10" style="background-color:#2c1a10;border:1px solid #6a3217;border-radius:20px;padding:8px 14px;font-size:11px;line-height:16px;font-weight:bold;letter-spacing:1.4px;color:#ff9a66;">&#10003;&nbsp; REGISTRATION APPROVED</td></tr></table>
                        </td></tr>
                        <tr><td style="padding:24px 28px 0;font-family:Georgia,'Times New Roman',serif;font-size:40px;line-height:46px;color:#ffffff;">Welcome to<br>the Dawat table<span style="color:#ff6210;">.</span></td></tr>
                        <tr><td style="padding:24px 28px 0;font-size:16px;line-height:26px;color:#eeeeee;">Hello {{ $ownerName }},</td></tr>
                        <tr><td style="padding:12px 28px 0;font-size:15px;line-height:26px;color:#bdbdbd;">The <strong style="color:#ffffff;">{{ $approvedStepLabel }}</strong> step for <strong style="color:#ffffff;">{{ $restaurantName }}</strong> is approved. You can now log in with the email and password you used to create your account.</td></tr>
                        <tr><td style="padding:28px 28px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#1c1c1c" style="background-color:#1c1c1c;border:1px solid #353535;border-radius:10px;">
                                <tr><td colspan="2" style="padding:20px 20px 10px;font-size:11px;line-height:16px;font-weight:bold;letter-spacing:1.5px;color:#ff8b4d;">YOUR REGISTRATION</td></tr>
                                @foreach(['Restaurant' => $restaurantName, 'Restaurant ID' => $restaurantId, 'Login email' => $restaurantEmail, 'Phone' => $restaurantPhone, 'Approved step' => $approvedStepLabel, 'Approved on' => $approvedDate] as $label => $value)
                                    <tr>
                                        <td style="padding:9px 12px 9px 20px;width:36%;vertical-align:top;font-size:13px;line-height:22px;color:#a3a3a3;">{{ $label }}</td>
                                        <td style="padding:9px 20px 9px 0;vertical-align:top;font-size:13px;line-height:22px;color:#eeeeee;word-break:break-word;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                                <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>
                            </table>
                        </td></tr>
                        <tr><td style="padding:24px 28px 0;font-size:15px;line-height:26px;color:#bdbdbd;">Review the progress of each onboarding step below. Complete any remaining information in your account; banking, tax, and KYC details retain their own review status.</td></tr>
                        <tr><td style="padding:0 28px;">{!! $onboardingStepsHtml !!}</td></tr>
                        <tr><td style="padding:24px 28px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" bgcolor="#ff6210" style="background-color:#ff6210;border-radius:28px;mso-padding-alt:15px 28px;"><a href="{{ $loginUrl }}" style="display:inline-block;padding:15px 28px;color:#ffffff;font-size:14px;line-height:20px;font-weight:bold;text-decoration:none;border-radius:28px;">Log in to continue &nbsp;&#8594;</a></td></tr></table>
                        </td></tr>
                        <tr><td style="padding:20px 28px 0;font-size:13px;line-height:23px;color:#bdbdbd;">Forgot your password? Use the <strong style="color:#eeeeee;">Forgot password?</strong> link on the login page to reset it.</td></tr>
                        <tr><td style="padding:30px 28px 32px;font-size:14px;line-height:24px;color:#aaaaaa;">See you at the table,<br><strong style="color:#ffffff;font-weight:bold;">The Dawat Team</strong></td></tr>
                    </table>
                </td></tr>
                <tr><td align="center" style="padding:28px 16px 0;font-family:Georgia,'Times New Roman',serif;font-size:20px;line-height:28px;color:#d6d6d6;">Good food brings people together.</td></tr>
                <tr><td align="center" style="padding:12px 16px 0;font-size:12px;line-height:21px;color:#8f8f8f;">You received this email about your restaurant registration with Dawat.</td></tr>
                <tr><td align="center" style="padding:12px 16px 0;font-size:12px;line-height:21px;"><a href="{{ $supportUrl }}" style="color:#ff9a66;text-decoration:underline;">Contact support</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="{{ $privacyUrl }}" style="color:#ff9a66;text-decoration:underline;">Privacy policy</a></td></tr>
                <tr><td align="center" style="padding:12px 16px 0;font-size:11px;line-height:20px;color:#777777;">&copy; {{ $currentYear }} Dawat. All rights reserved.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
