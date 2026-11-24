{{--
    Shared Dawat email layout (onboarding-step design).
    Rendered by Modules\EmailNotification\Support\DawatEmail — see that class for the spec format.
    Text/HTML values come from seeders (trusted) and may contain {{placeholder}} tokens that
    NotificationTemplate::compile() replaces at send time, so they are output unescaped.
--}}
@php
    $orange = '#ff6210';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>{!! $title !!}</title>
</head>
<body style="margin:0;padding:0;background-color:#090909;color:#ffffff;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    @if($preheader)
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">{!! $preheader !!}</div>
    @endif
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#090909" style="background-color:#090909;">
        <tr><td align="center" style="padding:32px 16px;">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">
                <tr><td style="padding:0 8px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="font-family:Georgia,'Times New Roman',serif;font-size:44px;line-height:1.1;color:#ffffff;">Dawat<span style="color:{{ $orange }};">.</span></td>
                            <td align="right" style="font-size:10px;line-height:18px;letter-spacing:2px;color:#a3a3a3;">GOOD FOOD.<br>GREAT CONNECTIONS.</td>
                        </tr>
                    </table>
                </td></tr>
                <tr><td bgcolor="#141414" style="background-color:#141414;border:1px solid #303030;border-top:3px solid {{ $orange }};border-radius:16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                        @if($badge)
                        <tr><td style="padding:36px 28px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td bgcolor="#2c1a10" style="background-color:#2c1a10;border:1px solid #6a3217;border-radius:20px;padding:8px 14px;font-size:11px;line-height:16px;font-weight:bold;letter-spacing:1.4px;color:#ff9a66;">{!! $badge !!}</td></tr></table>
                        </td></tr>
                        @endif
                        <tr><td style="padding:{{ $badge ? '24px' : '36px' }} 28px 0;font-family:Georgia,'Times New Roman',serif;font-size:40px;line-height:46px;color:#ffffff;">{!! $headline !!}@if($headlineDot)<span style="color:{{ $orange }};">.</span>@endif</td></tr>
                        @if($greeting)
                        <tr><td style="padding:24px 28px 0;font-size:16px;line-height:26px;color:#eeeeee;">{!! $greeting !!}</td></tr>
                        @endif
                        @foreach($intro as $i => $paragraph)
                        <tr><td style="padding:{{ $i === 0 && $greeting ? '12px' : '16px' }} 28px 0;font-size:15px;line-height:26px;color:#bdbdbd;">{!! $paragraph !!}</td></tr>
                        @endforeach

                        @foreach($blocks as $block)
                            @switch($block['type'])
                                @case('details')
                                <tr><td style="padding:28px 28px 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#1c1c1c" style="background-color:#1c1c1c;border:1px solid #353535;border-radius:10px;">
                                        @if(!empty($block['title']))
                                        <tr><td colspan="2" style="padding:20px 20px 10px;font-size:11px;line-height:16px;font-weight:bold;letter-spacing:1.5px;color:#ff8b4d;text-transform:uppercase;">{!! $block['title'] !!}</td></tr>
                                        @else
                                        <tr><td colspan="2" style="height:10px;line-height:10px;">&nbsp;</td></tr>
                                        @endif
                                        @foreach($block['rows'] as $label => $value)
                                        <tr>
                                            <td style="padding:9px 12px 9px 20px;width:36%;vertical-align:top;font-size:13px;line-height:22px;color:#a3a3a3;">{!! $label !!}</td>
                                            <td style="padding:9px 20px 9px 0;vertical-align:top;font-size:13px;line-height:22px;color:#eeeeee;word-break:break-word;">{!! $value !!}</td>
                                        </tr>
                                        @endforeach
                                        @if(!empty($block['footer']))
                                        <tr><td colspan="2" style="padding:12px 20px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="border-top:1px solid #353535;font-size:1px;line-height:1px;">&nbsp;</td></tr></table></td></tr>
                                        @foreach($block['footer'] as $label => $value)
                                        <tr>
                                            <td style="padding:9px 12px 9px 20px;width:36%;vertical-align:top;font-size:14px;line-height:22px;font-weight:bold;color:#ff9a66;">{!! $label !!}</td>
                                            <td style="padding:9px 20px 9px 0;vertical-align:top;font-size:14px;line-height:22px;font-weight:bold;color:#ffffff;">{!! $value !!}</td>
                                        </tr>
                                        @endforeach
                                        @endif
                                        <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>
                                    </table>
                                </td></tr>
                                @break

                                @case('code')
                                <tr><td style="padding:28px 28px 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#1c1c1c" style="background-color:#1c1c1c;border:1px solid #353535;border-radius:10px;">
                                        <tr><td align="center" style="padding:22px 16px 12px;font-size:11px;line-height:16px;font-weight:bold;letter-spacing:1.5px;color:#ff8b4d;text-transform:uppercase;">{!! $block['label'] !!}</td></tr>
                                        <tr><td align="center" style="padding:0 8px {{ !empty($block['caption']) ? '12px' : '22px' }};font-family:'Courier New',Courier,monospace;font-size:36px;line-height:48px;font-weight:bold;letter-spacing:{{ $block['spacing'] ?? '6px' }};color:#ffffff;word-break:break-word;">{!! $block['value'] !!}</td></tr>
                                        @if(!empty($block['caption']))
                                        <tr><td align="center" style="padding:0 16px 22px;font-size:13px;line-height:22px;color:#bdbdbd;">{!! $block['caption'] !!}</td></tr>
                                        @endif
                                    </table>
                                </td></tr>
                                @break

                                @case('callout')
                                <tr><td style="padding:24px 28px 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#2c1a10" style="background-color:#2c1a10;border-left:3px solid {{ $orange }};border-radius:8px;">
                                        <tr><td style="padding:16px 18px;font-size:13px;line-height:23px;color:#e6c8b8;">@if(!empty($block['title']))<strong style="color:#ff9a66;">{!! $block['title'] !!}</strong><br>@endif<span style="white-space:pre-line;">{!! $block['body'] !!}</span></td></tr>
                                    </table>
                                </td></tr>
                                @break

                                @case('list')
                                <tr><td style="padding:28px 28px 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#1c1c1c" style="background-color:#1c1c1c;border:1px solid #353535;border-radius:10px;">
                                        @if(!empty($block['title']))
                                        <tr><td colspan="2" style="padding:20px 20px 6px;font-size:11px;line-height:16px;font-weight:bold;letter-spacing:1.5px;color:#ff8b4d;text-transform:uppercase;">{!! $block['title'] !!}</td></tr>
                                        @else
                                        <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>
                                        @endif
                                        @foreach($block['items'] as $item)
                                        <tr>
                                            <td style="padding:7px 0 7px 20px;width:18px;vertical-align:top;font-size:13px;line-height:22px;color:{{ $orange }};">&#9679;</td>
                                            <td style="padding:7px 20px 7px 8px;vertical-align:top;font-size:13px;line-height:22px;color:#eeeeee;">{!! $item !!}</td>
                                        </tr>
                                        @endforeach
                                        <tr><td colspan="2" style="height:12px;line-height:12px;">&nbsp;</td></tr>
                                    </table>
                                </td></tr>
                                @break

                                @case('text')
                                <tr><td style="padding:24px 28px 0;font-size:14px;line-height:24px;color:#bdbdbd;">{!! $block['body'] !!}</td></tr>
                                @break

                                @default
                                <tr><td style="padding:24px 28px 0;">{!! $block['body'] ?? '' !!}</td></tr>
                            @endswitch
                        @endforeach

                        @if(!empty($buttons))
                        <tr><td style="padding:24px 28px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                @foreach($buttons as $i => $button)
                                    @if($i === 0)
                                    <td align="center" bgcolor="{{ $orange }}" style="background-color:{{ $orange }};border-radius:28px;mso-padding-alt:15px 28px;"><a href="{!! $button['url'] !!}" style="display:inline-block;padding:15px 28px;color:#ffffff;font-size:14px;line-height:20px;font-weight:bold;text-decoration:none;border-radius:28px;">{!! $button['label'] !!} &nbsp;&#8594;</a></td>
                                    @else
                                    <td style="width:10px;font-size:1px;line-height:1px;">&nbsp;</td>
                                    <td align="center" style="border:1px solid #6a3217;border-radius:28px;mso-padding-alt:14px 24px;"><a href="{!! $button['url'] !!}" style="display:inline-block;padding:14px 24px;color:#ff9a66;font-size:14px;line-height:20px;font-weight:bold;text-decoration:none;border-radius:28px;">{!! $button['label'] !!}</a></td>
                                    @endif
                                @endforeach
                            </tr></table>
                        </td></tr>
                        @endif

                        @foreach($notes as $note)
                        <tr><td style="padding:20px 28px 0;font-size:13px;line-height:23px;color:#bdbdbd;">{!! $note !!}</td></tr>
                        @endforeach

                        <tr><td style="padding:30px 28px 32px;font-size:14px;line-height:24px;color:#aaaaaa;">{!! $signoff !!}<br><strong style="color:#ffffff;font-weight:bold;">The Dawat Team</strong></td></tr>
                    </table>
                </td></tr>
                <tr><td align="center" style="padding:28px 16px 0;font-family:Georgia,'Times New Roman',serif;font-size:20px;line-height:28px;color:#d6d6d6;">Good food brings people together.</td></tr>
                @if($footer)
                <tr><td align="center" style="padding:12px 16px 0;font-size:12px;line-height:21px;color:#8f8f8f;">{!! $footer !!}</td></tr>
                @endif
                <tr><td align="center" style="padding:12px 16px 0;font-size:11px;line-height:20px;color:#777777;">&copy; {!! $year !!} Dawat. All rights reserved.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
