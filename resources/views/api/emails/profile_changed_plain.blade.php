@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},

Changes has been made to your {{ config('myapp.name') }} profile information.
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection

@section('client_geo_location')
@include('api.emails.layouts.partials.client_geo_location_plain')
@endsection
