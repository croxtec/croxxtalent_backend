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
        Your One-Time Password (OTP) is
        <br><br>
        <p style="text-align: center;">
            <b style="font-size: 50px; letter-spacing: 15px;">{{ $verification_token }}</b>
        </p>
        <br>
        This OTP is valid for 30 minutes or until a next OTP is generated.
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection

@section('client_geo_location')
    @include('api.emails.layouts.partials.client_geo_location')
@endsection
