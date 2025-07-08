@extends('api.emails.layouts.master')

@section('email_page_title')
    {{ __('notifications.email_templates.invitation_title', ['company_name' => $company_name, 'app_name' => config('myapp.name')]) }}
@endsection

@section('email_body_title')
    {{ __('notifications.email_templates.join_team_title', ['company_name' => $company_name]) }}
@endsection

@section('email_body')
    {{ __('notifications.email_templates.greeting', ['name' => $name]) }}
    <br><br>
    @if($is_talent)
        <p>
            {{ __('notifications.email_templates.talent_invitation_message', ['company_name' => $company_name]) }}
        </p>
        <p>
            {{ __('notifications.email_templates.talent_verify_instruction') }}
        </p>
    @else
        <p>
            {{ __('notifications.email_templates.employee_invitation_message', ['company_name' => $company_name]) }}
        </p>
        <p>
            {{ __('notifications.email_templates.employee_verify_instruction', ['company_name' => $company_name]) }}
        </p>
    @endif

    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => __('notifications.email_templates.verify_email_button'),
            'button_url' =>  $verification_url
        ])
    </p>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection