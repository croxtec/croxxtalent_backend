@extends('api.emails.layouts.master')

@section('email_page_title')
    {{ __('notifications.supervisor.page_title', [], $locale) }} - {{ config('myapp.name') }}
@endsection

@section('email_body')
<div class="email-container">
    <h1>{{ __('notifications.supervisor.added.subject', [], $locale) }}</h1>
    
    <p>{{ __('notifications.supervisor.added.greeting', ['name' => $supervisor->name], $locale) }}</p>
    
    <div class="supervisor-info">
        <h3>{{ __('notifications.supervisor.details', [], $locale) }}</h3>
        <ul>
            <li><strong>{{ __('notifications.supervisor.name', [], $locale) }}:</strong> {{ $supervisor->name }}</li>
            @if($supervisor->department)
                <li><strong>{{ __('notifications.supervisor.department', [], $locale) }}:</strong> {{ $supervisor->department->name }}</li>
                <li><strong>{{ __('notifications.supervisor.job_code', [], $locale) }}:</strong> {{ $supervisor->department->job_code }}</li>
            @endif
        </ul>
    </div>
    
    <p>{{ __('notifications.supervisor.added.message', [], $locale) }}</p>
    
    <div class="cta-button">
        <a href="{{ config('app.frontend_url') }}/dashboard" class="btn btn-primary">
            {{ __('notifications.supervisor.view_dashboard', [], $locale) }}
        </a>
    </div>
    
    <p class="footer-text">
        {{ __('notifications.supervisor.added.footer', [], $locale) }}
    </p>
</div>
@endsection
@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
