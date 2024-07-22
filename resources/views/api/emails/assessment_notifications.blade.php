@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Hi ,
    <br>
    <h1>{{ $message }}</h1>
    <p>You have been assigned a new assessment. Please log in to view the details.</p>
    <p><a href="{{ url('/assessments/' . $assessment->code) }}">View Assessment</a></p>
    <p>Thank you for using our application!</p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection

@section('client_geo_location')
    @include('api.emails.layouts.partials.client_geo_location')
@endsection
