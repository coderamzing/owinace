<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ $team['name'] }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%); padding: 24px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Welcome to {{ $team['name'] }}</h1>
    </div>

    <div style="background: #f9f9f9; padding: 24px; border-radius: 0 0 10px 10px;">
        <p>Hi {{ $user['name'] ?? 'there' }},</p>

        <p>You've been added to the <strong>{{ $team['name'] }}</strong> team. Use the credentials below to sign in and get started:</p>

        <div style="background: white; padding: 16px; border-left: 4px solid #3b82f6; margin: 20px 0;">
            <p style="margin: 0;"><strong>Email:</strong> {{ $user['email'] }}</p>
            <p style="margin: 6px 0 0 0;"><strong>Password:</strong> {{ $password }}</p>
        </div>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $url ?? '#' }}" style="background: #111827; color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">Sign in</a>
        </p>

        <p style="font-size: 13px; color: #555;">For security, please sign in and change your password after your first login.</p>

        <p style="margin-top: 30px;">See you inside,<br>The {{ config('app.name') }} Team</p>
    </div>
</body>
</html>

