@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},

Please be informed that your {{ config('myapp.name') }} account was successfully logged in to from a new device.
You're getting this email notification to make sure it was you.
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection

@section('client_geo_location')
@include('api.emails.layouts.partials.client_geo_location_plain')
@endsection
