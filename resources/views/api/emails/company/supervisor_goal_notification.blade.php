@extends('api.emails.layouts.master')

@section('email_page_title')
    New Goal Notification - {{ config('myapp.name') }}
@endsection

@section('email_body')
    <p>
        Dear {{ $employee->name }},
        <br><br>
        Your supervisor, **{{ $supervisor->name }}**, has assigned a new goal to you: **"{{ $goal->title }}"**.
        <br><br>
        Please log in to the platform to review the goal and start working towards completing it. This goal is an important part of your performance and growth within the company.
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => 'View Goal',
            'button_url' => $buttonUrl
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
