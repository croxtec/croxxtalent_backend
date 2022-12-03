@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Hi {{ $cvReference->cv->name }},
    <br>
    <p>
        Your CV reference request has been approved by <b>{{ $cvReference->name }}</b>
        on <a href="{{ config('myapp.url') }}" target="_blank">{{ config('myapp.name') }}</a>.
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
