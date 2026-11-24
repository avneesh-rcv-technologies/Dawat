@if($reason !== '')
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#2c1a10" style="background-color:#2c1a10;border-left:3px solid #ff6210;border-radius:8px;">
        <tr><td style="padding:16px 18px;font-size:14px;line-height:24px;color:#e6c8b8;">
            <strong style="color:#ff9a66;">{{ $refill ? 'Reason and required corrections' : 'Rejection reason' }}</strong><br>
            <span style="white-space:pre-line;">{{ $reason }}</span>
        </td></tr>
    </table>
@endif
