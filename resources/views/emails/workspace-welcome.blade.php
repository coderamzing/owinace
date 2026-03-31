@extends('emails.layout')

@section('title')
    Welcome to {{ $workspace }}
@endsection

@section('preheader')
    Your workspace {{ $workspace }} is ready on {{ config('app.name') }}.
@endsection

@section('heading')
    Welcome to {{ $workspace }}
@endsection

@section('subheading')
    Your workspace is ready.
@endsection

@section('content')
    <p style="margin:0 0 16px;">Hello {{ $name }},</p>

    <p style="margin:0 0 16px;">
        Your workspace <strong>{{ $workspace }}</strong> has been created. You can start managing leads, teams, and your pipeline from your dashboard.
    </p>

    <div style="background-color:#f3f3e5;border-left:4px solid #b8ff90;padding:20px 20px;margin:24px 0;border-radius:0 8px 8px 0;">
        <h2 style="margin:0 0 12px;font-size:17px;font-weight:700;color:#061d19;">What&rsquo;s next</h2>
        <ul style="margin:0;padding:0 0 0 18px;color:#061d19;font-size:15px;line-height:1.55;list-style-type:disc;">
            <li style="margin:0 0 8px;">Manage leads and contacts</li>
            <li style="margin:0 0 8px;">Create teams and invite members</li>
            <li style="margin:0 0 8px;">Track your sales pipeline</li>
            <li style="margin:0;">Build proposals and portfolios</li>
        </ul>
    </div>

    @if(isset($team))
    <div style="background-color:#f8faf8;padding:16px 18px;border-radius:8px;margin:20px 0;border:1px solid #e8e8dc;">
        <p style="margin:0;font-size:14px;color:#061d19;">
            <strong>Default team:</strong> {{ $team }}<br>
            <span style="color:#475569;font-size:13px;">A default team was created so you can get started right away.</span>
        </p>
    </div>
    @endif

    <div style="background-color:#f3f3e5;padding:16px 18px;border-radius:8px;margin:24px 0;border:1px dashed #c5d4c0;">
        <p style="margin:0;font-size:14px;line-height:1.55;color:#061d19;">
            <strong>Tip:</strong> Sign in anytime with <strong>{{ $email }}</strong>.
        </p>
    </div>

    @include('emails.partials.centered-button', ['url' => $url ?? url('/dashboard'), 'label' => 'Open dashboard'])

    <div style="border-top:1px solid #e8e8dc;padding-top:22px;margin-top:28px;">
        <p style="margin:0;font-size:14px;line-height:1.55;color:#475569;">
            Questions? Reply to this email or contact support &mdash; we&rsquo;re here to help.
        </p>
    </div>

    <p style="margin:28px 0 0;">Best regards,<br>The {{ config('app.name') }} team</p>
@endsection

@section('footer_extra')
    <p style="margin:0 0 8px;">You are receiving this because you created a workspace on {{ config('app.name') }}.</p>
    <p style="margin:0;">If you did not create this workspace, please contact support.</p>
@endsection
