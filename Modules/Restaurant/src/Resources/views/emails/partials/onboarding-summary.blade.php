<h2 style="margin:24px 0 12px;font-size:20px;color:#172033;">Your onboarding details</h2>
<p style="font-size:14px;color:#64748b;">Your registration step has been approved. Each remaining step has its own review status below.</p>
@foreach($steps as $step)
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:16px 0;border:1px solid #e2e8f0;border-radius:8px;">
        <tr><td style="padding:16px;background:#f8fafc;">
            <h3 style="margin:0;color:#172033;font-size:16px;">Step {{ $step['number'] }}: {{ $step['label'] }}</h3>
            <p style="margin:6px 0 0;font-size:13px;">Status: <strong>{{ $step['status'] }}</strong></p>
            @if($step['submitted_at'])<p style="margin:4px 0;font-size:12px;color:#64748b;">Submitted: {{ $step['submitted_at'] }}</p>@endif
            @if($step['reviewed_at'])<p style="margin:4px 0;font-size:12px;color:#64748b;">Reviewed: {{ $step['reviewed_at'] }}@if($step['reviewer']) by {{ $step['reviewer'] }}@endif</p>@endif
        </td></tr>
        <tr><td style="padding:16px;">
            @if($step['fields'])
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    @foreach($step['fields'] as $field)
                        <tr>
                            <td style="padding:7px 8px 7px 0;width:40%;vertical-align:top;border-bottom:1px solid #f1f5f9;font-size:13px;color:#64748b;">{{ $field['label'] }}</td>
                            <td style="padding:7px 0;vertical-align:top;border-bottom:1px solid #f1f5f9;font-size:13px;color:#172033;word-break:break-word;white-space:pre-line;">{{ $field['value'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p style="margin:0;font-size:13px;color:#64748b;">No details submitted for this step yet.</p>
            @endif
            @if($step['rejection_reason'])<p style="font-size:13px;color:#b91c1c;">Review notes: {{ $step['rejection_reason'] }}</p>@endif
        </td></tr>
    </table>
@endforeach
