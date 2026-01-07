<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Follow-up Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">🔔 Follow-up Reminder</h1>
        <p style="color: #fef3c7; margin: 10px 0 0 0;">{{ $date }}</p>
    </div>

    <div style="background: white; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-bottom: 25px;">Hello {{ $member_name }},</p>

        <p style="font-size: 16px; margin-bottom: 25px;">
            You have <strong>{{ $total_leads }}</strong> lead{{ $total_leads > 1 ? 's' : '' }} requiring follow-up in the next 24 hours:
        </p>

        @foreach($leads as $lead)
        <div style="background: {{ $lead['is_urgent'] ? '#fef3c7' : '#f0f9ff' }}; border-left: 4px solid {{ $lead['is_urgent'] ? '#f59e0b' : '#3b82f6' }}; padding: 20px; margin-bottom: 20px; border-radius: 6px;">
            <div style="margin-bottom: 12px;">
                <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 18px;">
                    {{ $lead['title'] }}
                    @if($lead['is_urgent'])
                    <span style="background: #ef4444; color: white; padding: 2px 8px; font-size: 11px; border-radius: 12px; font-weight: normal; margin-left: 8px;">URGENT</span>
                    @endif
                </h3>
                <p style="margin: 0; color: #64748b; font-size: 14px;">
                    <strong>Status:</strong> {{ $lead['kanban_name'] }} | 
                    <strong>Source:</strong> {{ $lead['source_name'] }}
                </p>
            </div>

            <div style="background: white; padding: 15px; border-radius: 4px; margin-bottom: 12px;">
                <div style="margin-bottom: 8px;">
                    <span style="color: #64748b; font-size: 13px;">⏰ Follow-up Time:</span>
                    <span style="color: #1e293b; font-weight: bold; font-size: 14px;">{{ $lead['next_follow_up'] }}</span>
                    <span style="color: {{ $lead['is_urgent'] ? '#ef4444' : '#3b82f6' }}; font-size: 13px; margin-left: 8px;">
                        (in {{ $lead['hours_until'] }} hours)
                    </span>
                </div>

                @if($lead['expected_value'])
                <div style="margin-bottom: 8px;">
                    <span style="color: #64748b; font-size: 13px;">💰 Expected Value:</span>
                    <span style="color: #10b981; font-weight: bold; font-size: 14px;">${{ number_format($lead['expected_value'], 2) }}</span>
                </div>
                @endif

                @if($lead['team_name'])
                <div style="margin-bottom: 8px;">
                    <span style="color: #64748b; font-size: 13px;">👥 Team:</span>
                    <span style="color: #1e293b; font-size: 14px;">{{ $lead['team_name'] }}</span>
                </div>
                @endif
            </div>

            @if(!empty($lead['contacts']))
            <div style="background: #f8fafc; padding: 12px; border-radius: 4px; margin-bottom: 12px;">
                <div style="color: #475569; font-size: 13px; font-weight: bold; margin-bottom: 6px;">📞 Contacts:</div>
                @foreach($lead['contacts'] as $contact)
                <div style="color: #64748b; font-size: 13px; margin-bottom: 4px;">
                    <strong>{{ $contact['name'] }}</strong>
                    @if($contact['email'])
                    | {{ $contact['email'] }}
                    @endif
                    @if($contact['phone'])
                    | {{ $contact['phone'] }}
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($lead['description'])
            <div style="color: #64748b; font-size: 14px; margin-bottom: 12px; padding: 10px; background: #f8fafc; border-radius: 4px;">
                <strong>Description:</strong><br>
                {{ Str::limit($lead['description'], 150) }}
            </div>
            @endif

            @if($lead['notes'])
            <div style="color: #64748b; font-size: 13px; margin-bottom: 12px; padding: 10px; background: #fffbeb; border-radius: 4px;">
                <strong>📝 Notes:</strong><br>
                {{ Str::limit($lead['notes'], 150) }}
            </div>
            @endif

            <p style="text-align: center; margin: 15px 0 0 0;">
                <a href="{{ $lead['url'] }}" style="background: {{ $lead['is_urgent'] ? '#ef4444' : '#3b82f6' }}; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; font-size: 14px;">View Lead Details</a>
            </p>
        </div>
        @endforeach

        @php
            $urgentCount = collect($leads)->where('is_urgent', true)->count();
        @endphp

        @if($urgentCount > 0)
        <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; margin: 25px 0;">
            <p style="margin: 0; color: #7f1d1d;">
                <strong>⚠️ Urgent Reminder!</strong><br>
                {{ $urgentCount }} lead{{ $urgentCount > 1 ? 's require' : ' requires' }} follow-up within the next 2 hours. Please prioritize these leads!
            </p>
        </div>
        @endif

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background: #10b981; color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View All Leads</a>
        </p>

        <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 30px;">
            <p style="color: #6b7280; font-size: 14px; margin: 0;">
                💡 <strong>Tip:</strong> Timely follow-ups significantly increase your conversion rates. Make sure to reach out to your leads promptly!
            </p>
        </div>

        <p style="margin-top: 30px;">Best regards,<br>The {{ config('app.name') }} Team</p>
    </div>

    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        <p>You're receiving this because you have leads assigned to you with upcoming follow-ups.</p>
        <p>To manage your notification preferences, visit your <a href="{{ url('/admin/notification-preferences') }}" style="color: #3b82f6;">settings page</a>.</p>
    </div>
</body>
</html>

