@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},

We received a request to reset your {{ config('myapp.name') }} password.

Your Password Reset Code is {{ $verification_token }}.

This code is valid for 30 minutes or until a next code is generated.
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection
