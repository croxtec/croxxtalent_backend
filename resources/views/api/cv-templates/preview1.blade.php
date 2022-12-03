@php
    $mail_image_url = image_to_data_url(asset('images/mail2.png'));
    $phone_image_url = image_to_data_url(asset('images/phone2.png'));
    $location_image_url = image_to_data_url(asset('images/location2.png'));
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
            background-color: #0040a1;
        }
        .container{
            width: 100%;
        }
        .left{
            float: left;
            width: 26%;
            height: 10000% !important;
            background-color:#0040a1;
            padding: 20px;
            color: #ffffff;
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
            width: 150px;
            height: 150px;
            overflow: hidden;
            border-radius: 50%;
            margin: 30px auto;
            border: 2px solid #ece4e4
        }
        .profile-img{
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-email{
            padding: 0px
        }
        .profile-mail{
            width: 18px;
            margin: 0px;
        }
        .email-text{
            font-size: 12px;
            margin: 0px;
            margin-bottom: 30px;
        }
        .pills {
            background-color: #28c76f;
            color: #1a1a1a;
            font-size: 12px;
            min-width: 20px;
            padding: 8px 10px;
            border-radius: 50px;
        }
        li{
            margin-bottom: 10px
        }
        .justify-content {
            text-align: justify;
        }
        .cv-section {
            margin-bottom: 40px;
        }
        .cv-section-heading {
            border-bottom: 2px solid #000000; 
            font-weight: 300;
        }
        .cv-section-content {
            margin: 4px 20px 4px 4px;            
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
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <div class="profile-image"><img src="{{ $cv->photo_data_url }}" alt="Profile" class="profile-img" /></div>
            <div class="profile-email">
                <p class="email-text">
                    {{-- <img src="{{ $mail_image_url }}" alt="Email" class="profile-mail" /> <br>  --}}
                    {{ $cv->email }}
                </p> 
            </div>
            <div class="profile-email">
                <p class="email-text">
                    {{-- <img src="{{ $phone_image_url }}" alt="Phone" class="profile-mail" /> <br> --}}
                    {{ $cv->phone }}
                </p>
            </div>
            <div class="profile-email">
                <p class="email-text">
                    {{-- <img src="{{ $location_image_url }}" alt="Address" class="profile-mail" /> <br> --}}
                    {{ $cv->address . ", " . $cv->city . ", " . $cv->state_name . " " . $cv->postal_code . ", " . $cv->country_name }}
                </p>
            </div>
            
            @if(is_object($cv->skills) && $cv->skills->isNotEmpty())
                <div style="margin-bottom: 30px;">
                    <div style="border-bottom: 2px solid white; width: 70px;">SKILLS</div>
                    <br>
                    @foreach ($cv->skills as $skill)
                        @if($loop->iteration <= 10)
                            <span class="pills">{{ $skill->skill_name }}</span>
                            <br>
                            <div class="star-image">
                                @if ($skill->level == 'advanced')
                                    {{-- <img src="{{ $star3_image_url }}" alt="3 Stars" /> --}}
                                @elseif($skill->level == 'intermediate')
                                    {{-- <img src="{{ $star2_image_url }}" alt="2 Stars" /> --}}
                                @else
                                    {{-- <img src="{{ $star1_image_url }}" alt="1 Star" /> --}}
                                @endif                            
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
                        
            @if(is_object($cv->hobbies) && $cv->hobbies->isNotEmpty())
                <div style="margin-bottom: 30px;">
                    <div style="border-bottom: 2px solid white; width: 90px;">HOBBIES</div>
                    <br>
                    @foreach ($cv->hobbies as $hobby)
                        <span class="pills">{{ $hobby->name }}</span> <br><br>
                    @endforeach
                </div>
            @endif
            
            @if(is_object($cv->languages) && $cv->languages->isNotEmpty())
                <div style="margin-bottom: 30px;">
                    <div style="border-bottom: 2px solid white; width: 100px;">LANGUAGES</div>
                    <br>
                    @foreach ($cv->languages as $language)
                        <span class="pills">{{ $language->language_name }}</span>
                        <br>
                        <div class="star-image">
                            @if ($language->level == 'advanced')
                                {{-- <img src="{{ $star3_image_url }}" alt="3 Stars" /> --}}
                            @elseif($language->level == 'intermediate')
                                {{-- <img src="{{ $star2_image_url }}" alt="2 Stars" /> --}}
                            @else
                                {{-- <img src="{{ $star1_image_url }}" alt="1 Star" /> --}}
                            @endif                            
                        </div>
                    @endforeach
                </div>
            @endif            
        </div>

        <div class="right">
            <div style="">
                <h1 style="font-weight: 300; letter-spacing: 3px;">{{ strtoupper($cv->name) }}</h1>
            </div>
            <div style="">
                <h4 style="font-weight: 600;">{{ strtoupper($cv->job_title_name) }}</h4>
            </div>
            <div>
                <div class="cv-section">
                    <h4 class="cv-section-heading">CAREER SUMMARY</h4> 
                    <div class="cv-section-content justify-content">
                        {{ $cv->career_summary }}
                    </div>
                </div>

                <div class="cv-section">
                    <h4 class="cv-section-heading">WORK EXPERIENCE</h4> 
                    <div class="cv-section-content">
                        @if(is_object($cv->workExperiences) && $cv->workExperiences->isNotEmpty())
                            @foreach ($cv->workExperiences as $workExperience)
                                <p>
                                    <strong>
                                        {{ $workExperience->job_title_name }} at {{ $workExperience->employer }}, {{ $workExperience->city }}
                                    </strong>
                                    <br />
                                    <i>
                                        {{ (new \Carbon\Carbon($workExperience->start_date))->format("M Y") }}
                                        &#8212;
                                        @if($workExperience->is_current)
                                            <span>{{ (new \Carbon\Carbon($workExperience->end_date))->format("M Y") }}</span>
                                        @else
                                            <span>Present</span>
                                        @endif
                                    </i>
                                    @if($workExperience->description)
                                        <br>
                                        <small>{{ $workExperience->description }}</small>
                                    @endif
                                </p>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="cv-section">
                    <h4 class="cv-section-heading">EDUCATION</h4>
                    <div class="cv-section-content">
                        @if(is_object($cv->educations) && $cv->educations->isNotEmpty())
                            @foreach ($cv->educations as $education)
                                <p>
                                    <strong>
                                        {{ $education->degree_name }} {{ $education->course_of_study_name }}, {{ $education->school }}, {{ $education->city }}
                                    </strong>
                                    <br />
                                    <i>
                                        {{ (new \Carbon\Carbon($education->start_date))->format("M Y") }}
                                        &#8212;
                                        @if($education->is_current)
                                            <span>{{ (new \Carbon\Carbon($education->end_date))->format("M Y") }}</span>
                                        @else
                                            <span>Present</span>
                                        @endif
                                    </i>
                                    @if($education->description)
                                        <br>
                                        <small>{{ $education->description }}</small>
                                    @endif
                                </p>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="cv-section">
                    <h4 class="cv-section-heading">CERTIFICATIONS</h4> 
                    <div class="cv-section-content">
                        @if(is_object($cv->certifications) && $cv->certifications->isNotEmpty())
                            @foreach ($cv->certifications as $certification)
                                <p>
                                    <strong>
                                        {{ $certification->certification_course_name }} at {{ $certification->institution }}
                                    </strong>
                                    <br />
                                    <i>
                                        {{ (new \Carbon\Carbon($certification->start_date))->format("M Y") }}
                                        &#8212;
                                        @if($certification->is_current)
                                            <span>{{ (new \Carbon\Carbon($certification->end_date))->format("M Y") }}</span>
                                        @else
                                            <span>Present</span>
                                        @endif
                                    </i>
                                    @if($certification->description)
                                        <br>
                                        <small>{{ $certification->description }}</small>
                                    @endif
                                </p>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="cv-section">
                    <h4 class="cv-section-heading">HONOURS & AWARDS</h4> 
                    <div class="cv-section-content">
                    @if(is_object($cv->awards) && $cv->awards->isNotEmpty())
                            @foreach ($cv->awards as $award)
                                <p>
                                    <strong>
                                        {{ $award->title }}
                                    </strong>
                                    <br />
                                    {{ (new \Carbon\Carbon($award->date))->format("M Y") }}
                                    &middot;
                                    {{ $award->organization }}
                                    @if($award->description)
                                        <br>
                                        <small>{{ $award->description }}</small>
                                    @endif
                                </p>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="cv-section">
                    <h4 class="cv-section-heading">SKILLS</h4>
                    <div class="cv-section-content">                  
                        @if(is_object($cv->skills) && $cv->skills->isNotEmpty())
                            <ul>
                                @foreach ($cv->skills as $skill)
                                    <li>{{ $skill->skill_name }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="cv-section page">
                    <h4 class="cv-section-heading">REFERENCES</h4>
                    <div class="cv-section-content">                  
                        @if(is_object($cv->references) && $cv->references->isNotEmpty())
                            @foreach ($cv->references as $reference)
                                <p>
                                    <strong>{{ $reference->name }}</strong> 
                                    @if ($reference->is_approved)
                                        {{-- <img src="{{ $cv_reference_approved_image_url }}" alt="Reference Approved" style="width: 16px;" /> --}}
                                    @endif
                                    <br />
                                    {{ $reference->position }}, {{ $reference->company }}
                                    <br />
                                    {{ $reference->email }}
                                    <br />
                                    {{ $reference->phone }}
                                </p>
                            @endforeach
                        @else 
                            <p>References available upon request.</p>
                        @endif
                    </div>
                </div>

                <div class="cv-section">
                    <h4 class="cv-section-heading" style="padding-top: 50px;">REFERENCES QUESTIONNAIRE</h4>
                    <div class="cv-section-content">                  
                        @if(is_object($cv->references) && $cv->references->isNotEmpty())
                            @foreach ($cv->references as $reference)
                                <p>
                                    @if (!$loop->first)
                                        <hr style="margin-top: 60px;">
                                    @endif                                    
                                    <div style="text-align: center">
                                        <strong>QUESTIONNAIRE FOR {{ strtoupper($reference->name) }}</strong> 
                                    </div>
                                    <br />
                                    <strong>Name:</strong> {{ $reference->name }}
                                    <br />
                                    <strong>Position:</strong> {{ $reference->position }}
                                    <br />
                                    <strong>Company:</strong> {{ $reference->company }}
                                    <br />
                                    <strong>Email:</strong> {{ $reference->email }}
                                    <br />
                                    <strong>Phone:</strong> {{ $reference->phone }}
                                    <br /><br />
                                    <strong>FEEDBACK:</strong><br />                                    
                                    @if ($reference->is_approved)
                                        <i>Submitted on {{ (new Carbon\Carbon($reference->approved_at))->toDayDateTimeString() }} </i><br />
                                        @if(is_array($reference->feedback) && $cv->references->isNotEmpty())
                                            @foreach ($reference->feedback as $feedback)
                                                <p>
                                                    <strong>{{ $loop->iteration }}. {{ $feedback['question'] }}</strong>
                                                    <br>
                                                    Answer: {{ $feedback['answer'] }}
                                                </p>
                                            @endforeach
                                        @endif
                                    @else
                                        <i>Reference request not yet approved by {{ $reference->name }}.</i>
                                    @endif                                    
                                </p>
                            @endforeach
                        @else 
                            <p>References available upon request.</p>
                        @endif
                    </div>
                </div>
                 
            </div>
        </div>
    </div> 
</body>
</html>