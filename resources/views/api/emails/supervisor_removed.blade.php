<!-- @extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Hi {{$supervisor->name}},
    <p>You have a supervisor update .</p>
    <p>You have been removed has  {{$supervisor?->department?->job_code}} supervisor.</p>
    <p>Thank you for using our application!</p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
 -->

@extends('api.emails.layouts.master')

@section('email_page_title')
    {{ __('emails.notification_title', ['app' => config('app.name')], $locale) }}
@endsection

@section('email_body')
    {{ __('notifications.supervisor.removed.greeting', ['name' => $supervisor->name], $locale) }},
    <p>{{ __('emails.update_notification', [], $locale) }}</p>
    <p>{{ __('notifications.supervisor.removed.body', [
        'job_code' => $supervisor?->department?->job_code
    ], $locale) }}</p>
    <p>{{ __('notifications.supervisor.removed.closing', [], $locale) }}</p>
@endsection