@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},

Your One-Time Password (OTP) is {{ $verification_token }}.

This OTP is valid for 30 minutes or until a next OTP is generated.
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection

@section('client_geo_location')
@include('api.emails.layouts.partials.client_geo_location_plain')
@endsection
