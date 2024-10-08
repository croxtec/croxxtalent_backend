@extends('api.emails.layouts.master_plain')

@section('email_body')
Hi {{ $name }},
@if($is_talent)
<p>
    We are excited to invite you to officially join the {{ $company_name }} team on our platform.
    Your profile has been linked to the company's employee management system, where you'll be able to access important company resources and collaborate with your team.
</p>
<p>
    Simply click the button below to verify your email address and complete your onboarding process.
</p>
@else
<p>
    You’ve been invited to join {{ $company_name }} on our platform! By joining, you will gain access to company tools, resources, and be a part of their employee management system.
</p>
<p>
    Please click the button below to verify your email address and get started as a member of {{ $company_name }}.
</p>
@endif

@endsection

@section('email_complimentary_close')
@include('api.emails.layouts.partials.complimentary_close_plain')
@endsection
