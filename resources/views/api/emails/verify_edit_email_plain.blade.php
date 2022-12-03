@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},
        
We received a request to change the email address associated with your account at {{ config('myapp.name') }} ({{ config('myapp.url') }}).
Please click or copy the link below to confirm that "{{ $email }}" is your email address.

Your account email address will be updated once you’ve verified this email address.

{{ $verification_url }}
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection
