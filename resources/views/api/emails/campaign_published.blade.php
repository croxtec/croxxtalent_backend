@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    {!! __('notifications.campaign_published.greeting', ['name' => $name]) !!}
    <br>
    <p>
        {!! __('notifications.campaign_published.message', ['title' => $campaign->title]) !!}
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
