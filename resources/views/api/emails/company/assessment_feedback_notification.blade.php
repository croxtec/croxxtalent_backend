@extends('api.emails.layouts.master')

@section('email_page_title')
    Assessment Feedback Notification - {{ config('myapp.name') }}
@endsection

@section('email_body')
    <p>
        Dear {{ $employee->name }},
        <br><br>
        Your assessment titled **"{{ $assessment->title }}"** (Code: {{ $assessment->code }}) has been reviewed, and your feedback is now available. This assessment is an important part of your professional development and performance evaluation.
        <br><br>
        We encourage you to carefully review the feedback provided by your supervisor, which contains valuable insights to help guide your career growth. Please take the time to reflect on the feedback and make improvements where necessary.
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => 'View Feedback',
            'button_url' => config('myapp.url')
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
