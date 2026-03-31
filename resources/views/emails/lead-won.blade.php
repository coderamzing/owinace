@extends('emails.layout')

@section('title')
    Lead won — {{ $lead_title }}
@endsection

@section('preheader')
    Lead won: {{ $lead_title }} — ${{ $actual_value }} on {{ config('app.name') }}.
@endsection

@section('heading', 'Lead won')

@section('content')
    <p style="margin:0 0 16px;">Great news, team,</p>

    <p style="margin:0 0 20px;">A lead was marked <strong>won</strong>. Here are the details.</p>

    <div style="background-color:#f3f3e5;border:1px solid #e8e8dc;border-radius:10px;padding:22px 22px;margin:20px 0;">
        <h2 style="margin:0 0 14px;font-size:20px;font-weight:700;color:#061d19;padding-bottom:12px;border-bottom:2px solid #b8ff90;">
            {{ $lead_title }}
        </h2>

        @if(isset($lead_description) && $lead_description)
        <p style="margin:0 0 14px;font-size:15px;line-height:1.55;color:#475569;font-style:italic;">{{ $lead_description }}</p>
        @endif

        <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:15px;line-height:1.55;">
            <tr>
                <td style="padding:6px 0;color:#475569;width:160px;">Value</td>
                <td style="padding:6px 0;color:#061d19;font-weight:700;">${{ $actual_value }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#475569;">Assigned member</td>
                <td style="padding:6px 0;color:#061d19;">{{ $assigned_member }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#475569;">Converted by</td>
                <td style="padding:6px 0;color:#061d19;">{{ $converted_by }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#475569;">Conversion date</td>
                <td style="padding:6px 0;color:#061d19;">{{ $conversion_date }}</td>
            </tr>
            @if(isset($team_name))
            <tr>
                <td style="padding:6px 0;color:#475569;">Team</td>
                <td style="padding:6px 0;color:#061d19;">{{ $team_name }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="background-color:#ecfdf3;border-left:4px solid #16a34a;padding:16px 18px;margin:24px 0;border-radius:0 8px 8px 0;">
        <p style="margin:0;font-size:14px;line-height:1.55;color:#14532d;">
            <strong>Congratulations</strong> &mdash; this is a strong result. Share the win with your team.
        </p>
    </div>

    @include('emails.partials.centered-button', ['url' => $lead_url, 'label' => 'View lead'])

    <p style="margin:20px 0 0;font-size:14px;line-height:1.55;color:#475569;">
        Keep the momentum going on the rest of your pipeline.
    </p>

    <p style="margin:28px 0 0;">Best regards,<br>The {{ config('app.name') }} team</p>
@endsection

@section('footer_extra')
    <p style="margin:0 0 8px;">You are receiving this because you are a member of {{ $team_name ?? 'the team' }}.</p>
    <p style="margin:0;">
        <a href="{{ url('/admin/notification-preferences') }}" style="color:#061d19;font-weight:600;">Notification preferences</a>
    </p>
@endsection
