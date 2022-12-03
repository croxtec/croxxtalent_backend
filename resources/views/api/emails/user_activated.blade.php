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
       Your account has been activated
    </p>
    <p>
        <p>
            @include('api.emails.layouts.partials.button_primary', [
                'button_text' => 'Click to login',
                'button_url' => $button_url
            ])
        </p>
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
