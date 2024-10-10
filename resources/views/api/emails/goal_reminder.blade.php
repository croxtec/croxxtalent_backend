@extends('api.emails.layouts.master')

@section('email_page_title')
    Goal Reminder - {{ config('app.name') }}
@endsection

@section('email_body')
    <p>
        Hello {{ $name }},
        <br><br>
        This is a friendly reminder about the goal assigned to you: <strong>"{{ $goal->title }}"</strong>.
        <br><br>
        This goal was set to help you achieve significant progress in your role and contribute to the company’s success.
    </p>
    <p>
        <strong>Goal Metric:</strong> {{ $goal->metric }}
    </p>
    <p>
        As the deadline approaches, we encourage you to review the progress you have made so far. Completing this goal will help in your personal development and will ensure that the team achieves its objectives.
    </p>
    <p>
        If you need any assistance or clarification, feel free to reach out to your supervisor. Keep pushing forward, and we are confident you will meet this goal!
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => 'View Your Goal',
            'button_url' => url("")
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
