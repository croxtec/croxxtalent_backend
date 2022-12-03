@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},

Your job invitation/offer was rejected by {{ $jobInvitation->talentCv->name }}.
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection
