@extends('api.emails.layouts.master')

@section('email_page_title')
    {{ __('notifications.email_templates.notification_title', ['app_name' => config('myapp.name')]) }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    {{ __('notifications.email_templates.greeting', ['name' => $name]) }}
    <br>
    <p>
        {{ __('notifications.profile_change.notification_message', ['app_name' => config('myapp.name')]) }}
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection

@section('client_geo_location')
    @include('api.emails.layouts.partials.client_geo_location')
@endsection