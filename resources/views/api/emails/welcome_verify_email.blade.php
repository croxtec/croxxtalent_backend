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
        {{ __('notifications.email_templates.profile_registered', ['app_name' => config('myapp.name')]) }}
        <br><br>
        {{ __('notifications.email_templates.verify_email_instruction') }}
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => __('notifications.email_templates.verify_button_text'),
            'button_url' => $verification_url
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
