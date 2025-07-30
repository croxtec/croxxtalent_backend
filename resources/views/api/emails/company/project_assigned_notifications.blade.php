@extends('api.emails.layouts.master')

@section('email_page_title')
{{ __('notifications.project_assigned.page_title', [], $locale) }} - {{ config('myapp.name') }}
@endsection

@section('email_body')
<p>
    {{ __('notifications.project_assigned.greeting', ['name' => $employee?->name], $locale) }}
    <br><br>
    @if($role === 'lead')
        {{ __('notifications.project_assigned.lead_intro', ['project_title' => $project->title], $locale) }}
    @else
        {{ __('notifications.project_assigned.employee_intro', ['project_title' => $project->title], $locale) }}
    @endif
    <br><br>
    {{ __('notifications.project_assigned.contribution_message', [], $locale) }}
</p>
<p style="margin-top: 20px;">
    <b>{{ __('notifications.project_assigned.details_label', [], $locale) }}</b><br>
    {{ __('notifications.project_assigned.title_label', [], $locale) }} {{ $project->title }}<br>
    @if(isset($project->start_date))
        {{ __('notifications.project_assigned.start_date_label', [], $locale) }} {{ $project->start_date }}<br>
    @endif
    @if(isset($project->end_date))
        {{ __('notifications.project_assigned.end_date_label', [], $locale) }} {{ $project->end_date }}<br>
    @endif
</p>
<p>
    @include('api.emails.layouts.partials.button_primary', [
        'button_text' => __('notifications.project_assigned.view_button', [], $locale),
        'button_url' => config('myapp.employee_url')
    ])
</p>
@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close')
@endsection