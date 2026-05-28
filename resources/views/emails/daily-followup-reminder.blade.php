@extends('emails.layout')

@section('title', 'Follow-up reminder')

@section('preheader')
    {{ $total_leads }} lead{{ $total_leads > 1 ? 's' : '' }} need follow-up in the next 24 hours.
@endsection

@section('heading', 'Follow-up reminder')

@section('subheading')
    {{ $date }}
@endsection

@section('content')
    <p style="margin:0 0 16px;">Hello {{ $member_name }},</p>

    <p style="margin:0 0 20px;">
        You have <strong>{{ $total_leads }}</strong> lead{{ $total_leads > 1 ? 's' : '' }} with follow-up in the next 24 hours:
    </p>

    @foreach($leads as $lead)
    <div style="background-color:{{ $lead['is_urgent'] ? '#fff5f5' : '#f6f7fb' }};border-left:4px solid {{ $lead['is_urgent'] ? '#ef4444' : '#b8ff90' }};padding:20px;margin-bottom:18px;border-radius:0 10px 10px 0;border:1px solid #e6e8ef;border-left-width:4px;">
        <div style="margin-bottom:14px;">
            <h3 style="margin:0 0 8px;font-size:17px;font-weight:700;color:#061d19;">
                {{ $lead['title'] }}
                @if($lead['is_urgent'])
                <span style="background-color:#ef4444;color:#ffffff;padding:3px 10px;font-size:11px;border-radius:999px;font-weight:600;margin-left:8px;vertical-align:middle;">Urgent</span>
                @endif
            </h3>
            <p style="margin:0;font-size:14px;color:#475569;">
                <strong>Status:</strong> {{ $lead['kanban_name'] }}
                <span style="color:#cbd5e1;">&nbsp;|&nbsp;</span>
                <strong>Source:</strong> {{ $lead['source_name'] }}
            </p>
        </div>

        <div style="background-color:#ffffff;padding:14px 16px;border-radius:8px;margin-bottom:12px;border:1px solid #e6e8ef;">
            <div style="margin-bottom:8px;font-size:14px;">
                <span style="color:#475569;font-size:13px;">Follow-up:</span>
                <span style="color:#061d19;font-weight:600;">{{ $lead['next_follow_up'] }}</span>
                <span style="color:{{ $lead['is_urgent'] ? '#ef4444' : '#061d19' }};font-size:13px;margin-left:8px;">(in {{ $lead['hours_until'] }} hours)</span>
            </div>

            @if($lead['expected_value'])
            <div style="margin-bottom:8px;font-size:14px;">
                <span style="color:#475569;font-size:13px;">Expected value:</span>
                <span style="color:#061d19;font-weight:700;">${{ number_format($lead['expected_value'], 2) }}</span>
            </div>
            @endif

            @if($lead['team_name'])
            <div style="font-size:14px;">
                <span style="color:#475569;font-size:13px;">Team:</span>
                <span style="color:#061d19;">{{ $lead['team_name'] }}</span>
            </div>
            @endif
        </div>

        @if(!empty($lead['contacts']))
        <div style="background-color:#f8fafc;padding:12px 14px;border-radius:8px;margin-bottom:12px;border:1px solid #e6e8ef;">
            <div style="color:#061d19;font-size:13px;font-weight:600;margin-bottom:8px;">Contacts</div>
            @foreach($lead['contacts'] as $contact)
            <div style="color:#475569;font-size:13px;margin-bottom:4px;">
                <strong style="color:#061d19;">{{ $contact['name'] }}</strong>
                @if($contact['email'])
                &nbsp;&middot;&nbsp;{{ $contact['email'] }}
                @endif
                @if($contact['phone'])
                &nbsp;&middot;&nbsp;{{ $contact['phone'] }}
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if($lead['description'])
        <div style="color:#475569;font-size:14px;margin-bottom:12px;padding:12px 14px;background-color:#f8faf8;border-radius:8px;">
            <strong style="color:#061d19;">Description:</strong><br>
            {{ Str::limit($lead['description'], 150) }}
        </div>
        @endif

        @if($lead['notes'])
        <div style="color:#475569;font-size:13px;margin-bottom:12px;padding:12px 14px;background-color:#f6f7fb;border-radius:8px;border:1px dashed #cbd5e1;">
            <strong style="color:#061d19;">Notes:</strong><br>
            {{ Str::limit($lead['notes'], 150) }}
        </div>
        @endif

        <table role="presentation" align="center" cellpadding="0" cellspacing="0" style="margin:16px auto 0;border-collapse:collapse;">
            <tr>
                <td style="border-radius:8px;background-color:{{ $lead['is_urgent'] ? '#ef4444' : '#061d19' }};">
                    <a href="{{ $lead['url'] }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:10px 22px;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">View lead</a>
                </td>
            </tr>
        </table>
    </div>
    @endforeach

    @php
        $urgentCount = collect($leads)->where('is_urgent', true)->count();
    @endphp

    @if($urgentCount > 0)
    <div style="background-color:#fff5f5;border-left:4px solid #ef4444;padding:16px 18px;margin:24px 0;border-radius:0 8px 8px 0;">
        <p style="margin:0;font-size:14px;line-height:1.55;color:#7f1d1d;">
            <strong>Urgent:</strong>
            {{ $urgentCount }} lead{{ $urgentCount > 1 ? 's need' : ' needs' }} follow-up within two hours. Please prioritize.
        </p>
    </div>
    @endif

    @include('emails.partials.centered-button', ['url' => $url, 'label' => 'View all leads'])

    <div style="border-top:1px solid #e6e8ef;padding-top:22px;margin-top:28px;">
        <p style="margin:0;font-size:14px;line-height:1.55;color:#475569;">
            Timely follow-ups improve conversions. Reach out while leads are still warm.
        </p>
    </div>

    <p style="margin:28px 0 0;">Best regards,<br>The {{ config('app.name') }} team</p>
@endsection

@section('footer_extra')
    <p style="margin:0 0 8px;">You are receiving this because you have leads with upcoming follow-ups.</p>
    <p style="margin:0;">
        Notification preferences:
        <a href="{{ url('/admin/notification-preferences') }}" style="color:#061d19;font-weight:600;">settings</a>
    </p>
@endsection
