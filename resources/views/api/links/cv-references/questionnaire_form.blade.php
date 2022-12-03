<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reference Questionaire Form - Croxx Talent</title>
    <link rel="shortcut icon" href="{{ mix('images/logoicon.png') }}" type="image/x-icon">
    
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@500&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Changa&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <style>
        body{
            font-family: 'Nunito Sans', sans-serif;
        }
        #header {
            margin-top: 10vh;
        }
    </style>
</head>
<body>

    <div class="container mb-5">
        <section id="header">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <a href="{{ app_url() }}">
                        <img src="{{ mix('images/logo.png') }}" alt="Croxx Talent" width="200">
                    </a>
                </div>
            </div>
        </section>
        <section id="content">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <hr />
                    @if(!$cvReference)
                        <div class="alert alert-danger">
                            <h5>The reference approval link is invalid, maybe the link has expired or used.</h5>
                        </div>
                    @else
                        <h6  style="color:red;">
                            CAUTION: IT IS UNETHICAL TO PROVIDE REFERENCE FOR SOMEONE YOU HAVEN'T WORKED WITH IN THE PAST.
                        </h6>
                        <br>
                        <p>
                            Dear {{ $cvReference->name }},
                        </p>
                        <p>
                            {{ $cvReference->cv->name }} has listed you as one the references on his\her CV. 
                            If you have previous work history with {{ $cvReference->cv->name }} or someone well known to you,
                            kindly complete the Questionaire Form below to grant your permission to be listed as reference.
                        </p>
                        <p>
                            Please answer these questions to the best of your ability, as this information allows us to 
                            make an informed hiring decision.
                        </p>
                        <hr>
                        <form method="post" action="{{ $form_action_url }}" onsubmit="return confirm('Submit Questionaire Form');">
                            @csrf
                            @if(is_object($referenceQuestions) && $referenceQuestions->isNotEmpty())
                                @foreach ($referenceQuestions as $referenceQuestion)                                    
                                    <div class="form-group">
                                        <label for="feedback-{{ $loop->index }}" style="font-weight: bold">
                                            {{ $loop->iteration }}. {{ $referenceQuestion->question }}
                                        </label>
                                        @if ($referenceQuestion->description)
                                            <label for="feedback-{{ $loop->index }}">
                                                {{ $referenceQuestion->description }}
                                            </label>
                                        @endif     
                                        <input
                                            type="hidden"
                                            name="feedback[{{ $loop->index }}][question]"
                                            value="{{ $referenceQuestion->question }}"
                                        >
                                        @if ($referenceQuestion->is_predefined_options && is_object($referenceQuestion->options) && $referenceQuestion->options->isNotEmpty())
                                            @foreach ($referenceQuestion->options as $referenceQuestionOption)
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input"
                                                        type="radio" 
                                                        name="feedback[{{ $loop->parent->index }}][answer]" 
                                                        id="feedback-{{ $loop->parent->index }}-option-{{ $loop->index }}" 
                                                        value="{{ $referenceQuestionOption->name }}"
                                                        required
                                                    >
                                                    <label class="form-check-label" for="feedback-{{ $loop->parent->index }}-option-{{ $loop->index }}">
                                                        {{ $referenceQuestionOption->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            @if ($referenceQuestion->use_textarea)
                                                <textarea
                                                    class="form-control" 
                                                    name="feedback[{{ $loop->index }}][answer]" 
                                                    id="feedback-{{ $loop->index }}"
                                                    rows="3"
                                                    required
                                                ></textarea>
                                            @else
                                                <input 
                                                    type="text"
                                                    class="form-control" 
                                                    name="feedback[{{ $loop->index }}][answer]"
                                                    id="feedback-{{ $loop->index }}"
                                                    aria-describedby="emailHelp"
                                                    value=""
                                                    required
                                                >
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                            <div class="text-center mt-5 mb-5">
                                <button type="submit" class="btn btn-primary btn-lg btn-block">Submit Questionaire Form</button>
                            </div>
                        </form>
                    @endif
                    
                </div>
            </div>
        </section>
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>