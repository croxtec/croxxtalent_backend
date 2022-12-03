@extends('api.emails.layouts.master_plain')

@section('email_body')
Dear {{ $cvReference->name }},

I hope you are well. I would appreciate your assistance with my job search. 
I am currently seeking employment as a {{ $cvReference->cv->job_title_name }} through {{ config('myapp.name') }} ({{ config('myapp.url') }})
and am hoping that you will provide a reference for me.

With your permission,  I would like to list you as one of my references who can provide potential employers 
with information about my skills and qualifications that will improve my chances of getting the job.

Simply click or copy the link below to Approve my Reference Request: 
{{ $form_url }} 


Click or copy the following link to view a copy of my current CV: 
{{ $cv_url }}

Please let me know if you need any additional information to act as a reference on my behalf. 

Thank you very much for taking the time to consider my request.
@endsection

@section('email_complimentary_close')


Best regards,
{{ $cvReference->cv->name }}
{{ $cvReference->cv->phone }}
{{ $cvReference->cv->email }}


@endsection
