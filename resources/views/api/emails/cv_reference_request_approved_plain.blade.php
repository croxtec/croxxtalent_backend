@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $cvReference->cv->name }},

Your CV reference request has been approved by {{ $cvReference->name }} on {{ config('myapp.name') }}.
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection
