@extends('emails.layout')

@section('title', 'Verify your email')

@section('preheader')
    Verify your email to finish setting up your {{ config('app.name') }} account.
@endsection

@section('heading', 'Verify your email')

@section('subheading')
    One click to secure your account.
@endsection

@section('content')
    <p style="margin:0 0 16px;">Hi {{ $name ?? 'there' }},</p>

    <p style="margin:0 0 16px;">
        Thanks for signing up for <strong>{{ config('app.name') }}</strong>. Please confirm this email address to activate your account.
    </p>

    @include('emails.partials.centered-button', ['url' => $verificationUrl, 'label' => 'Verify email address'])

    <p style="margin:20px 0 0;font-size:13px;line-height:1.55;color:#475569;">
        Or copy and paste this link into your browser:<br>
        <a href="{{ $verificationUrl }}" style="color:#061d19;font-weight:600;word-break:break-all;">{{ $verificationUrl }}</a>
    </p>

    <div style="background-color:#f6f7fb;border:1px solid #e6e8ef;border-radius:10px;padding:16px 18px;margin:24px 0;">
        <p style="margin:0;font-size:13px;line-height:1.55;color:#475569;">
            If you didn’t create an account, you can safely ignore this email.
        </p>
    </div>

    <p style="margin:28px 0 0;">Best regards,<br>The {{ config('app.name') }} team</p>
@endsection

