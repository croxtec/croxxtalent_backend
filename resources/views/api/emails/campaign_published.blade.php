@extends('api.emails.layouts.master')

@section('email_page_title')
    {{ __('notifications.campaign_published.page_title', [], $locale) }} - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    {!! __('notifications.campaign_published.greeting', ['name' => $name], $locale) !!}
    <br>
    <p>
        {!! __('notifications.campaign_published.message', ['title' => $campaign->title], $locale) !!}
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
