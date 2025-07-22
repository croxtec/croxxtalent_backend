
@extends('api.emails.layouts.master')

@section('email_page_title')
    {{ __('notifications.employee_goal_submission.email_title') }} - {{ config('myapp.name') }}
@endsection

@section('email_body')
    <p>
        {!! __('notifications.employee_goal_submission.greeting', ['name' => $supervisor->name], $locale) !!}
        <br><br>
        {!! __('notifications.employee_goal_submission.message', [
            'employee_name' => $employee->name,
            'goal_title' => $goal->title,
            'status' => __('goals.status.' . $goal->employee_status, [], $locale)
        ], $locale) !!}
        <br><br>
        @if($goal->employee_comment)
            <strong>{{ __('notifications.employee_goal_submission.employee_comment', [], $locale) }}:</strong><br>
            {{ $goal->employee_comment }}
            <br><br>
        @endif
        {{ __('notifications.employee_goal_submission.instruction', [], $locale) }}
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => __('notifications.employee_goal_submission.button_text', [], $locale),
            'button_url' => $buttonUrl
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection

