@extends('api.emails.layouts.master')

@section('email_page_title')
{{ __('notifications.goal_reminder.page_title', [], $locale) }} - {{ config('app.name') }}
@endsection

@section('email_body')
<p>
    {{ __('notifications.goal_reminder.greeting', ['name' => $name], $locale) }}
    <br><br>
    {{ __('notifications.goal_reminder.intro', ['goal_title' => $goal->title], $locale) }}
    <br><br>
    {{ __('notifications.goal_reminder.purpose', [], $locale) }}
</p>
<p>
    <strong>{{ __('notifications.goal_reminder.metric_label', [], $locale) }}</strong> {{ $goal->metric }}
</p>
<p>
    {{ __('notifications.goal_reminder.progress_message', [], $locale) }}
</p>
<p>
    {{ __('notifications.goal_reminder.support_message', [], $locale) }}
</p>
<p>
    @include('api.emails.layouts.partials.button_primary', [
        'button_text' => __('notifications.goal_reminder.view_button', [], $locale),
        'button_url' => url("")
    ])
</p>
@endsection