@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    {!! __('notifications.password_reset.greeting', ['name' => $name]) !!}
    <br>
    <p>
        {!! __('notifications.password_reset.message', [
            'url' => config('myapp.url'),
            'app_name' => config('myapp.name')
        ]) !!}
    </p>
    <p>
        {{ __('notifications.password_reset.code_label') }}
        <br><br>
        <p style="text-align: center;">
            <b style="font-size: 50px; letter-spacing: 15px;">{{ $verification_token }}</b>
        </p>
        <br>
        {{ __('notifications.password_reset.validity') }}
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
