@extends('emails.layout')

@section('title')
    Welcome to {{ $team['name'] }}
@endsection

@section('preheader')
    Sign-in details for {{ $team['name'] }} on {{ config('app.name') }}.
@endsection

@section('heading')
    Welcome to {{ $team['name'] }}
@endsection

@section('content')
    <p style="margin:0 0 16px;">Hi {{ $user['name'] ?? 'there' }},</p>

    <p style="margin:0 0 16px;">
        You have been added to the <strong>{{ $team['name'] }}</strong> team. Use the credentials below to sign in and get started.
    </p>

    <div style="background-color:#f6f7fb;border-left:4px solid #061d19;padding:18px 20px;margin:24px 0;border-radius:0 8px 8px 0;">
        <p style="margin:0;font-size:15px;"><strong>Email:</strong> {{ $user['email'] }}</p>
        <p style="margin:10px 0 0;font-size:15px;"><strong>Password:</strong> {{ $password }}</p>
    </div>

    @include('emails.partials.centered-button', ['url' => $url ?? '#', 'label' => 'Sign in'])

    <p style="margin:8px 0 0;font-size:13px;line-height:1.55;color:#475569;">
        For security, sign in and change your password after your first login.
    </p>

    <p style="margin:28px 0 0;">See you inside,<br>The {{ config('app.name') }} team</p>
@endsection
