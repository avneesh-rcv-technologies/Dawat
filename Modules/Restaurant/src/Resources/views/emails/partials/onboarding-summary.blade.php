<h2 style="margin:24px 0 12px;font-size:20px;color:#eeeeee;">Your onboarding details</h2>
<p style="font-size:14px;color:#bdbdbd;">Your registration step has been approved. Each remaining step has its own review status below.</p>
@foreach($steps as $step)
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:16px 0;border:1px solid #353535;border-radius:8px;">
        <tr><td style="padding:16px;background:#1c1c1c;">
            <h3 style="margin:0;color:#eeeeee;font-size:16px;">Step {{ $step['number'] }}: {{ $step['label'] }}</h3>
            <p style="margin:6px 0 0;font-size:13px;">Status: <strong style="color:#ff9a66;">{{ $step['status'] }}</strong></p>
            @if($step['submitted_at'])<p style="margin:4px 0;font-size:12px;color:#bdbdbd;">Submitted: {{ $step['submitted_at'] }}</p>@endif
            @if($step['reviewed_at'])<p style="margin:4px 0;font-size:12px;color:#bdbdbd;">Reviewed: {{ $step['reviewed_at'] }}@if($step['reviewer']) by {{ $step['reviewer'] }}@endif</p>@endif
        </td></tr>
        <tr><td style="padding:16px;background:#141414;">
            @if($step['fields'])
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    @foreach($step['fields'] as $field)
                        <tr>
                            <td style="padding:7px 8px 7px 0;width:40%;vertical-align:top;border-bottom:1px solid #303030;font-size:13px;color:#bdbdbd;">{{ $field['label'] }}</td>
                            <td style="padding:7px 0;vertical-align:top;border-bottom:1px solid #303030;font-size:13px;color:#eeeeee;word-break:break-word;white-space:pre-line;">{{ $field['value'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @elseif($step['key'] === 'tax' && !in_array($step['status'], ['Draft', 'Not started'], true))
                <p style="margin:0;font-size:13px;color:#bdbdbd;">View your submitted tax information in your onboarding account.</p>
            @else
                <p style="margin:0;font-size:13px;color:#bdbdbd;">No details submitted for this step yet.</p>
            @endif
            @if($step['rejection_reason'])<p style="font-size:13px;color:#ff9a66;">Review notes: {{ $step['rejection_reason'] }}</p>@endif
        </td></tr>
    </table>
@endforeach
