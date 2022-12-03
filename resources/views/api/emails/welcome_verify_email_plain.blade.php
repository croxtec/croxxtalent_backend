@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},

Your profile has been registered with {{ config('myapp.name') }} ({{ config('myapp.url') }}).

Simply click or copy the link below to verify your email address.

{{ $verification_url }}
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection
