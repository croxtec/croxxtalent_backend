@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Hi {{ $jobInvitation->talentCv->name }},
    <br>
    <p>
        You have a new job invitation/offer from <b>{{ $jobInvitation->employerUser->display_name }}</b>.
    </p>
    <p>
        <b>{{ $jobInvitation->employerUser->display_name }}</b> has schedule an interview with you at {{ $jobInvitation->interview_at }}
    </p>
    <p>
        Please login to your <a href="{{ config('myapp.client_url') }}" target="_blank">{{ config('myapp.name') }}</a> account to view details and accept offer.
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
