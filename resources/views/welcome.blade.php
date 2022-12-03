<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Croxx Talent</title>
    @include('inc.head')
</head>
<body>
    <section class="home-banner">
        @include('inc.navigation')
        <div class="container">
            <div class="row carousel slide" id="myCarousel" data-ride="carousel">
                        <!-- Indicators -->
                <ul class="carousel-indicators">
                    <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                    <li data-target="#myCarousel" data-slide-to="1"></li>
                    <li data-target="#myCarousel" data-slide-to="2"></li>
                </ul>
                <!-- <ul class="carousel-indicators">
                    <li data-target="#demo" data-slide-to="0" class="active"></li>
                    <li data-target="#demo" data-slide-to="1"></li>
                    <li data-target="#demo" data-slide-to="2"></li>
                </ul> -->
                <!-- The slideshow --> 
                <div class="carousel-inner">
                    <div class="carousel-item active"> 
                        <img src="{{ mix('images/test.jpg') }}" alt="Image1" class="imgslide1" width="100%" height="100%">
                        <div class="slide-content-left">
                            <h1 class="text-pry bold">Join Africa’s leading Energy marketplace...</h1>
                            <p>
                                Join Africa’s leading Energy marketplace
                                Find great talent. Contract hire.
                                Engineers taking their career to next level. 
                            </p>
                             <a href="{{ route('register') }}"> <button class="btn btn-lg btn-green block mt-4">Find Work</button></a>
                             <a href="{{ route('register') }}"> <button class="btn btn-lg btn-blue block mt-4">Search Talent</button></a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="{{ mix('images/test2.jpg') }}" alt="Image2" class="imgslide2" width="100%" height="100%">
                        <div class="slide-content-right">
                            <h6>For talents</h6>
                            <h1 class="text-pry title">Find jobs faster</h1>
                            <p>
                                Fill in your relevant info and skills and apply for the highest paying contract jobs.
                                Find opportunities, get hired and paid easily.
                            </p>
                             <!-- <a href="{{ route('register') }}"> <button class="btn btn-lg btn-green block mt-4">Join CroxxTalent </button></a> -->
                             <a href="{{ route('register') }}"> <button class="btn btn-lg btn-blue block mt-4">Join CroxxTalent</button></a>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="{{ mix('images/test3.jpg') }}" alt="Image33" class="imgslide3" width="100%" height="100%">
                        <div class="slide-content-left"> 
                            <h6>For companies</h6>
                            <h1 class="text-pry title">Find skilled talents</h1>
                            <p> 
                                Find skilled talents for contract hire.
                                No obligations. Just skilled talents who come in and do the job for you.
                            </p>
                             <!-- <a href="{{ route('register') }}"> <button class="btn btn-lg btn-green block mt-4 sm-mt-3">Find Work</button></a> -->
                             <a href="{{ route('register') }}"> <button class="btn btn-lg btn-blue block mt-4 sm-mt-3">Get Started</button></a>
                        </div>
                    </div>
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
    <!-- <section class="p-0 portfolio-section">
        <div class="container">
            <div class="row card-container">
                <div class="col-md image-card mr-3 mt-2 mb-4">
                    <img src="{{ mix('images/engineers.jpg') }}" alt="employer_search" width="100%">
                    <div class="image-card-caption">
                        <h4>Create Campaigns</h4>
                        <p>Post a Campaign and hire a pro</p>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('register') }}"> <button class="btn button-inverse btn-sm my-2">Create Campaign</button></a> <br>
                    </div>
                </div>
                <div class="col-md image-card mr-3 mt-2 mb-4">
                    <img src="{{ mix('images/engineers2.jpg') }}" alt="employer_search" width="100%">
                    <div class="image-card-caption">
                        <h4>View skilled Talents</h4>
                        <p>Browse skilled talents </p>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('register') }}"> <button class="btn button-inverse btn-sm my-2">View Talent</button></a> <br>
                    </div>
                </div>
                <div class="col-md image-card mr-3 mt-2 mb-4">
                    <img src="{{ mix('images/engineers3.jpg') }}" alt="employer_search" width="100%">
                    <div class="image-card-caption">
                        <h4>Services</h4>
                        <p>Let us help you find the best talents</p>
                    </div> 
                    <div class="text-center">
                        <a href="{{ route('register') }}"> <button class="btn button-inverse btn-sm my-2">View Services</button></a> <br>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    <section class="info-section">
        <div class="container">
            <div class="row info-sub-section">
                <div class="info-img col-sm-6">
                    <img src="{{ mix('images/employee_search.png') }}" alt="employee_search"  width="300">
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
                    <img src="{{ mix('images/employer_search.png') }}" alt="employer_search" width="350">
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
    <div class="container description">
        <h1 class="text-center text-muted mt-4 bold">How it works</h1>
        <div class="row card-container">
                <div class="col-md content-card" style="margin: 0px;">
                    <a href="{{ route('register') }}" class="card-link" style="color: inherit;">
                        <img src="{{ mix('images/service.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                        <h3 class="mt-4 mb-4">Registration</h3>
                        <p class="text-muted mb-4">
                            Create an account in less than 1 minute. Join CroxxTalent and find opportunities. 
                        </p>
                    </a>
                </div>
                <div class="col-md content-card">
                    <a href="{{ route('register') }}"  class="card-link" style="color: inherit;">
                        <img src="{{ mix('images/settings.jpg') }}" class="mt-5" width="80" alt="Acountability Icon">
                        <h3 class="mt-4 mb-4">Setup Profile</h3>
                        <p class="text-muted mb-4">
                            Quickly setup your profile and settings.
                        </p>
                    </a>
                </div>
                <div class="col-md content-card">
                    <a href="{{ route('register') }}"  class="card-link" style="color: inherit;">
                        <img src="{{ mix('images/layers.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                        <h3 class="mt-4 mb-4">Build CV</h3>
                        <p class="text-muted mb-2">
                            Build your CV using our live web cv builder. Build a beautiful cv and showcase relevant skills for employers to see.  
                        </p>
                    </a>
                </div>
                <div class="col-md content-card">
                    <a href="{{ route('register') }}"  class="card-link" style="color: inherit;">
                        <img src="{{ mix('images/appointment.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                        <h3 class="mt-4 mb-4">Apply for Jobs</h3>
                        <p class="text-muted mb-4">
                            Check available jobs. Start sending your CV to 100’s of companies. Apply for multiple roles.
                        </p>
                    </a>
                </div>
        </div>
    </div>
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
    <section class="our-vision p-3">
        <div class="container about-content">
            <h2 class="mb-5 text-center">We Have a Vision </h2>
            <div class="row">
                <div class="col-md-6 info-img order-sm-12 pl-5 mb-3">
                        <img src="{{ mix('images/about.png') }}" alt="About Us" width="400">
                </div>
                <div class="col-md-6  order-sm-12 about-conten-paragraph">
                    <p class="text-muted">
                        Our vision is to be a talent partner of choice with an easily accessible platform providing a gateway for energy professionals <br> 
                        <a href="{{ route('register') }}"><button class="btn button-inverse btn-lg mt-4 mb-2">Get Started</button></a> <br>
                        <p class="text-muted">Already a member?<a href="{{ route('login') }}"> Sign In</a></p>
                    </p>  
                </div>
            </div> 
        </div>
    </section>
   
    @include('inc.subscribe')
    @include('inc.footer')
</body>
</html>