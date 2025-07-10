@extends('api.emails.layouts.master')

@section('email_page_title')
    {{ __('notifications.email_templates.notification_title', ['app_name' => config('myapp.name')]) }} - {{ __('notifications.assessment_published.page_title', [], $locale) }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    {{ __('notifications.email_templates.greeting', ['name' => $name], $locale) }}
    <br>
    <p>
        {!! __('notifications.job_invitation.accepted_message', ['talent_name' => $jobInvitation->talentCv->name], $locale) !!}
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection