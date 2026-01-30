<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ $workspace }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🎉 Welcome to {{ $workspace }}!</h1>
        <p style="color: #d1fae5; margin: 10px 0 0 0;">Your workspace is ready</p>
    </div>

    <div style="background: white; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-bottom: 25px;">Hello {{ $name }},</p>

        <p style="font-size: 16px; margin-bottom: 25px;">
            Congratulations! Your workspace <strong>{{ $workspace }}</strong> has been successfully created. You're all set to start managing your leads, teams, and projects.
        </p>

        <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 25px 0; border-radius: 6px;">
            <h3 style="margin: 0 0 12px 0; color: #065f46; font-size: 18px;">✨ What's Next?</h3>
            <ul style="margin: 0; padding-left: 20px; color: #047857;">
                <li style="margin-bottom: 8px;">Start managing your leads and contacts</li>
                <li style="margin-bottom: 8px;">Create teams and invite members</li>
                <li style="margin-bottom: 8px;">Track your sales pipeline</li>
                <li style="margin-bottom: 0;">Generate proposals and portfolios</li>
            </ul>
        </div>

        @if(isset($team))
        <div style="background: #f8fafc; padding: 16px; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 0; color: #475569; font-size: 14px;">
                <strong>Default Team:</strong> {{ $team }}<br>
                <span style="color: #64748b; font-size: 13px;">A default team has been created for you to get started.</span>
            </p>
        </div>
        @endif

        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; margin: 25px 0; border-radius: 6px;">
            <p style="margin: 0; color: #1e40af;">
                <strong>💡 Tip:</strong> You can access your workspace anytime by signing in with your email: <strong>{{ $email }}</strong>
            </p>
        </div>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $url ?? url('/dashboard') }}" style="background: #10b981; color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Go to Dashboard</a>
        </p>

        <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 30px;">
            <p style="color: #6b7280; font-size: 14px; margin: 0;">
                If you have any questions or need assistance, don't hesitate to reach out to our support team. We're here to help you succeed!
            </p>
        </div>

        <p style="margin-top: 30px;">Best regards,<br>The {{ config('app.name') }} Team</p>
    </div>

    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        <p>You're receiving this because you created a workspace on {{ config('app.name') }}.</p>
        <p>If you didn't create this workspace, please contact our support team.</p>
    </div>
</body>
</html>
