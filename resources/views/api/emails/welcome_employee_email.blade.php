@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Hi {{ $name }},
    <br>
    <p>
        Your profile has been linked with  this company <a href="{{ config('myapp.url') }}" target="_blank">{{ config('myapp.name') }}</a>.
        <br><br>
        Simply click the button below  to verify your email address and join their employee list.
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => 'Click here to connect email address',
            'button_url' =>  $verification_url
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
