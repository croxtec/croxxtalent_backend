@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $jobInvitation->talentCv->name }},

You have a new job invitation/offer from {{ $jobInvitation->employerUser->display_name }}.

Please login to your {{ config('myapp.name') }} account to view details and accept offer.
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection
