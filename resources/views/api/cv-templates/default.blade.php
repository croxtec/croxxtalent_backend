@php
    $mail_image_url = image_to_data_url(asset('images/mail.png'));
    $phone_image_url = image_to_data_url(asset('images/phone.png'));
    $location_image_url = image_to_data_url(asset('images/location.png'));
    $star1_image_url = image_to_data_url(asset('images/star1.png'));
    $star2_image_url = image_to_data_url(asset('images/star2.png'));
    $star3_image_url = image_to_data_url(asset('images/star3.png'));
    $cv_reference_approved_image_url = image_to_data_url(asset('images/cv_reference_approved.png'));
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CV Preview</title>
    <style>
        @page {
            margin-top: 15px;
            margin-bottom: 0px;
        }
        body {
            margin: 0px;
            /* background-color: #f58a07; */
            /* background: #ededed !important; */
        }
        .container{
            width: 100%;
        }
        .left{
            float: left;
            width: 26%;
            height: 100%;
            /* height: 10000% !important; */
            padding: 20px;
            color: #000;
        }
        .right{
            height: 100%;
            padding: 10px 0px;
            background-color:#ffffff;
            padding-left: 20px;
            margin-left: 31%; /* 315px */
        }
        .profile-image{
            position: relative;
            overflow: hidden;
            margin-top: 10px;
            background-color: #009bf1 !important;
            height: 200px;
            width: 100%;
            text-align: center;
        }
        .profile-img{
            margin-top: 15px  !important;
            text-align: center;
            border: 2px solid #ece4e4;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-email{
            display: block;
            padding: 0px;
        }
        .profile-mail{
            width: 18px;
            margin: 0px;
            color: gray;
        }
        .email-text{
            font-size: 14px;
            margin: 0px;
            color: #002573;
            margin-bottom: 15px;
            opacity: 1;
        }
        li{
            margin-bottom: 10px
        }
        .justify-content {
            text-align: justify;
        }
        .cv-section {
            margin: 20px auto;
            display: block;
        }
        .row{
            display: block;
            position: relative;
            width: 100%;
            margin: 0px;
        }
        .col-8 {
           width: 65%;
           float: left;
        }
        .col-4 {
           width: 32%;
           float: right;
        }
        .cv-section-heading {
            border-bottom: 1px solid #002573;
            font-weight: 300;
            width: 90%;
        }
        .cv-section-content {
            margin: 4px 8px 4px 4px;
        }
        .star-image {
            margin-top: 3px;
            margin-bottom: 4px;
        }
        .star-image img{
            width: 80px;
        }
        div.page
        {
            page-break-after: always;
            page-break-inside: avoid;
        }
        .text-blur{
            background-color: #eee !important;
            color: transparent;
        }
        /* Preview 2 */
        .text-primary{
              color: #002573  !important;
        }
        .left .cv-pad-pos {
            padding: 2px 10px;
            background: #ededed !important;
        }
        .left .cv-pad-pos h4 {
            color: #002573;
            font-size: 18px;
            font-weight: bold;
            padding: 0px auto;
            text-align: center;
            text-transform: uppercase;
        }
        .left .cv-pad-pos p {
            color: #000;
            font-size: 12px;
            opacity: 0.9;
            font-weight: 100;
        }
        .left .about-me {
            background: #dadada;
            margin-top: 0px !important;
        }
        .left .about-me .personal-detail {
            color: #000;
            font-size: 12px;
            font-weight: 200;
        }
        .left .about-me .personal-info {
            color: #002573;
            font-weight: 400;
            font-size: 16px;
            opacity: 1;
        }
        .left .contact {
            margin-top: 6px;
            opacity: 0.7;
            background: #ededed;
        }
        .left .contact p {
            font-size: 12px;
            opacity: 0.7;
            font-weight: 100;
            color: #002573;
        }
        .left .contact p i {
            color: #000;
            opacity: 0.8;
            font-size: 20px;
        }
        .left .skills {
            margin-top: 6px;
            background: #ededed;
            color: #002573;
        }
        .left .skills li {
            color: #000;
            font-size: 12px;
            opacity: 0.7;
            font-weight: 100;
            list-style: none;
            margin-right: 15px;
        }
        .left .hobbies {
            margin-top: 6px;
        }
        .left .hobbies li {
            color: #000;
            font-size: 12px;
            opacity: 0.7;
            font-weight: 100;
            list-style: none;
            margin-right: 15px;
        }
        .left .material-icons {
            color: #fff;
        }
        .right .underline {
            border-bottom: 3px solid #002573;
            width: 80%;
            margin-top: 3px;
            padding: 2px;
            display: block;
        }
        .preview-right .job-title {
            width: 100%;
        }
        .right .career p {
            color: #000;
            font-size: 15px;
            opacity: 0.95;
            font-weight: 400;
            width: 90%;
            padding-right: 10px;
            text-align: justify;
        }
        .right .career .reference-right {
            display: block;
            float: right;
            position: relative;
            margin-left: 60px;
        }
        .progress, progress {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            height: 1rem;
            overflow: hidden;
            font-size: 0.75rem;
            background: #002573 !important;
            border-radius: 1.25rem;
        }
        .text-dark{
            color: #000;
        }
        .progress-bar {
            display: block;
            width: 80%;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            transition: width 0.6s ease;
        }
        progress::-webkit-progress-value {
            background: #002573 !important;
        }

        progress::-moz-progress-bar-value {
            background: #002573;
        }

        progress::-webkit-progress-value-value {
            background: #002573;
        }

        progress::-webkit-progress-bar-value {
            background: #002573;
        }
        .text-timeline{
           font-style: normal;
        }
        .skill-detail{
            display: block;
        }
        .skill-detail  .domain{
            display: block;
            font-size: 20px;
            padding: 4px;
            margin-left: 0px ;
        }

        .skill-detail  .core{
            font-size: 17px;
            padding: 4px;
            margin-left: 20px;
        }
       .dot, .dot-primary{
           display: inline-block;
           width: 15px;
           height: 15px;
           border-radius: 50%;
        }
      .dot{
           background: gray;
       }
       .dot-primary{
            background: #002573 !important;
       }
       .dot-lapse{
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
            background: #dadada !important;
       }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page 1 -->
        <div class="">
            <div class="left">
                <div class="profile-image">
                    <img src="{{ $cv->photo_data_url ?? '' }}" alt="Profile" class="profile-img" />
                </div>
                <div class="about-me cv-pad-pos">
                    <h4>Personal Info</h4>
                    <div class="profile-email">
                        <span class="personal-detail">Birth Date</span>  <br>
                        <span class="personal-info text-primary">{{ (new \Carbon\Carbon($cv->date_of_birth))->format("d M Y") ?? '' }} </span> <br><br>
                    </div>
                    <div class="profile-email">
                        <span class="personal-detail">Nationality</span> <br>
                        <span class="personal-info text-primary">{{$cv->country_name}}  </span> <br><br>
                    </div>
                    <div class="profile-email">
                        <span class="personal-detail">Years of Experience</span> <br>
                        <span class="text-primary">{{$cv->experience_years}} {{$cv->experience_years_suffix}}  </span> <br><br>
                    </div>
                </div>
                <div class="contact cv-pad-pos">
                    <h4>Contact Info</h4>
                    <div class="profile-email">
                        <p class="email-text">
                            <img src="{{$mail_image_url}}" alt="Email" class="profile-mail"  /> <br>
                            @if ($user)
                                <span class="{{($user->type == 'employer') ? 'text-blur' : ''}}">{{ $cv->email ?? '' }}</span>
                            @else
                                {{ $cv->email ?? '' }}
                            @endif
                        </p>
                    </div>
                    <div class="profile-email">
                        <p class="email-text">
                            <img src="{{$phone_image_url}}" alt="Phone" class="profile-mail" /> <br>
                            @if ($user)
                                <span class="{{($user->type == 'employer') ? 'text-blur' : ''}}">{{ $cv->phone ?? '' }}</span>
                            @else
                            {{ $cv->phone ?? '' }}
                            @endif
                        </p>
                    </div>
                    <div class="profile-email">
                        <p class="email-text ">
                            <img src="{{$location_image_url}}" alt="Address" class="profile-mail" /> <br>
                            @if ($user)
                                <span class="{{($user->type == 'employer') ? 'text-blur' : ''}}">
                                    {{ $cv->city . ", " . $cv->state_name . " " . $cv->country_name ?? '' }}
                                </span>
                            @else
                                {{ $cv->city . ", " . $cv->state_name . " " . $cv->country_name ?? '' }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="skills cv-pad-pos">
                    <h4>SKILLS</h4>
                    @if(is_object($cv->skills) && $cv->skills->isNotEmpty())
                        @php
                            $topSkill = $cv->skills[0];
                        @endphp
                        <div style="margin-bottom: 0px;">
                            <p class="h5 personal-detail">Domain </p>
                            <p class="text-primary" style="font-size: 17px;  font-weight: 600">{{$topSkill->skill_name}}</p>
                            <p class="h5 personal-detail">Core </p>
                            <p class="text-primary" style="font-size: 17px;  font-weight: 600">{{$topSkill->secondary->name}}</p>
                            <div class="">
                                @if($topSkill->tertiary)
                                    <span class="text-dark">{{ $topSkill->tertiary->name ?? '' }}</span> <br>
                                @endif
                                <progress class="progress-bar" style="background: #002573 !important;"  value="{{$topSkill->level_progress}}" max="100">  </progress>
                            </div> <br>
                        </div>
                    @endif
                </div>
                <div class="skills cv-pad-pos" style="padding-bottom: 10px;">
                    <h4>Languages</h4>
                    @if(is_object($cv->languages) && $cv->languages->isNotEmpty())
                        <div style="margin-bottom: 3px;">
                            @foreach ($cv->languages as $language)
                                <span class="text-dark">{{ $language->language_name ?? '' }}</span> <br>
                                <progress class="progress-bar" value="{{$language->level_progress}}" max="100">  </progress> <br>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="skills cv-pad-pos" style="padding-bottom: 20px;">
                    <h4>Hobbies</h4>
                    <div class="" style="display:block; width: 100%;">
                        @if(is_object($cv->hobbies) && $cv->hobbies->isNotEmpty())
                            <span class="" style="width: 30%; padding-right:3px;">
                                @foreach ($cv->hobbies as $hobby)
                                   {{ $hobby->name ?? '' }}
                                @endforeach
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="right">
                <div >
                    <h1 style="font-weight: 300; letter-spacing: 3px;">{{ strtoupper($cv->name) ?? '' }}
                        <div class="underline"></div>
                    </h1>
                </div>
                <!-- <div >
                    <h4 style="font-weight: 600;">{{ strtoupper($cv->job_title_name) ?? '' }}</h4>
                </div> -->
                <div>
                    <p class="cv-section" style="margin: 20px 0px;"></p>  <br>
                    <div class="cv-section career">
                        <h4 class="cv-section-heading">CAREER SUMMARY</h4>
                        <div class="cv-section-content justify-content" style="width: 90%;">
                            {{ $cv->career_summary ?? '' }}
                        </div>
                    </div>

                    <div class="cv-section career">
                        <div class="row">
                            <h4 class="cv-section-heading">WORK EXPERIENCE</h4>
                        </div>
                        <div class="cv-section-content">
                            <div class="row" >
                                @if(is_object($cv->workExperiences) && $cv->workExperiences->isNotEmpty())
                                    @foreach ($cv->workExperiences as $workExperience)
                                        <div class="col-8">
                                            <p>
                                                <strong>
                                                    {{ $workExperience->job_title_name ?? '' }} at {{ $workExperience->employer ?? '' }}, {{ $workExperience->city ?? '' }}
                                                </strong>
                                            </p>
                                            @if($workExperience->description)
                                                <p style="margin-left: 10px;" class="ml-3">
                                                    <span>{{ $workExperience->description ?? '' }}</span>
                                                </p>
                                            @endif
                                        </div>
                                        <div class="col-4">
                                            <p class="text-primary">
                                                    <i  class="dot-lapse"></i>
                                                <i class="text-timeline">
                                                    {{ (new \Carbon\Carbon($workExperience->start_date))->format("M Y") ?? '' }}
                                                    &#8212;
                                                    @if($workExperience->is_current)
                                                        <span>{{ (new \Carbon\Carbon($workExperience->end_date))->format("M Y") ?? '' }}</span>
                                                    @else
                                                        <span>Present</span>
                                                    @endif
                                                </i>
                                            </p>
                                        </div>
                                        <br><br>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <br> <br> <br>
                    <div class="cv-section career">
                        <div class="row">
                            <h4 class="cv-section-heading">EDUCATION</h4>
                        </div>
                        <div class="cv-section-content">
                            @if(is_object($cv->educations) && $cv->educations->isNotEmpty())
                                @foreach ($cv->educations as $education)
                                    <div class="row">
                                        <div class="col-8">
                                            <p>
                                                <strong>
                                                    {{ $education->degree_name ?? '' }} {{ $education->course_of_study_name ?? '' }}, {{ $education->school ?? '' }}, {{ $education->city ?? '' }}
                                                </strong>
                                            </p>
                                            @if($education->description)

                                                <p style="margin-left: 10px;" class="ml-3">
                                                    <span>{{ $education->description ?? '' }}</span>
                                                </p>
                                            @endif
                                        </div>
                                        <div class="col-4">
                                            <p class="text-primary">
                                                <i  class="dot-lapse"></i>
                                                <i class="text-timeline">
                                                    {{ (new \Carbon\Carbon($education->start_date))->format("M Y") ?? '' }}
                                                    &#8212;
                                                    @if($education->is_current)
                                                        <span>{{ (new \Carbon\Carbon($education->end_date))->format("M Y") ?? '' }}</span>
                                                    @else
                                                        <span>Present</span>
                                                    @endif
                                                </i>
                                            </p>
                                        </div>
                                        <br> <br>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <br> <br>
                    <div class="cv-section career">
                        <div class="row">
                            <h4 class="cv-section-heading">CERTIFICATIONS</h4>
                        </div>
                        <div class="cv-section-content">
                            @if(is_object($cv->certifications) && $cv->certifications->isNotEmpty())
                                @foreach ($cv->certifications as $certification)
                                <div class="row">
                                    <div class="col-8">
                                        <p>
                                            <strong>
                                                {{ $certification->certification_course_name ?? '' }} at {{ $certification->institution ?? '' }}
                                            </strong>
                                        </p>
                                        @if($certification->description)
                                            <p style="margin-left: 10px;" class="ml-3">
                                                <span>{{ $certification->description ?? '' }}</span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="col-4">
                                        <p class="text-primary">
                                            <i  class="dot-lapse"></i>
                                            <i class="text-timeline">
                                                {{ (new \Carbon\Carbon($certification->start_date))->format("M Y") ?? '' }}
                                                &#8212;
                                                @if($certification->is_current)
                                                    <span>{{ (new \Carbon\Carbon($certification->end_date))->format("M Y") ?? '' }}</span>
                                                @else
                                                    <span>Present</span>
                                                @endif
                                            </i>
                                        </p>
                                    </div>
                                    <br><br>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <!-- <br><br> -->
                    <div class="cv-section career">
                        <h4 class="cv-section-heading">HONOURS & AWARDS</h4>
                        <div class="cv-section-content">
                        @if(is_object($cv->awards) && $cv->awards->isNotEmpty())
                                @foreach ($cv->awards as $award)
                                <div class="row">
                                    <div class="col-8">
                                        <p>
                                            <strong>
                                                {{ $award->title ?? '' }}
                                            </strong>
                                            {{ $award->organization ?? '' }}
                                        </p>
                                        @if($award->description)
                                            <p style="margin-left: 10px;" class="ml-3">
                                                <span>{{ $award->description ?? '' }}</span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="col-4">
                                        <p class="text-primary">
                                            <i  class="dot-lapse"></i>
                                            {{ (new \Carbon\Carbon($award->date))->format("M Y") ?? '' }}
                                            &middot;
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <br> <br>
                    <div class="cv-section page">
                        <div class="row">
                            <h4 class="cv-section-heading">REFERENCES</h4>
                        </div>
                        <div class="cv-section-content">
                            @if(is_object($cv->references) && $cv->references->isNotEmpty())
                            @foreach ($cv->references as $index => $reference)
                                <div class="row">
                                    <div  >
                                        @if($index % 2 === 1)
                                            <p style="width: 48%;float: right;">
                                                <strong>{{ $reference->name ?? '' }}</strong>
                                                @if ($reference->is_approved)
                                                    <img src="{{ $cv_reference_approved_image_url ?? '' }}" alt="Reference Approved" style="width: 16px;" />
                                                @endif
                                                <br />
                                                {{ $reference->position ?? '' }}, {{ $reference->company ?? '' }}
                                                @if (!$user)
                                                    <br />
                                                    {{ $reference->email ?? '' }}
                                                    <br />
                                                    {{ $reference->phone ?? '' }}
                                                @endif
                                            </p>
                                        @else
                                            <p style="width: 48%; float: left;">
                                                <strong>{{ $reference->name ?? '' }}</strong>
                                                @if ($reference->is_approved)
                                                    <img src="{{ $cv_reference_approved_image_url ?? '' }}" alt="Reference Approved" style="width: 16px;" />
                                                @endif
                                                <br />
                                                {{ $reference->position ?? '' }}, {{ $reference->company ?? '' }}
                                                @if (!$user)
                                                    <br />
                                                    {{ $reference->email ?? '' }}
                                                    <br />
                                                    {{ $reference->phone ?? '' }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                    @endforeach
                                @else
                                    <p>References available upon request.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- @if (!$user)
                       <div class="cv-section career">
                           <h4 class="cv-section-heading" style="padding-top: 50px;">REFERENCES QUESTIONNAIRE</h4>
                           <div class="cv-section-content">
                               @if(is_object($cv->references) && $cv->references->isNotEmpty())
                                   @foreach ($cv->references as $reference)
                                       <p>
                                           @if (!$loop->first)
                                               <hr style="margin-top: 60px;">
                                           @endif
                                           <div style="text-align: center">
                                               <strong>QUESTIONNAIRE FOR {{ strtoupper($reference->name) ?? '' }}</strong>
                                           </div>
                                           <br />
                                           <strong>Name:</strong> {{ $reference->name ?? '' }}
                                           <br />
                                           <strong>Position:</strong> {{ $reference->position ?? '' }}
                                           <br />
                                           <strong>Company:</strong> {{ $reference->company ?? '' }}
                                           @if (!$user)
                                               <br />
                                               <strong>Email:</strong> {{ $reference->email ?? '' }}
                                               <br />
                                               <strong>Phone:</strong> {{ $reference->phone ?? '' }}
                                           @endif
                                           <br /><br />
                                           <strong>FEEDBACK:</strong><br />
                                           @if ($reference->is_approved)
                                               <i class="text-timeline">Approved </i><br />
                                               @if(is_array($reference->feedback) && $cv->references->isNotEmpty())
                                                   @foreach ($reference->feedback as $feedback)
                                                       <p>
                                                           <strong>{{ $loop->iteration ?? '' }}. {{ $feedback['question'] ?? '' }}</strong>
                                                           <br>
                                                           Answer: {{ $feedback['answer'] ?? '' }}
                                                       </p>
                                                   @endforeach
                                               @endif
                                           @else
                                               <i class="text-timeline">Pending.</i>
                                           @endif
                                       </p>
                                   @endforeach
                               @else
                                   <p>References available upon request.</p>
                               @endif
                           </div>
                       </div>
                     @endif -->
                </div>
            </div>
        </div>
        <!-- Page 2 -->
        <div class="">
            <div class="left">
                    <div class="profile-image">
                        <img src="{{ $cv->photo_data_url ?? '' }}" alt="Profile" class="profile-img" />
                    </div>
                    <div class="about-me cv-pad-pos">
                        <h4>Personal Info</h4>
                        <div class="profile-email">
                            <span class="personal-detail">Birth Date</span>  <br>
                            <span class="personal-info text-primary">{{ (new \Carbon\Carbon($cv->date_of_birth))->format("d M Y") ?? '' }} </span> <br><br>
                        </div>
                        <div class="profile-email">
                            <span class="personal-detail">Nationality</span> <br>
                            <span class="personal-info text-primary">{{$cv->country_name}}  </span> <br><br>
                        </div>
                        <div class="profile-email">
                            <span class="personal-detail">Years of Experience</span> <br>
                            <span class="text-primary">{{$cv->experience_years}} {{$cv->experience_years_suffix}}  </span> <br><br>
                        </div>
                    </div>
                    <div class="contact cv-pad-pos">
                        <h4>Contact Info</h4>
                        <div class="profile-email">
                            <p class="email-text">
                                <img src="{{$mail_image_url}}" alt="Email" class="profile-mail"  /> <br>
                                @if ($user)
                                    <span class="{{($user->type == 'employer') ? 'text-blur' : ''}}">{{ $cv->email ?? '' }}</span>
                                @else
                                    {{ $cv->email ?? '' }}
                                @endif
                            </p>
                        </div>
                        <div class="profile-email">
                            <p class="email-text">
                                <img src="{{$phone_image_url}}" alt="Phone" class="profile-mail" /> <br>
                                @if ($user)
                                    <span class="{{($user->type == 'employer') ? 'text-blur' : ''}}">{{ $cv->phone ?? '' }}</span>
                                @else
                                {{ $cv->phone ?? '' }}
                                @endif
                            </p>
                        </div>
                        <div class="profile-email">
                            <p class="email-text ">
                                <img src="{{$location_image_url}}" alt="Address" class="profile-mail" /> <br>
                                @if ($user)
                                    <span class="{{($user->type == 'employer') ? 'text-blur' : ''}}">
                                        {{ $cv->city . ", " . $cv->state_name . " " . $cv->country_name ?? '' }}
                                    </span>
                                @else
                                    {{ $cv->city . ", " . $cv->state_name . " " . $cv->country_name ?? '' }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="skills cv-pad-pos">
                        <h4>SKILLS</h4>
                        @if(is_object($cv->skills) && $cv->skills->isNotEmpty())
                            @php
                                $topSkill = $cv->skills[0];
                            @endphp
                            <div style="margin-bottom: 0px;">
                                <p class="h5 personal-detail">Domain </p>
                                <p class="text-primary" style="font-size: 17px;  font-weight: 600">{{$topSkill->skill_name}}</p>
                                <p class="h5 personal-detail">Core </p>
                                <p class="text-primary" style="font-size: 17px;  font-weight: 600">{{$topSkill->secondary->name}}</p>
                                <div class="">
                                    @if($topSkill->tertiary)
                                        <span class="text-dark">{{ $topSkill->tertiary->name ?? '' }}</span> <br>
                                    @endif
                                    <progress class="progress-bar" style="background: #002573 !important;"  value="{{$topSkill->level_progress}}" max="100">  </progress>
                                </div> <br>
                            </div>
                        @endif
                    </div>
                    <div class="skills cv-pad-pos" style="padding-bottom: 10px;">
                        <h4>Languages</h4>
                        @if(is_object($cv->languages) && $cv->languages->isNotEmpty())
                            <div style="margin-bottom: 3px;">
                                @foreach ($cv->languages as $language)
                                    <span class="text-dark">{{ $language->language_name ?? '' }}</span> <br>
                                    <progress class="progress-bar" value="{{$language->level_progress}}" max="100">  </progress> <br>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="skills cv-pad-pos" style="padding-bottom: 20px;">
                        <h4>Hobbies</h4>
                        <div class="" style="display:block; width: 100%;">
                            @if(is_object($cv->hobbies) && $cv->hobbies->isNotEmpty())
                                <span class="" style="width: 30%; padding-right:3px;">
                                    @foreach ($cv->hobbies as $hobby)
                                    {{ $hobby->name ?? '' }}
                                    @endforeach
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="right">
                <div >
                    <h1 style="font-weight: 300; letter-spacing: 3px;">{{ strtoupper($cv->name) ?? '' }}
                        <div class="underline"></div>
                    </h1>
                </div>
                <div>
                    <p class="cv-section" style="margin: 10px 0px;"></p>  <br>
                    <div class="cv-section career">
                        <h4 class="cv-section-heading">DETAILED SKILL SUMMARY</h4>
                        <div class="cv-section-content justify-content" style="width: 90%;">
                            <ul class="skill-detail">
                                @foreach($cv->skills as $skill)
                                    <li class="row" >
                                        <div class="domain">
                                            {{$skill->skill_name}} - {{$skill->secondary->name}}
                                        </div>
                                        <div class="core" >
                                            <span>{{$skill->tertiary->name}}  </span>
                                            <div>
                                                @for ($i = 0; $i < 3; $i++)
                                                    <i class="dot-primary"></i>
                                                @endfor
                                                @for ($i = 0; $i < 3; $i++)
                                                    @if($skill->level == 'intermediate' || $skill->level == 'advanced')
                                                        <i class="dot-primary"></i>
                                                    @else
                                                        <i class="dot"></i>
                                                    @endif
                                                @endfor
                                                @for ($i = 0; $i < 3; $i++)
                                                    @if($skill->level == 'advanced')
                                                        <i class="dot-primary"></i>
                                                    @else
                                                        <i class="dot"></i>
                                                    @endif
                                                @endfor
                                            </div> <br>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
