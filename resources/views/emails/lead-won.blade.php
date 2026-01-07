<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lead Won - {{ $lead_title }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 24px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">🎉 Lead Won!</h1>
    </div>

    <div style="background: #f9f9f9; padding: 24px; border-radius: 0 0 10px 10px;">
        <p>Great news, Team!</p>

        <p>We've successfully won a lead! Here are the details:</p>

        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 16px 0; color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 10px;">{{ $lead_title }}</h2>
            
            @if(isset($lead_description) && $lead_description)
            <p style="color: #666; margin: 12px 0;"><em>{{ $lead_description }}</em></p>
            @endif

            <div style="margin-top: 16px;">
                <p style="margin: 8px 0;"><strong>💰 Value:</strong> <span style="color: #10b981; font-size: 18px; font-weight: bold;">${{ $actual_value }}</span></p>
                <p style="margin: 8px 0;"><strong>👤 Assigned Member:</strong> {{ $assigned_member }}</p>
                <p style="margin: 8px 0;"><strong>🎯 Converted By:</strong> {{ $converted_by }}</p>
                <p style="margin: 8px 0;"><strong>📅 Conversion Date:</strong> {{ $conversion_date }}</p>
                @if(isset($team_name))
                <p style="margin: 8px 0;"><strong>👥 Team:</strong> {{ $team_name }}</p>
                @endif
            </div>
        </div>

        <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 16px; margin: 20px 0;">
            <p style="margin: 0; color: #065f46;">
                <strong>🌟 Congratulations!</strong><br>
                This is a significant achievement for our team. Keep up the excellent work!
            </p>
        </div>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $lead_url }}" style="background: #10b981; color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View Lead Details</a>
        </p>

        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            Keep up the momentum and let's continue delivering great results together!
        </p>

        <p style="margin-top: 30px;">Best regards,<br>The {{ config('app.name') }} Team</p>
    </div>

    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        <p>You're receiving this because you're a member of {{ $team_name ?? 'the team' }}.</p>
        <p>To manage your notification preferences, visit your <a href="{{ url('/admin/notification-preferences') }}" style="color: #3b82f6;">settings page</a>.</p>
    </div>
</body>
</html>

