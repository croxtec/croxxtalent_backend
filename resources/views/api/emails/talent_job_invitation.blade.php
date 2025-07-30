@extends('api.emails.layouts.master')

@section('email_page_title')
{{ __('notifications.job_invitation.page_title', [], $locale) }} - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
{{ __('notifications.job_invitation.greeting', ['name' => $jobInvitation->talentCv->name], $locale) }}
<br>
<p>
    {{ __('notifications.job_invitation.message', ['employer_name' => $jobInvitation->employerUser->display_name], $locale) }}
</p>
<p>
    {{ __('notifications.job_invitation.interview_details', [
        'employer_name' => $jobInvitation->employerUser->display_name,
        'interview_time' => $jobInvitation->interview_at
    ], $locale) }}
</p>
<p>
    {{ __('notifications.job_invitation.login_instruction', ['app_name' => config('myapp.name')], $locale) }}
    <a href="{{ config('myapp.client_url') }}" target="_blank">{{ config('myapp.name') }}</a>
</p>
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close')
@endsection
