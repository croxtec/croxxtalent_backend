@extends('api.emails.layouts.master')

@section('email_page_title')
    New Assessment Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Hi ,
    <br>
    <p>
        {{ $message }} <br><br>
        You can view the details of the assessment by logging into the platform.
        <br><br>
        <a href="{{ config('myapp.url') }}" target="_blank">Click here to view the assessment details</a>.
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
