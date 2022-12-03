@extends('api.emails.layouts.master')

@section('email_page_title')
    Notification - {{ config('myapp.name') }}
@endsection

@section('email_body_title')
@endsection

@section('email_body')
    <div>
        <h5 style="text-align:center;">Croxx Talent Contact</h5>
        <p> 
            <span style="font-size: 18px;">Fullname:</span>
            <span>{{$feedback->fullname}} </span>
         </p> 
        <p> 
            <span style="font-size: 18px;">Email:</span>
            <span>{{$feedback->email}} </span>
         </p> 
        <p> 
            <span style="font-size: 18px;">Phone Number:</span>
            <span>{{$feedback->phone}} </span>
         </p>
        <p> 
            <span style="font-size: 18px;">Subject:</span>
            <span>{{$feedback->subject}} </span>
         </p> 
        <p> 
            <span style="font-size: 18px;">Message:</span> <br>
            <span>{{$feedback->message}} </span>
         </p>
    </div>
@endsection

@section('email_complimentary_close')
    @include('api.emails.layouts.partials.complimentary_close')
@endsection

