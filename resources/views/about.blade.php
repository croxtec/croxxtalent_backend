<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Croxx Talent</title>
    @include('inc.head')
</head>
<body>
    <section class="about-banner">
        @include('inc.navigation')
        <div class="container">
            <div class="row  slide">
                    <div class="slide-content-left">
                        <h1 class="text-center">About Us</h1>
                    </div>
            </div>
        </div>
    </section>
    
    <div class="container p-5 about-content"> 
        <h2 class="mb-4 text-center"> We’re on a mission </h2>
        <div class="row">
            <div class="col-md-6 info-img  order-sm-12 pl-5 mb-3">
                    <img src="{{ mix('images/about.png') }}" alt="About Us" width="400">
            </div>
            <div class="col-md-6  order-sm-1 about-conten-paragraph">
                <div class="text-muted">
                    <p>CroxxTalent is pioneering a better way of matching talent in the energy industry. We understand the dynamics around energy and competencies to drive innovation.
                    </p>
                    <p>Our mission is to create a best-in-class recruitment and onboarding experience aligned with the demands and work culture for the benefit of all.</p>
                    <p>We are enabling the local, regional, and global work network with the underlying pillar of integrity and customer satisfaction.
                    </p>
                    <a href="{{ route('register') }}"> <button class="btn button-inverse btn-lg mt-4 mb-3">Get Started</button></a> <br>
                    <p class="text-muted">Already a member?<a href="{{ route('login') }}"> Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
    <div class="container description">
        <h1 class="text-center text-muted mt-4 bold">How it works</h1>
        <div class="row card-container">
                <div class="col-md content-card" style="margin: 0px;">
                    <img src="{{ mix('images/service.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                    <h3 class="mt-4 mb-4">Registration</h3>
                    <p class="text-muted mb-4">  
                    Create an account in less than 1 minute. Join CroxxTalent and find opportunities. 
                    </p>
                    <a class="btn button-direct btn-md" href="{{ route('register') }}">Register Now</a>
                </div>
                <div class="col-md content-card">
                    <img src="{{ mix('images/settings.jpg') }}" class="mt-5" width="80" alt="Acountability Icon">
                    <h3 class="mt-4 mb-4">Setup Profile</h3>
                    <p class="text-muted mb-4">
                        Quickly setup your profile and settings.
                    </p>
                    <a class="btn button-direct btn-md" href="{{ route('register') }}">Register Now</a>
                </div>
                <div class="col-md content-card">
                    <img src="{{ mix('images/layers.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                    <h3 class="mt-4 mb-4">Build CV</h3>
                    <p class="text-muted mb-2">
                        Build your CV using our live web cv builder. Build a beautiful cv and showcase relevant skills for employers to see.  
                    </p>
                    <a class="btn button-direct btn-md" href="{{ route('register') }}">Register Now</a>
                </div>
                <div class="col-md content-card">
                    <img src="{{ mix('images/appointment.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                    <h3 class="mt-4 mb-4">Apply for Jobs</h3>
                    <p class="text-muted mb-4">
                        Check available jobs. Start sending your CV to 100’s of companies. Apply for multiple roles.
                    </p>
                    <a class="btn button-direct btn-md" href="{{ route('register') }}">Register Now</a>
                </div>
        </div>
    </div>
    <section class="info-section">
        <div class="container">
            <div class="row info-sub-section">
                <div class="info-img col-sm-6">
                    <img src="{{ mix('images/about3.png') }}" alt="employee_search"  width="300">
                </div>
                <div class="col-sm-6 info-content">
                    <h3>Looking For Talent?</h3>
                    <p class="text-muted">
                        CroxxTalent boasts of the best skilled workers. Sign up as an employers and go through our robust talent database. 
                        <br><a href="{{ route('register') }}"> <button class="btn btn-green button-inverse btn-lg my-3">Sign Up</button></a> 
                    </p>
                </div>
            </div>
            <div class="row pt-5 info-sub-section">
                <div class="info-img col-md-6 order-sm-12">
                    <img src="{{ mix('images/about2.png') }}" alt="employer_search" width="350">
                </div>
                <div class="col-md-6  order-sm-1 info-content">
                    <h3>Get hired!</h3>
                    <p class="text-muted">
                        Looking to get hired by top engineering companies? Look no further. Create your CV and start applying for jobs. 
                        <br><a href="{{ route('register') }}"> <button class="btn button-inverse btn-lg my-3">Create CV</button></a> 
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- <section class="video-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <iframe width="100%" height="570" src="https://www.youtube.com/embed/U0gVbw2Ynwo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            </div>
        </div>
    </section> -->
    @include('inc.subscribe')
    @include('inc.footer')
</body>
</html>