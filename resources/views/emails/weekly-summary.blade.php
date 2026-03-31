@extends('emails.layout')

@section('title')
    Weekly summary — {{ $team_name }}
@endsection

@section('preheader')
    Weekly results for {{ $team_name }}: {{ $start_date }} &ndash; {{ $end_date }}.
@endsection

@section('heading', 'Weekly summary')

@section('subheading')
    {{ $start_date }} &ndash; {{ $end_date }}
@endsection

@section('content')
    <p style="margin:0 0 16px;">Hello team {{ $team_name }},</p>

    <p style="margin:0 0 20px;">Here is your weekly activity on {{ config('app.name') }}.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:16px;">
        <tr>
            <td style="width:50%;padding:0 8px 16px 0;vertical-align:top;">
                <div style="background-color:#f3f3e5;padding:18px;border-radius:10px;border-left:4px solid #b8ff90;">
                    <div style="font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#475569;margin-bottom:8px;">Proposals created</div>
                    <div style="font-size:30px;font-weight:700;color:#061d19;line-height:1;">{{ $proposals_created }}</div>
                </div>
            </td>
            <td style="width:50%;padding:0 0 16px 8px;vertical-align:top;">
                <div style="background-color:#f3f3e5;padding:18px;border-radius:10px;border-left:4px solid #061d19;">
                    <div style="font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#475569;margin-bottom:8px;">Portfolios added</div>
                    <div style="font-size:30px;font-weight:700;color:#061d19;line-height:1;">{{ $portfolios_added }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width:50%;padding:0 8px 16px 0;vertical-align:top;">
                <div style="background-color:#f3f3e5;padding:18px;border-radius:10px;border-left:4px solid #94a3b8;">
                    <div style="font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#475569;margin-bottom:8px;">Leads open</div>
                    <div style="font-size:30px;font-weight:700;color:#061d19;line-height:1;">{{ $leads_open }}</div>
                </div>
            </td>
            <td style="width:50%;padding:0 0 16px 8px;vertical-align:top;">
                <div style="background-color:#f3f3e5;padding:18px;border-radius:10px;border-left:4px solid #b8ff90;">
                    <div style="font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#475569;margin-bottom:8px;">Leads new</div>
                    <div style="font-size:30px;font-weight:700;color:#061d19;line-height:1;">{{ $leads_new }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width:50%;padding:0 8px 0 0;vertical-align:top;">
                <div style="background-color:#ecfdf3;padding:18px;border-radius:10px;border-left:4px solid #16a34a;">
                    <div style="font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#475569;margin-bottom:8px;">Leads won</div>
                    <div style="font-size:30px;font-weight:700;color:#061d19;line-height:1;">{{ $leads_won }}</div>
                </div>
            </td>
            <td style="width:50%;padding:0 0 0 8px;vertical-align:top;">
                <div style="background-color:#fef2f2;padding:18px;border-radius:10px;border-left:4px solid #ef4444;">
                    <div style="font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#475569;margin-bottom:8px;">Leads lost</div>
                    <div style="font-size:30px;font-weight:700;color:#061d19;line-height:1;">{{ $leads_lost }}</div>
                </div>
            </td>
        </tr>
    </table>

    @if($leads_won > 0)
    <div style="background-color:#ecfdf3;border-left:4px solid #16a34a;padding:16px 18px;margin:24px 0;border-radius:0 8px 8px 0;">
        <p style="margin:0;font-size:14px;line-height:1.55;color:#14532d;">
            <strong>Nice work</strong> &mdash; you won {{ $leads_won }} lead{{ $leads_won > 1 ? 's' : '' }} this week. Keep the momentum.
        </p>
    </div>
    @endif

    @include('emails.partials.centered-button', ['url' => $url, 'label' => 'Open dashboard'])

    <div style="border-top:1px solid #e8e8dc;padding-top:22px;margin-top:28px;">
        <p style="margin:0;font-size:14px;line-height:1.55;color:#475569;">
            Weekly snapshot for admins of {{ $team_name }}.
        </p>
    </div>

    <p style="margin:28px 0 0;">Best regards,<br>The {{ config('app.name') }} team</p>
@endsection

@section('footer_extra')
    <p style="margin:0 0 8px;">You are receiving this because you are an admin of {{ $team_name }}.</p>
    <p style="margin:0;">
        <a href="{{ url('/admin/notification-preferences') }}" style="color:#061d19;font-weight:600;">Notification preferences</a>
    </p>
@endsection
