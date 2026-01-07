<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Weekly Summary - {{ $team_name }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">📊 Weekly Summary</h1>
        <p style="color: #e0e7ff; margin: 10px 0 0 0;">{{ $start_date }} - {{ $end_date }}</p>
    </div>

    <div style="background: white; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-bottom: 25px;">Hello Team {{ $team_name }},</p>

        <p style="font-size: 16px; margin-bottom: 25px;">Here's your weekly activity summary:</p>

        <!-- Row 1: Proposals & Portfolio -->
        <div style="margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; padding-right: 10px;">
                        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #0ea5e9;">
                            <div style="color: #0c4a6e; font-size: 14px; font-weight: 600; margin-bottom: 8px;">📝 PROPOSALS CREATED</div>
                            <div style="color: #0ea5e9; font-size: 32px; font-weight: bold;">{{ $proposals_created }}</div>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 10px;">
                        <div style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #a855f7;">
                            <div style="color: #581c87; font-size: 14px; font-weight: 600; margin-bottom: 8px;">💼 PORTFOLIOS ADDED</div>
                            <div style="color: #a855f7; font-size: 32px; font-weight: bold;">{{ $portfolios_added }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Row 2: Lead Open & New -->
        <div style="margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; padding-right: 10px;">
                        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                            <div style="color: #78350f; font-size: 14px; font-weight: 600; margin-bottom: 8px;">🔓 LEADS OPEN</div>
                            <div style="color: #f59e0b; font-size: 32px; font-weight: bold;">{{ $leads_open }}</div>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 10px;">
                        <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <div style="color: #1e3a8a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">🆕 LEADS NEW</div>
                            <div style="color: #3b82f6; font-size: 32px; font-weight: bold;">{{ $leads_new }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Row 3: Lead Won & Lost -->
        <div style="margin-bottom: 25px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; padding-right: 10px;">
                        <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #10b981;">
                            <div style="color: #064e3b; font-size: 14px; font-weight: 600; margin-bottom: 8px;">🎉 LEADS WON</div>
                            <div style="color: #10b981; font-size: 32px; font-weight: bold;">{{ $leads_won }}</div>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 10px;">
                        <div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444;">
                            <div style="color: #7f1d1d; font-size: 14px; font-weight: 600; margin-bottom: 8px;">📉 LEADS LOST</div>
                            <div style="color: #ef4444; font-size: 32px; font-weight: bold;">{{ $leads_lost }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if($leads_won > 0)
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 16px; margin: 25px 0;">
            <p style="margin: 0; color: #065f46;">
                <strong>🌟 Great work this week!</strong><br>
                You've successfully won {{ $leads_won }} lead{{ $leads_won > 1 ? 's' : '' }}. Keep up the excellent momentum!
            </p>
        </div>
        @endif

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background: #3b82f6; color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View Dashboard</a>
        </p>

        <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 30px;">
            <p style="color: #6b7280; font-size: 14px; margin: 0;">
                This is your weekly activity summary. Keep tracking your progress and achieving your goals!
            </p>
        </div>

        <p style="margin-top: 30px;">Best regards,<br>The {{ config('app.name') }} Team</p>
    </div>

    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        <p>You're receiving this because you're an admin of {{ $team_name }}.</p>
        <p>To manage your notification preferences, visit your <a href="{{ url('/admin/notification-preferences') }}" style="color: #3b82f6;">settings page</a>.</p>
    </div>
</body>
</html>

