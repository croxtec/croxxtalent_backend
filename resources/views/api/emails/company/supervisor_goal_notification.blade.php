@extends('api.emails.layouts.master')

@section('email_page_title')
    New Goal Notification - {{ config('myapp.name') }}
@endsection

@section('email_body')
    <p>
        {!! __('notifications.supervisor_goal.greeting', ['name' => $employee->name]) !!}
        <br><br>
        {!! __('notifications.supervisor_goal.message', [
            'supervisor_name' => $supervisor->name,
            'goal_title' => $goal->title
        ]) !!}
        <br><br>
        {{ __('notifications.supervisor_goal.instruction') }}
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => __('notifications.supervisor_goal.button_text'),
            'button_url' => $buttonUrl
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection