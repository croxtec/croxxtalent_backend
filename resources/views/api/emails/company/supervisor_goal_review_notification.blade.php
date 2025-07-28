
@extends('api.emails.layouts.master')

@section('email_page_title')
    {{ __('notifications.supervisor_goal_review.email_title') }} - {{ config('myapp.name') }}
@endsection

@section('email_body')
    <p>
        {!! __('notifications.supervisor_goal_review.greeting', ['name' => $employee->name], $locale) !!}
        <br><br>
        {!! __('notifications.supervisor_goal_review.message', [
            'supervisor_name' => $supervisor->name,
            'goal_title' => $goal->title
        ], $locale) !!}
        <br><br>
        <strong>{{ __('notifications.supervisor_goal_review.your_assessment', [], $locale) }}:</strong> 
        {{ __('goals.status.' . $goal->employee_status, [], $locale) }}
        <br>
        <strong>{{ __('notifications.supervisor_goal_review.supervisor_decision', [], $locale) }}:</strong> 
        {{ __('goals.status.' . $goal->supervisor_status, [], $locale) }}
        <br><br>
        @if($goal->supervisor_comment)
            <strong>{{ __('notifications.supervisor_goal_review.supervisor_comment', [], $locale) }}:</strong><br>
            {{ $goal->supervisor_comment }}
            <br><br>
        @endif
        {{ __('notifications.supervisor_goal_review.instruction', [], $locale) }}
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => __('notifications.supervisor_goal_review.button_text', [], $locale),
            'button_url' => $buttonUrl
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection