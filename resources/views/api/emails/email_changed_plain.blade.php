@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},

You've successfully changed your {{ config('myapp.name') }} account email address
from {{ $old_email }} to {{ $new_email }}.
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection

@section('client_geo_location')
@include('api.emails.layouts.partials.client_geo_location_plain')
@endsection
