@extends('emails.layout')

@section('title', 'Team Invitation')

@section('preheader')
    Invitation to join {{ $invitation->team->name }} on {{ config('app.name') }}.
@endsection

@section('heading', "You're invited")

@section('content')
    <p style="margin:0 0 16px;">Hello,</p>

    <p style="margin:0 0 16px;">
        You have been invited to join <strong>{{ $invitation->team->name }}</strong> in the <strong>{{ $invitation->workspace->name }}</strong> workspace.
    </p>

    @if($invitation->team->description)
    <div style="background-color:#f3f3e5;border-left:4px solid #b8ff90;padding:16px 18px;margin:24px 0;border-radius:0 8px 8px 0;">
        <p style="margin:0;color:#061d19;font-size:15px;line-height:1.55;">{{ $invitation->team->description }}</p>
    </div>
    @endif

    @include('emails.partials.centered-button', ['url' => $acceptUrl, 'label' => 'Accept invitation'])

    <p style="margin:24px 0 0;font-size:13px;line-height:1.55;color:#475569;">
        Or copy and paste this link into your browser:<br>
        <a href="{{ $acceptUrl }}" style="color:#061d19;font-weight:500;word-break:break-all;">{{ $acceptUrl }}</a>
    </p>

    <p style="margin:20px 0 0;font-size:13px;line-height:1.55;color:#64748b;">
        This invitation expires on {{ $invitation->expires_at->format('F j, Y g:i A') }}.
    </p>

    <p style="margin:28px 0 0;font-size:14px;color:#475569;">
        If you did not expect this invitation, you can safely ignore this email.
    </p>
@endsection
