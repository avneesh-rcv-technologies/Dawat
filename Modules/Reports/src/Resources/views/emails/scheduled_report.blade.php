<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? 'Scheduled Report' }}</title>
</head>
<body style="margin:0; padding:24px; background:#F3F4F6; font-family:Arial, Helvetica, sans-serif; color:#111827; line-height:1.5;">
    @php
        $previewRows = $meta['preview_rows'] ?? [];
        $columns = $meta['columns'] ?? [];
        $metric = $meta['primary_metric'] ?? null;
    @endphp

    <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; max-width:920px; margin:0 auto; background:#ffffff; border:1px solid #E5E7EB; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="padding:20px 24px; background:#0F172A; color:#ffffff;">
                <div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; opacity:.8; margin-bottom:6px;">Automated Report Delivery</div>
                <h1 style="margin:0; font-size:22px; line-height:1.2;">{{ $reportName }}</h1>
                <p style="margin:8px 0 0 0; font-size:13px; color:#CBD5E1;">{{ $subjectLine ?? 'Scheduled Report' }}</p>
            </td>
        </tr>

        <tr>
            <td style="padding:18px 24px 4px 24px;">
                <div style="padding:12px 14px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; color:#334155;">
                    {!! nl2br(e($emailBody)) !!}
                </div>
            </td>
        </tr>

        <tr>
            <td style="padding:16px 24px 6px 24px;">
                <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:separate; border-spacing:8px 8px;">
                    <tr>
                        <td style="width:25%; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px;">
                            <div style="font-size:11px; color:#64748B; text-transform:uppercase; letter-spacing:.05em;">Generated</div>
                            <div style="font-size:14px; font-weight:600; color:#0F172A; margin-top:4px;">{{ $meta['generated_at'] ?? now()->toDateTimeString() }}</div>
                        </td>
                        <td style="width:25%; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px;">
                            <div style="font-size:11px; color:#64748B; text-transform:uppercase; letter-spacing:.05em;">Rows</div>
                            <div style="font-size:18px; font-weight:700; color:#0F172A; margin-top:2px;">{{ number_format((int) ($meta['total_rows'] ?? 0)) }}</div>
                        </td>
                        <td style="width:25%; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px;">
                            <div style="font-size:11px; color:#64748B; text-transform:uppercase; letter-spacing:.05em;">Execution</div>
                            <div style="font-size:18px; font-weight:700; color:#0F172A; margin-top:2px;">{{ $meta['execution_time_seconds'] ?? 0 }}s</div>
                        </td>
                        <td style="width:25%; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:10px 12px;">
                            <div style="font-size:11px; color:#64748B; text-transform:uppercase; letter-spacing:.05em;">Chart</div>
                            <div style="font-size:14px; font-weight:600; color:#0F172A; margin-top:4px;">{{ ucfirst((string) ($meta['chart_type'] ?? 'table')) }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        @if(!empty($metric))
        <tr>
            <td style="padding:8px 24px 8px 24px;">
                <div style="font-size:13px; font-weight:700; color:#0F172A; margin-bottom:8px;">Primary Metric: {{ $metric['label'] ?? 'Value' }}</div>
                <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:separate; border-spacing:8px 8px;">
                    <tr>
                        <td style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:8px; padding:10px 12px; width:25%;">
                            <div style="font-size:11px; color:#065F46; text-transform:uppercase;">Total</div>
                            <div style="font-size:16px; font-weight:700; color:#065F46;">{{ $metric['sum'] ?? 0 }}</div>
                        </td>
                        <td style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:10px 12px; width:25%;">
                            <div style="font-size:11px; color:#1D4ED8; text-transform:uppercase;">Average</div>
                            <div style="font-size:16px; font-weight:700; color:#1D4ED8;">{{ $metric['avg'] ?? 0 }}</div>
                        </td>
                        <td style="background:#FEF3C7; border:1px solid #FCD34D; border-radius:8px; padding:10px 12px; width:25%;">
                            <div style="font-size:11px; color:#92400E; text-transform:uppercase;">Max</div>
                            <div style="font-size:16px; font-weight:700; color:#92400E;">{{ $metric['max'] ?? 0 }}</div>
                        </td>
                        <td style="background:#FEE2E2; border:1px solid #FCA5A5; border-radius:8px; padding:10px 12px; width:25%;">
                            <div style="font-size:11px; color:#991B1B; text-transform:uppercase;">Min</div>
                            <div style="font-size:16px; font-weight:700; color:#991B1B;">{{ $metric['min'] ?? 0 }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif

        <tr>
            <td style="padding:8px 24px 18px 24px;">
                <div style="font-size:13px; font-weight:700; color:#0F172A; margin-bottom:8px;">Data Preview (Top {{ count($previewRows) }})</div>
                @if(!empty($previewRows) && !empty($columns))
                    <div style="overflow:auto; border:1px solid #E5E7EB; border-radius:8px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; min-width:680px; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#F8FAFC;">
                                    @foreach($columns as $col)
                                        <th style="text-align:left; padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12px; color:#334155; white-space:nowrap;">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewRows as $row)
                                    <tr>
                                        @foreach($columns as $col)
                                            <td style="padding:9px 12px; border-bottom:1px solid #F1F5F9; font-size:12px; color:#0F172A; white-space:nowrap;">
                                                {{ is_scalar($row[$col] ?? null) || ($row[$col] ?? null) === null ? ($row[$col] ?? '') : json_encode($row[$col]) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding:12px 14px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B;">
                        No preview rows available for this report run.
                    </div>
                @endif
            </td>
        </tr>

        <tr>
            <td style="padding:0 24px 22px 24px; font-size:12px; color:#64748B;">
                Data Source: <strong style="color:#334155;">{{ $meta['data_source'] ?? '-' }}</strong> &nbsp;•&nbsp;
                Columns: <strong style="color:#334155;">{{ count($columns) }}</strong><br>
                Attachment includes full export file. If enabled, chart is attached as SVG.
            </td>
        </tr>
    </table>
</body>
</html>
