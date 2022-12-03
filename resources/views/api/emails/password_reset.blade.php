@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Hi {{ $name }},
    <br>
    <p>
        We received a request to reset your <a href="{{ config('myapp.url') }}" target="_blank">{{ config('myapp.name') }}</a> password.
    </p>
    <p>
        Your Password Reset Code is
        <br><br>
        <p style="text-align: center;">
            <b style="font-size: 50px; letter-spacing: 15px;">{{ $verification_token }}</b>
        </p>
        <br>
        This code is valid for 30 minutes or until a next code is generated.
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
