<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Banking information {{ $approved ? 'approved' : 'rejected' }} — Dawat</title>
</head>
<body style="margin:0;padding:0;background-color:#090909;color:#ffffff;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">The banking step for {{ $restaurantName }} has been {{ $approved ? 'approved' : 'rejected' }}. {{ $approved ? 'Check your remaining onboarding steps.' : 'Review the feedback and resubmit your details.' }}</div>
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
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td bgcolor="#2c1a10" style="background-color:#2c1a10;border:1px solid #6a3217;border-radius:20px;padding:8px 14px;font-size:11px;line-height:16px;font-weight:bold;letter-spacing:1.4px;color:#ff9a66;">{{ $approved ? 'BANKING APPROVED' : 'BANKING NEEDS CORRECTION' }}</td></tr></table>
                        </td></tr>
                        <tr><td style="padding:24px 28px 0;font-family:Georgia,'Times New Roman',serif;font-size:40px;line-height:46px;color:#ffffff;">
                            @if($approved)
                                Banking approved<br>Keep moving forward<span style="color:#ff6210;">.</span>
                            @else
                                Let’s get your<br>banking details right<span style="color:#ff6210;">.</span>
                            @endif
                        </td></tr>
                        <tr><td style="padding:24px 28px 0;font-size:16px;line-height:26px;color:#eeeeee;">Hello,</td></tr>
                        <tr><td style="padding:12px 28px 0;font-size:15px;line-height:26px;color:#bdbdbd;">The banking and company information for <strong style="color:#ffffff;">{{ $restaurantName }}</strong> has been <strong style="color:#ffffff;">{{ $approved ? 'approved' : 'rejected' }}</strong> by the review team. @if($approved)Your banking step is complete. Continue with any remaining onboarding information.@else Please review the reason below, correct your banking or company information, and resubmit it for review.@endif</td></tr>
                        <tr><td style="padding:28px 28px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#1c1c1c" style="background-color:#1c1c1c;border:1px solid #353535;border-radius:10px;">
                                <tr><td colspan="2" style="padding:20px 20px 10px;font-size:11px;line-height:16px;font-weight:bold;letter-spacing:1.5px;color:#ff8b4d;">REVIEW DETAILS</td></tr>
                                @foreach(['Restaurant' => $restaurantName, 'Step' => 'Banking Information', 'Status' => $approved ? 'Approved' : 'Rejected', 'Reviewed on' => $reviewedAt] as $label => $value)
                                    <tr>
                                        <td style="padding:9px 12px 9px 20px;width:36%;vertical-align:top;font-size:13px;line-height:22px;color:#a3a3a3;">{{ $label }}</td>
                                        <td style="padding:9px 20px 9px 0;vertical-align:top;font-size:13px;line-height:22px;color:#eeeeee;word-break:break-word;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                                <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>
                            </table>
                        </td></tr>
                        <tr><td style="padding:28px 28px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#2c1a10" style="background-color:#2c1a10;border-left:3px solid #ff6210;border-radius:8px;">
                                <tr><td style="padding:16px 18px;font-size:13px;line-height:23px;color:#e6c8b8;">@if($approved)<strong style="color:#ff9a66;">What happens next</strong><br>Check the remaining steps in your restaurant account. Each onboarding step has its own review status.@else <strong style="color:#ff9a66;">Rejection reason</strong><br><span style="white-space:pre-line;">{{ $rejectionReason }}</span>@endif</td></tr>
                            </table>
                        </td></tr>
                        <tr><td style="padding:24px 28px 0;font-size:14px;line-height:24px;color:#bdbdbd;">@if($approved)Log in to check your progress and complete any remaining onboarding steps.@else Log in, open Banking Information, update the details, and submit them again. Your corrections will be reviewed after resubmission.@endif</td></tr>
                        <tr><td style="padding:22px 28px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" bgcolor="#ff6210" style="background-color:#ff6210;border-radius:28px;mso-padding-alt:15px 28px;"><a href="{{ $loginUrl }}" style="display:inline-block;padding:15px 28px;color:#ffffff;font-size:14px;line-height:20px;font-weight:bold;text-decoration:none;border-radius:28px;">{{ $approved ? 'Continue onboarding' : 'Update banking details' }} &nbsp;&#8594;</a></td></tr></table>
                        </td></tr>
                        <tr><td style="padding:20px 28px 0;font-size:13px;line-height:23px;color:#bdbdbd;">If you have a question about this review, please <a href="{{ $supportUrl }}" style="color:#ff9a66;text-decoration:underline;">contact support</a>.</td></tr>
                        <tr><td style="padding:30px 28px 32px;font-size:14px;line-height:24px;color:#aaaaaa;">See you at the table,<br><strong style="color:#ffffff;font-weight:bold;">The Dawat Team</strong></td></tr>
                    </table>
                </td></tr>
                <tr><td align="center" style="padding:28px 16px 0;font-family:Georgia,'Times New Roman',serif;font-size:20px;line-height:28px;color:#d6d6d6;">Good food brings people together.</td></tr>
                <tr><td align="center" style="padding:12px 16px 0;font-size:12px;line-height:21px;color:#8f8f8f;">You received this email about the review of your restaurant banking information on Dawat.</td></tr>
                <tr><td align="center" style="padding:12px 16px 0;font-size:11px;line-height:20px;color:#777777;">&copy; {{ $currentYear }} Dawat. All rights reserved.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
