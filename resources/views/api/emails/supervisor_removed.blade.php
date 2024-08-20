@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Hi {{$supervisor->name}},
    <p>You have a supervisor update .</p>
    <br>
    <p>You have been removed has  {{$supervisor?->department?->job_code}};</p>
    <p>Thank you for using our application!</p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
