@extends('api.emails.layouts.master')

@section('email_page_title')
{{ __('notifications.assessment_published.page_title', [], $locale) }} - {{ config('myapp.name') }}
@endsection

@section('email_body')
<p>
    {{ __('notifications.assessment_published.greeting', ['name' => $employee->name], $locale) }}
    <br><br>
    @if($role === 'supervisor')
        {{ __('notifications.assessment_published.supervisor_intro', ['assessment_name' => $assessment->name], $locale) }}
    @else
        {{ __('notifications.assessment_published.employee_intro', ['assessment_name' => $assessment->name], $locale) }}
    @endif
    <br><br>
    {{ __('notifications.assessment_published.importance_message', [], $locale) }}
</p>
<p>
    @include('api.emails.layouts.partials.button_primary', [
        'button_text' => __('notifications.assessment_published.access_button', [], $locale),
        'button_url' => config('myapp.employee_url')
    ])
</p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection
