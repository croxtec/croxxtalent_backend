@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    Dear {{ $cvReference->name }},
    <br>
    <p>
        I hope you are well. I would appreciate your assistance with my job search. <br>
        I am currently seeking employment as a {{ $cvReference->cv->job_title_name }} through 
        <a href="{{ config('myapp.url') }}" target="_blank">{{ config('myapp.name') }}</a>
        and am hoping that you will provide a reference for me.
    </p>
    <p>
        With your permission,  I would like to list you as one of my references who can provide potential employers 
        with information about my skills and qualifications that will improve my chances of getting the job.        
    </p>
    <p>
        Simply click the buttons below to Approve my Reference Request or view a copy of my current CV.
    </p>
    <p>
        @include('api.emails.layouts.partials.button_success', [
            'button_text' => 'Click here to Approve my Reference Request',
            'button_url' => $form_url
        ])
    </p>
    <p>
        @include('api.emails.layouts.partials.button_primary', [
            'button_text' => 'Click here to view my current CV',
            'button_url' => $cv_url
        ])
    </p>
    <p>
        Please let me know if you need any additional information to act as a reference on my behalf.
    </p>
    <p>
        Thank you very much for taking the time to consider my request.
    </p>
@endsection

@section('email_complimentary_close')
    Best regards,<br>
    {{ $cvReference->cv->name }}<br>
    {{ $cvReference->cv->phone }}<br>
    {{ $cvReference->cv->email }}
@endsection
