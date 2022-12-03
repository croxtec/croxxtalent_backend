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
        Please be informed that your <a href="{{ config('myapp.url') }}" target="_blank">{{ config('myapp.name') }}</a>
        account was successfully logged in to from a new device.<br>
        You're getting this email notification to make sure it was you.
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection

@section('client_geo_location')
    @include('api.emails.layouts.partials.client_geo_location')
@endsection
