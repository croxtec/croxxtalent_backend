@extends('api.emails.layouts.master')

@section('email_page_title')
    Assessment Feedback Notification - {{ config('myapp.name') }}
@endsection

@section('email_body')
    <p>
        {!! __('notifications.assessment_feedback.greeting', ['name' => $employee->name]) !!}
        <br><br>
        {!! __('notifications.assessment_feedback.message', [
            'assessment_name' => $assessment->name,
            'code' => $assessment->code
        ]) !!}
        <br><br>
        {{ __('notifications.assessment_feedback.encouragement') }}
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => __('notifications.assessment_feedback.button_text'),
            'button_url' => config('myapp.employee_url')
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection