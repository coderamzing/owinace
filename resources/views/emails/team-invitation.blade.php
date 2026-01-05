<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">You're Invited!</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p>Hello,</p>
        
        <p>You have been invited to join <strong>{{ $invitation->team->name }}</strong> team in the <strong>{{ $invitation->workspace->name }}</strong> workspace.</p>
        
        @if($invitation->team->description)
        <p style="background: white; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0;">
            {{ $invitation->team->description }}
        </p>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $acceptUrl }}" 
               style="background: #667eea; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Accept Invitation
            </a>
        </div>
        
        <p style="font-size: 12px; color: #666; margin-top: 30px;">
            Or copy and paste this link into your browser:<br>
            <a href="{{ $acceptUrl }}" style="color: #667eea; word-break: break-all;">{{ $acceptUrl }}</a>
        </p>
        
        <p style="font-size: 12px; color: #999; margin-top: 20px;">
            This invitation will expire on {{ $invitation->expires_at->format('F j, Y g:i A') }}.
        </p>
        
        <p style="margin-top: 30px;">
            If you did not expect this invitation, you can safely ignore this email.
        </p>
    </div>
</body>
</html>

