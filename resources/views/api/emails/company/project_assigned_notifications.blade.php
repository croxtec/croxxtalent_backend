{{-- api.emails.company.project_notifications.blade.php --}}
@extends('api.emails.layouts.master')

@section('email_page_title')
    New Project Assignment - {{ config('myapp.name') }}
@endsection

@section('email_body')
    <p>
        {{ $messageContent }}
        <br><br>
        You have been added to this project team and your contribution is essential to its success.
        Please log into the platform to view project details and your assigned tasks.
    </p>
    <p style="margin-top: 20px;">
        <b>Project Details:</b><br>
        Title: {{ $project->title }}<br>
        @if(isset($project->start_date))
            Start Date: {{ $project->start_date }}<br>
        @endif
        @if(isset($project->end_date))
            Expected Completion: {{ $project->end_date }}<br>
        @endif
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => 'View Project Details',
            'button_url' => config('myapp.employee_url')
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection