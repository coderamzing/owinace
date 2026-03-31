<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background-color:#f3f3e5;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <span style="display:none;max-height:0;overflow:hidden;mso-hide:all;">@yield('preheader', '')</span>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f3e5;border-collapse:collapse;">
        <tr>
            <td align="center" style="padding:24px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;border-collapse:collapse;">
                    <tr>
                        <td style="background-color:#061d19;border-radius:12px 12px 0 0;padding:8px 0 0;text-align:center;">
                            <div style="height:3px;background-color:#b8ff90;border-radius:3px 3px 0 0;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#061d19;padding:20px 24px 28px;text-align:center;">
                            <p style="margin:0;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:11px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:#b8ff90;">
                                {{ config('app.name') }}
                            </p>
                            <h1 style="margin:14px 0 0;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:24px;line-height:1.3;font-weight:700;color:#ffffff;">
                                @yield('heading')
                            </h1>
                            @hasSection('subheading')
                            <p style="margin:12px 0 0;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.5;color:rgba(255,255,255,0.78);">
                                @yield('subheading')
                            </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff;padding:32px 28px;border-radius:0 0 12px 12px;border:1px solid #e8e8dc;border-top:none;">
                            <div style="font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#061d19;">
                                @yield('content')
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 16px 8px;text-align:center;">
                            <p style="margin:0;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.5;color:#475569;">
                                © {{ date('Y') }} {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>
                    @hasSection('footer_extra')
                    <tr>
                        <td style="padding:0 16px 24px;text-align:center;">
                            <div style="font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.55;color:#64748b;">
                                @yield('footer_extra')
                            </div>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
