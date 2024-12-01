@extends('api.emails.layouts.master')

@section('email_page_title')
    New Assessment Notification - {{ config('myapp.name') }}
@endsection

@section('email_body')
    <p>
        {{ $messageContent }}
        <br><br>
        This assessment is an important part of your development and performance evaluation.
        Please log into the platform to complete the assessment.
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => 'Access Your Assessment',
            'button_url' => config('myapp.url')
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
